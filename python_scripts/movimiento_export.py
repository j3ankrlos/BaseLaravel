
import os
import time
from datetime import datetime
import xlwings as xw
from django.conf import settings
from django.utils import timezone

def export_movimiento_to_pdf(aretes_queryset, output_buffer):
    """
    Fills the PlanillaMovimientos.xlsx template with a list of aretes
    and exports it to PDF using xlwings.
    """
    template_path = os.path.join(settings.MEDIA_ROOT, 'templates', 'excel', 'PlanillaMovimientos.xlsx')
    
    if not os.path.exists(template_path):
        raise FileNotFoundError(f"Template not found at {template_path}")

    # Use a hidden Excel instance
    try:
        app = xw.App(visible=False)
    except Exception as e:
        raise RuntimeError(
            "Microsoft Excel debe estar instalado para generar PDFs. "
            "Si Excel ya está instalado, verifique que no haya instancias bloqueadas."
        ) from e
    
    try:
        wb = app.books.open(template_path)
        ws = wb.sheets[0] 
        
        # --- CLEAR TEMPLATE AREA ---
        # use .api.ClearContents() to avoid errors with merged cells
        try:
            ws.range('Y3').api.ClearContents()   # Dia
            ws.range('AA3').api.ClearContents()  # Mes
            ws.range('AD3').api.ClearContents()  # Año
            ws.range('AC4').api.ClearContents()  # Fecha PIC
            ws.range('N6').api.ClearContents()   # Granja
            
            # Bloque 1: 11-62
            ws.range('B11:AA62').api.ClearContents()
            # Bloque 2: 70-121
            ws.range('B70:AA121').api.ClearContents()
        except Exception as e:
            print(f"Warning clearing contents (might be merged cells): {e}")
        
        # --- HEADER DATA ---
        first_item = aretes_queryset.first()
        if first_item:
            local_now = timezone.localtime(timezone.now())
            ws.range('Y3').value = local_now.day
            ws.range('AA3').value = local_now.month
            ws.range('AD3').value = local_now.year
            
            # Fecha PIC header - Celda AC4
            import re
            pic_val = str(first_item.pic_numero or "")
            pic_digits = re.findall(r'\d+', pic_val)
            if pic_digits:
                ws.range('AC4').value = pic_digits[-1]

            # Granja - Celda N6
            ws.range('N6').value = first_item.granja.name if first_item.granja else ""
            
        # --- TABLE DATA (Pagination: 11-62, 70-121) ---
        # Row mapping: indices 0-51 -> 11-62, indices 52-103 -> 70-121
        for idx, item in enumerate(aretes_queryset):
            if idx < 52:
                current_row = 11 + idx
            elif idx < 104:
                current_row = 70 + (idx - 52)
            else:
                break # Max capacity reached for this template layout
                
            # Mapping based on user description:
            # Col B: LOTE (toma 3 primeros caracteres numericos para calculos)
            ws.range(f'B{current_row}').value = item.lote
            
            # Col C: ID (Arete)
            ws.range(f'C{current_row}').value = item.arete
            
            # Col E: CANTIDAD (Siempre 1)
            ws.range(f'E{current_row}').value = 1
            
            # Col J: PESO
            cell_j = ws.range(f'J{current_row}')
            cell_j.number_format = '#,##0.##'
            cell_j.value = item.peso
            
            # Col K: LINEA GENETICA o RAZA
            ws.range(f'K{current_row}').value = item.genetica or (item.raza.name if item.raza else "")
            
            # Col P: ORIGEN
            ws.range(f'P{current_row}').value = item.origen.name if item.origen else ""
            
            # Col U: DESTINO
            ws.range(f'U{current_row}').value = item.destino.name if item.destino else ""
            
            # Col W: Letra 'C' por corral
            ws.range(f'W{current_row}').value = 'C'
            
            # Col X: Numero del corral con dos digitos
            corral_val = item.corral or ""
            # Si el corral es numérico, formatear a 2 dígitos
            try:
                if corral_val.isdigit():
                    corral_val = f"{int(corral_val):02d}"
            except: pass
            ws.range(f'X{current_row}').value = corral_val

            # Col AA: Fecha PIC aproximada de monta (Fecha PIC - LOTE)
            # Solo usa los 3 primeros digitos del lote para la resta
            try:
                import re
                lote_digits = re.findall(r'\d+', str(item.lote))
                if lote_digits:
                    lote_num = int(lote_digits[0][:3])
                    full_pic = str(item.pic_numero or "0")
                    pic_num = int(re.findall(r'\d+', full_pic)[-1]) if re.findall(r'\d+', full_pic) else 0
                    ws.range(f'AA{current_row}').value = pic_num - lote_num
            except Exception as e:
                print(f"Error calculating monta for row {current_row}: {e}")

        # --- EXPORT ---
        # Save temp copy
        temp_excel = os.path.join(settings.MEDIA_ROOT, f'temp_mov_{int(time.time())}.xlsx')
        wb.save(temp_excel)
        
        # Export sheet to PDF
        temp_pdf = os.path.join(settings.MEDIA_ROOT, f'reporte_movimiento_{int(time.time())}.pdf')
        ws.to_pdf(temp_pdf)
        
        with open(temp_pdf, 'rb') as f:
            output_buffer.write(f.read())
            
        wb.close()
        
        # Cleanup
        for tmp in [temp_pdf, temp_excel]:
            if os.path.exists(tmp):
                try: os.remove(tmp)
                except: pass
                
    finally:
        try: app.quit()
        except: pass
