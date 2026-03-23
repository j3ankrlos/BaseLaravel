import os
import time
from datetime import datetime
import xlwings as xw
from django.conf import settings
from django.utils import timezone

def export_lista_campo_to_pdf(aretes_queryset, output_buffer):
    """
    Fills the PlanillaListaCampo.xlsx template with a list of aretes
    for field use (weighing, observations, etc.)
    """
    template_path = os.path.join(settings.MEDIA_ROOT, 'templates', 'excel', 'PlanillaListaCampo.xlsx')
    
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
        
        # --- HEADER DATA ---
        first_item = aretes_queryset.first()
        if first_item:
            local_now = timezone.localtime(timezone.now())
            ws.range('R3').value = local_now.day
            ws.range('S3').value = local_now.month
            ws.range('T3').value = local_now.year
            
            # PIC Number (numeric part as in Movimiento)
            import re
            pic_val = str(first_item.pic_numero or "")
            pic_digits = re.findall(r'\d+', pic_val)
            if pic_digits:
                ws.range('T4').value = pic_digits[-1]
            
            # Lote - Celda P5
            ws.range('P5').value = first_item.lote
            
            # Peso Promedio - Celda R5
            try:
                pesos = [item.peso for item in aretes_queryset if item.peso]
                if pesos:
                    cell_r5 = ws.range('R5')
                    cell_r5.number_format = '#,##0.##'
                    cell_r5.value = sum(pesos) / len(pesos)
            except: pass
            
        # --- TABLE DATA ---
        start_row = 12
        for idx, item in enumerate(aretes_queryset):
            current_row = start_row + idx
            if current_row > 150: # safety break
                break
                
            # Mapping based on "CONTROL DE CELOS" form:
            ws.range(f'A{current_row}').value = idx + 1 # N°
            ws.range(f'B{current_row}').value = item.corral
            ws.range(f'C{current_row}').value = item.arete
            ws.range(f'D{current_row}').value = item.genetica or (item.raza.name if item.raza else "")
            
            cell_e = ws.range(f'E{current_row}')
            cell_e.number_format = '#,##0.##'
            cell_e.value = item.peso

        # --- EXPORT ---
        temp_excel = os.path.join(settings.MEDIA_ROOT, f'temp_campo_{int(time.time())}.xlsx')
        wb.save(temp_excel)
        
        temp_pdf = os.path.join(settings.MEDIA_ROOT, f'lista_campo_{int(time.time())}.pdf')
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
