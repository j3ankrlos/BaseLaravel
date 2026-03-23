
import os
import time
from datetime import datetime
import xlwings as xw
from django.conf import settings
from django.utils import timezone

# Excel checkbox state constants
XL_ON = 1
XL_OFF = -4146

def _set_checkbox(ws, name, checked):
    """Set a named CheckBox shape to checked/unchecked via COM API."""
    try:
        shape = ws.api.Shapes(name)
        shape.OLEFormat.Object.Value = XL_ON if checked else XL_OFF
    except Exception as e:
        print(f"Warning: Could not set checkbox '{name}': {e}")

def _insert_photo(ws, shape_name, image_path):
    """Replace a named image placeholder shape with the actual photo."""
    if not image_path or not os.path.exists(image_path):
        return
    try:
        placeholder = ws.api.Shapes(shape_name)
        left = placeholder.Left
        top = placeholder.Top
        width = placeholder.Width
        height = placeholder.Height
        placeholder.Delete()
        pic = ws.api.Shapes.AddPicture(
            Filename=image_path,
            LinkToFile=False,
            SaveWithDocument=True,
            Left=left, Top=top,
            Width=width, Height=height
        )
        pic.LockAspectRatio = False
        pic.Width = width
        pic.Height = height
    except Exception as e:
        print(f"Warning: Could not insert photo into '{shape_name}': {e}")

