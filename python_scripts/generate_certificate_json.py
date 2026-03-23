
import os
import sys
import json
import time
from datetime import datetime
import xlwings as xw

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

def generate_pdf(data_json_path, template_path, output_pdf_path):
    if not os.path.exists(data_json_path):
        print(f"Error: JSON data not found at {data_json_path}")
        return

    with open(data_json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    if not os.path.exists(template_path):
        print(f"Error: Template not found at {template_path}")
        return

    # Use a hidden Excel instance
    app = xw.App(visible=False)
    
    try:
        wb = app.books.open(template_path)
        ws = wb.sheets[0] 
            
        # Header: Date and Time
        ws.range('I3').value = data.get('fecha_registro_formatted', '')
        ws.range('K3').value = data.get('hora_registro', '')
        
        # ID Area (Row 5): Multi-box mapping
        ws.range('I5').value = data.get('vet_acronyms', 'PS1')
        ws.range('J5').value = data.get('animal_id', '')
        ws.range('K5').value = str(data.get('id', '')).zfill(4)
        
        # --- Checkboxes: Species ---
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
        cert_area = (data.get('vet_area_reproduccion') or "").lower()
        for stem, cb_name in species_checks.items():
            _set_checkbox(ws, cb_name, stem in cert_area)

        # --- Checkboxes: Sex ---
        cert_sex = (data.get('sexo') or "").lower()
        _set_checkbox(ws, 'CheckMacho',  'macho' in cert_sex or cert_sex == 'm')
        _set_checkbox(ws, 'CheckHembra', 'hembra' in cert_sex or cert_sex == 'f')

        # Informe Post-Mortem
        vet_name = f"{data.get('vet_nombre', '')} {data.get('vet_apellido', '')}".upper()
        ws.range('B13').value = vet_name
        ws.range('I13').value = data.get('vet_cedula', '')
        
        # M.P.P.S. [Code], CMV [Estado] [College Code]
        ws.range('F15').value = data.get('vet_ministerio_codigo', '0')
        ws.range('I15').value = "Edo. Lara"
        ws.range('K15').value = data.get('vet_colegio_medico_codigo', '0')
        
        # declaro la muerte... en estado de: [EDAD/STATE]
        ws.range('G17').value = (data.get('estatus', '')).upper()
        
        # número de ID: [Animal ID]
        ws.range('C19').value = data.get('animal_id', '')
        
        # propiedad del área de producción: H19
        ws.range('H19').value = (data.get('raza', '')).upper()
        
        # Perteneciente a / Unidad de Producción: A21
        ws.range('A21').value = "PROCER (SITIO I)"
        
        # reportado por:
        ws.range('F21').value = (data.get('reportado_por', '')).upper()
        
        # causa de muerte...: [CAUSA / SISTEMA]
        cause_name = data.get('causa_muerte', 'N/A')
        system_name = data.get('sistema_involucrado', 'N/A')
        ws.range('A22').value = f"CAUSA: {cause_name} SISTEMA: {system_name}".upper()
        
        # Fecha real o estimada...
        death_date = data.get('fecha_muerte_formatted', 'N/A')
        ws.range('E23').value = f"Fecha real o estimada de la muerte: {death_date}"
        
        # Evaluaciones
        ws.range('C24').value = data.get('evaluacion_externa', 'N/A')
        ws.range('A26').value = data.get('evaluacion_interna', 'N/A')
        
        # Bottom Line
        location_str = f"{data.get('nave', '')} - {data.get('seccion', '')} - {data.get('corral', '')}"
        batch_val = data.get('lote', 'N/A')
        weight_val = f"{data.get('peso', '0')} Kg"
        ws.range('A29').value = f"Ubicación: {location_str}    LOTE: {batch_val}    PESO: {weight_val}"
        
        # Firmas footer
        ws.range('A34').value = vet_name
        ws.range('A36').value = data.get('vet_cedula', '')
        ws.range('A37').value = data.get('vet_ministerio_codigo', '0')
        ws.range('A38').value = data.get('vet_colegio_medico_codigo', '0')

        # ── Photos ──
        if data.get('arete_photo_path'):
            _insert_photo(ws, 'ImaArete', data.get('arete_photo_path'))
        if data.get('tatuaje_photo_path'):
            _insert_photo(ws, 'ImaTatuaje', data.get('tatuaje_photo_path'))
        if data.get('otra_photo_path'):
            _insert_photo(ws, 'ImaOtra', data.get('otra_photo_path'))

        # --- Export to PDF ---
        time.sleep(1) # Wait for images/rendering
        ws.to_pdf(output_pdf_path)
        
        wb.close()
        print(f"Success: PDF generated at {output_pdf_path}")
                
    except Exception as e:
        print(f"Error: {e}")
    finally:
        try:
            app.quit()
        except:
            pass

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: python generate_certificate_json.py <json_path> <template_path> <output_pdf_path>")
    else:
        generate_pdf(sys.argv[1], sys.argv[2], sys.argv[3])
