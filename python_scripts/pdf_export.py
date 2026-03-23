
import os
import time
import xlwings as xw
from django.conf import settings
import logging

logger = logging.getLogger('core')

def export_consumption_to_pdf(consumption_record, output_buffer):
    """
    Fills the Excel template and exports it to PDF using xlwings.
    This ensures 100% fidelity to the Excel formatting.
    """
    template_path = os.path.join(settings.MEDIA_ROOT, 'templates', 'excel', 'PlanillaConsumo.xlsx')
    
    if not os.path.exists(template_path):
        raise FileNotFoundError(f"Template not found at {template_path}")

    logger.info(f"Starting PDF export for consumption {consumption_record.id}. Template: {template_path}")
    
    # Use a hidden Excel instance
    app = xw.App(visible=False)
    
    try:
        logger.info("Opening template...")
        wb = app.books.open(template_path)
        # Assuming the sheet is named 'Formato' as seen in previous steps
        # If not, we'll try to find it or use active sheet
        try:
            ws = wb.sheets['Formato']
        except:
            ws = wb.sheets[0]
            
        # --- Header Mapping ---
        # Adjusted according to specific user coordinates
        ws.range('L2').value = consumption_record.pic_number or ""
        ws.range('N2').value = consumption_record.traspaso_reference or str(consumption_record.planilla_number or consumption_record.id)
        
        # Date components (Rectified: P3, Q3, R3)
        ws.range('P3').value = consumption_record.date.day
        ws.range('Q3').value = consumption_record.date.month
        ws.range('R3').value = str(consumption_record.date.year)[-2:]
        
        # Semana
        semana = consumption_record.date.isocalendar()[1]
        ws.range('N4').value = semana

        # --- Details Mapping ---
        details = consumption_record.details.all()[:24]
        for i, detail in enumerate(details):
            row = 9 + i
            ws.range(f'B{row}').value = i + 1
            ws.range(f'C{row}').value = str(detail.location)
            ws.range(f'D{row}').value = detail.product.code
            ws.range(f'E{row}').value = detail.product.name
            
            # LOTE in G
            ws.range(f'G{row}').value = detail.batch or ""
            
            # CANTIDAD in H, UNIDAD in J (Corrected logic: H gets quantity, not batch)
            ws.range(f'H{row}').value = float(detail.quantity_solicited)
            ws.range(f'J{row}').value = detail.product.unit_measure
            
            # Returned ONLY if > 0 (Using N and O as best estimate relative to J)
            if detail.quantity_returned and float(detail.quantity_returned) > 0:
                ws.range(f'N{row}').value = float(detail.quantity_returned)
                ws.range(f'O{row}').value = detail.product.unit_measure
            else:
                ws.range(f'N{row}').value = ""
                ws.range(f'O{row}').value = ""

        # --- Footer Signature ---
        resp_name = ""
        if consumption_record.responsible:
            if hasattr(consumption_record.responsible, 'get_full_name'):
                resp_name = consumption_record.responsible.get_full_name()
            else:
                resp_name = getattr(consumption_record.responsible, 'nombre_corto', '') or consumption_record.responsible.username
            
            # Write name in the ELABORADO POR area (usually around row 34-36)
            ws.range('A35').value = f"NOMBRE Y APELLIDO: {resp_name}"

        # --- Export to PDF ---
        temp_pdf = os.path.join(settings.MEDIA_ROOT, f'temp_planilla_{consumption_record.id}.pdf')
        logger.info(f"Exporting to PDF: {temp_pdf}")
        ws.to_pdf(temp_pdf)
        
        with open(temp_pdf, 'rb') as f:
            output_buffer.write(f.read())
            
        wb.close()
        if os.path.exists(temp_pdf):
            try:
                os.remove(temp_pdf)
            except:
                pass
                
    finally:
        try:
            app.quit()
        except:
            pass
        # Optional: kill excel process if it hangs, but app.quit() should work on Windows

def export_transfer_to_pdf(transfer_request, output_buffer):
    """
    Fills the TransferRequest Excel template and exports it to PDF using xlwings.
    """
    template_path = os.path.join(settings.MEDIA_ROOT, 'templates', 'excel', 'PlanillaSolicitud.xlsx')
    
    if not os.path.exists(template_path):
        raise FileNotFoundError(f"Template not found at {template_path}")

    logger.info(f"Starting PDF export for transfer {transfer_request.id}. Template: {template_path}")
    app = xw.App(visible=False)
    
    try:
        logger.info("Opening template...")
        wb = app.books.open(template_path)
        ws = wb.sheets[0]
            
        # --- Header Mapping ---
        # D7: Solicitante (Column 4)
        requester_name = ""
        if transfer_request.requester:
            if hasattr(transfer_request.requester, 'get_full_name'):
                requester_name = transfer_request.requester.get_full_name()
            else:
                requester_name = getattr(transfer_request.requester, 'nombre_corto', '') or transfer_request.requester.username
        ws.range('D7').value = requester_name
        
        # I7: N° Solicitud (Column 9)
        ws.range('I7').value = str(transfer_request.sequence_number or transfer_request.id).zfill(6)
        
        # L7: Fecha (Column 12)
        ws.range('L7').value = transfer_request.created_at.strftime('%d/%m/%Y')
        
        # --- Details Mapping ---
        start_row = 11
        details = transfer_request.details.all()[:24] # Limit to template capacity
        for i, detail in enumerate(details):
            row = start_row + i
            ws.range(f'B{row}').value = i + 1
            ws.range(f'C{row}').value = detail.product.code
            ws.range(f'D{row}').value = detail.product.name
            ws.range(f'H{row}').value = detail.product.unit_measure
            
            # Show approved quantity if finalized, otherwise requested
            if transfer_request.status in ['APPROVED', 'REJECTED'] and detail.quantity_approved is not None:
                qty = float(detail.quantity_approved)
            else:
                qty = float(detail.quantity_requested or 0)
                
            ws.range(f'J{row}').value = qty

        # --- Export to PDF ---
        temp_pdf = os.path.join(settings.MEDIA_ROOT, f'temp_solicitud_{transfer_request.id}.pdf')
        ws.to_pdf(temp_pdf)
        
        with open(temp_pdf, 'rb') as f:
            output_buffer.write(f.read())
            
        wb.close()
        if os.path.exists(temp_pdf):
            try:
                os.remove(temp_pdf)
            except:
                pass
                
    finally:
        try:
            app.quit()
        except:
            pass