def export_certificate_to_pdf(cert, output_buffer):
    """
    Fills the PlanillaCertificado.xlsx template and exports it to PDF using xlwings.
    """
    template_path = os.path.join(settings.MEDIA_ROOT, 'templates', 'excel', 'PlanillaCertificado.xlsx')
    
    if not os.path.exists(template_path):
        raise FileNotFoundError(f"Template not found at {template_path}")

    # Use a hidden Excel instance
    app = xw.App(visible=False)
    
    try:
        wb = app.books.open(template_path)
        ws = wb.sheets[0] # Active sheet
            
        # Mapping Data per Image 1 target
        local_now = timezone.localtime(timezone.now())
        
        # Header: Date and Time
        ws.range('I3').value = local_now.strftime('%d/%m/%Y')
        ws.range('K3').value = local_now.strftime('%H:%M:%S') # HMS as in target
        
        # ID Area (Row 5): Multi-box mapping
        # Target: [Sitio Acronym] [Animal ID] [Correlative]
        # Coordinates deduced: I5, J5, K5
        # I5: Siglas (saved in certificate)
        ws.range('I5').value = cert.vet_acronyms or "S/N"
        ws.range('H5').value = "" # Ensure clean
        ws.range('J5').value = cert.animal_id
        ws.range('K5').value = cert.certificate_number # This is the correlative
        
        # ── Checkboxes: Species (basado en Área de Producción) ───────────────
        # Se usa la raíz de cada especie para cubrir variantes de nombre:
        # "Porcino", "Porcina", "Porcinos", "Porcina" → todos activan CheckPorcino
        species_checks = {
            'caprin':  'CheckCaprino',
            'bovin':   'CheckBovino',
            'equin':   'CheckEquino',
            'bufal':   'CheckBufalino',
            'ovin':    'CheckOvino',
            'cuni':    'CheckCunicola',
            'canin':   'CheckCanino',
            'porcin':  'CheckPorcino',
        }
        # Prioridad: valor guardado en certificado; fallback: FK en vivo del empleado
        cert_area = (cert.production_area or "").strip().lower()
        if not cert_area and cert.veterinarian:
            emp_live = getattr(cert.veterinarian, 'fk_id_personal', None)
            if emp_live and emp_live.vet_area:
                cert_area = emp_live.vet_area.name.strip().lower()

        for stem, cb_name in species_checks.items():
            _set_checkbox(ws, cb_name, stem in cert_area)

        # ── Checkboxes: Sex ──────────────────────────────────────────────────
        cert_sex = (cert.sex or "").lower()
        _set_checkbox(ws, 'CheckMacho',  'macho'  in cert_sex)
        _set_checkbox(ws, 'CheckHembra', 'hembra' in cert_sex)

        # Informe Post-Mortem
        # Yo, [Vet Name], titular de la cédula... [ID]
        # Nombre corto: primer nombre + primer apellido
        full_vet_name = cert.vet_name or ""
        name_parts = full_vet_name.strip().split()
        short_vet_name = f"{name_parts[0]} {name_parts[-1]}".upper() if len(name_parts) >= 2 else full_vet_name.upper()
        ws.range('B13').value = short_vet_name
        ws.range('I13').value = cert.vet_cedula
        
        # M.P.P.S. [Code], CMV [Estado] [College Code]
        ws.range('F15').value = cert.vet_mpps_code or "0"
        
        emp = None
        if cert.veterinarian and hasattr(cert.veterinarian, 'fk_id_personal'):
            emp = cert.veterinarian.fk_id_personal
            
        if emp and emp.vet_status:
           ws.range('I15').value = f"Edo. {emp.vet_status.name}"
        
        ws.range('K15').value = cert.vet_college_code
        
        # declaro la muerte... en estado de: [EDAD/STATE]
        # In target image it shows "LECHONA" (might be age)
        ws.range('G17').value = (cert.age or cert.state.name if cert.state else "").upper()
        
        # número de ID: [Animal ID]
        ws.range('C19').value = cert.animal_id
        
        # propiedad del área de producción: H19
        ws.range('H19').value = cert.production_area or ""
        
        # Perteneciente a / Unidad de Producción: A21  — solo el nombre de la unidad
        unit_display = (cert.production_unit or "").upper()
        ws.range('A21').value = unit_display
        
        # reportado por: solo el nombre, sin cédula entre paréntesis
        # notification_status se guarda como "NOMBRE (cedula)" — extraemos el nombre
        import re
        notif_raw = cert.notification_status or ""
        notif_name = re.sub(r'\s*\(.*?\)', '', notif_raw).strip().upper()
        ws.range('F21').value = notif_name
        
        # causa de muerte...: [CAUSA / SISTEMA]
        cause_name = cert.cause.name if cert.cause else "N/A"
        system_name = cert.system_affected.name if cert.system_affected else "N/A"
        ws.range('A22').value = f"CAUSA: {cause_name} SISTEMA: {system_name}".upper()
        
        # Fecha real o estimada...
        death_date_str = cert.death_date.strftime('%d/%m/%Y') if cert.death_date else "N/A"
        ws.range('E23').value = f"Fecha real o estimada de la muerte: {death_date_str}"
        
        # Evaluaciones
        ws.range('C24').value = cert.external_eval or "N/A"
        ws.range('A26').value = cert.internal_eval or "N/A"
        
        # Bottom Line
        location_str = f"{cert.location.name if cert.location else ''} - {cert.section.name if cert.section else ''} - {cert.corral or ''}"
        batch_val = cert.batch or "N/A"
        weight_val = f"{cert.weight} Kg" if cert.weight else "N/A"
        ws.range('A29').value = f"Ubicación: {location_str}    LOTE: {batch_val}    PESO: {weight_val}"
        
        # ── Photos ───────────────────────────────────────────────────────────
        def _abs_path(field):
            """Return absolute filesystem path for a FileField value, or None."""
            if not field:
                return None
            path = os.path.join(settings.MEDIA_ROOT, str(field))
            return path if os.path.exists(path) else None

        _insert_photo(ws, 'ImaArete',   _abs_path(cert.photo_ear_tag))
        _insert_photo(ws, 'ImaTatuaje', _abs_path(cert.photo_tattoo))
        _insert_photo(ws, 'ImaOtra',    _abs_path(cert.photo_other))

        # --- VOIDED Watermark ---
        if cert.status == cert.Status.VOIDED:
            print(f"DEBUG: Adding ANULADO watermark for cert {cert.certificate_number} using COM API")
            try:
                # Excel constants
                msoTextOrientationHorizontal = 1
                
                # Coordinates (A4 center roughly)
                left, top, width, height = 50, 200, 500, 150
                
                # Using direct COM API because xlwings wrapper might be inconsistent in this environment
                shape_api = ws.api.Shapes.AddTextbox(msoTextOrientationHorizontal, left, top, width, height)
                
                # Set text and formatting
                shape_api.TextFrame.Characters().Text = "ANULADO"
                
                font = shape_api.TextFrame.Characters().Font
                font.Size = 80
                font.Bold = True
                font.Color = 255 # Red
                
                # Alignment
                shape_api.TextFrame.HorizontalAlignment = -4108 # xlCenter
                shape_api.TextFrame.VerticalAlignment = -4108 # xlCenter
                
                # Rotation
                shape_api.Rotation = -45
                
                # Transparency and lines
                try:
                    shape_api.Fill.Transparency = 0.5
                    shape_api.Line.Visible = 0 
                except:
                    pass
                
                print(f"DEBUG: Watermark COM for {cert.certificate_number} created successfully.")
            except Exception as e:
                print(f"Warning: Could not add watermark via COM: {e}")
        else:
            print(f"DEBUG: Cert {cert.certificate_number} status is {cert.status}, NOT voided.")

        # --- Export to PDF ---
        # Give Excel a moment to render the shape
        time.sleep(2)
        
        # Save and Refresh to force committing UI elements
        temp_excel = os.path.join(settings.MEDIA_ROOT, f'temp_cert_{cert.id}.xlsx')
        wb.save(temp_excel)
        
        # Optional: Force a refresh if possible
        try:
            wb.api.RefreshAll()
        except:
            pass
            
        temp_pdf = os.path.join(settings.MEDIA_ROOT, f'certificado_{cert.id}_{int(time.time())}.pdf')
        ws.to_pdf(temp_pdf)
        
        with open(temp_pdf, 'rb') as f:
            output_buffer.write(f.read())
            
        wb.close()
        
        # Cleanup temp files
        for tmp in [temp_pdf, temp_excel]:
            if os.path.exists(tmp):
                try:
                    os.remove(tmp)
                except:
                    pass
                
    finally:
        try:
            app.quit()
        except:
            pass
