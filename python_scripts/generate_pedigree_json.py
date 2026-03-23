
import xlwings as xw
import os
import sys
import json
import time

def generate_pedigree(data_json_path, template_path, output_pdf_path):
    if not os.path.exists(data_json_path):
        print(f"Error: JSON data not found at {data_json_path}")
        return

    with open(data_json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    if not os.path.exists(template_path):
        print(f"Error: Template not found at {template_path}")
        return

    app = xw.App(visible=False)
    
    try:
        wb = app.books.open(template_path)
        ws = wb.sheets[0] 
            
        lote = data.get('lote', '')
        responsible = data.get('responsible', '')

        # Fill table data
        start_row = 9
        index = 1
        for birth in data.get('births', []):
            try:
                pic_day = str(birth.get('pic_day') or '').zfill(3)
                pic_formatted = f"{pic_day}" if (pic_day and pic_day != '000') else ""
            except:
                pic_formatted = ""
            
            genetic_name = birth.get('genetic_name', '')
            
            for detail in birth.get('details', []):
                if index <= 44:
                    row_idx = 9 + index - 1
                else:
                    row_idx = 56 + (index - 45)
                
                if row_idx >= 101: break
                    
                row_data = [
                    index, birth.get('room'), birth.get('cage'), birth.get('mother_tag'),
                    birth.get('parity'), birth.get('father_tag'), pic_formatted, birth.get('lnv'),
                    birth.get('quantity'), detail.get('ear_id'), detail.get('generated_id'),
                    genetic_name, detail.get('weight'), birth.get('avg_weight'),
                    detail.get('teats_total'), detail.get('teats_left'), 
                    detail.get('teats_behind_shoulder_left'), detail.get('teats_behind_shoulder_right'),
                    detail.get('sex', '').upper(), ""
                ]
                ws.range(f'A{row_idx}').value = row_data
                index += 1

        # CLEAR and WRITE RESPONSIBLE in BOTH boxes if we are not sure
        # In the template: B53 has RAFAEL, E53 has Daniel.
        # Let's replace both so the user sees their name everywhere it matters.
        
        signature_cells = ['B53', 'D53', 'E53', 'B100', 'D100', 'E100']
        for cell_ref in signature_cells:
            cell = ws.range(cell_ref)
            # If the cell has RAFAEL or Daniel, replace it.
            # Or just replace whatever is in B and E as those are the name cells.
            if cell_ref.startswith('B') or cell_ref.startswith('E'):
                cell.value = responsible
                cell.api.Font.Bold = True

        # Special check for cell D53 since user mentioned it
        ws.range('D53').value = responsible
        ws.range('D53').api.Font.Bold = True

        # Force portrait orientation and fit all columns to 1 page width
        ws.api.PageSetup.Orientation = 1  # 1 = Portrait (vertical), 2 = Landscape
        ws.api.PageSetup.Zoom = False
        ws.api.PageSetup.FitToPagesWide = 1
        ws.api.PageSetup.FitToPagesTall = False
        
        # Export
        ws.to_pdf(output_pdf_path)
        
        wb.close(save=False)
        print(f"Success: PDF generated at {output_pdf_path}")
                
    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
    finally:
        try: app.quit()
        except: pass

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: python generate_pedigree_json.py <json_path> <template_path> <output_pdf_path>")
    else:
        generate_pedigree(sys.argv[1], sys.argv[2], sys.argv[3])
