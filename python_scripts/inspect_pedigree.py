
import xlwings as xw
import os

def inspect_pedigree(path):
    app = xw.App(visible=False)
    try:
        wb = app.books.open(path)
        ws = wb.sheets[0]
        cells = ['B53', 'D53', 'E53', 'B100', 'D100', 'E100']
        for c in cells:
            r = ws.range(c)
            print("CELL " + c + ": VALUE='" + str(r.value) + "' MERGED=" + str(r.merge_area.address))
        wb.close()
    finally:
        app.quit()

if __name__ == "__main__":
    inspect_pedigree(r'c:\xampp\htdocs\Laravel\granja-porcina\storage\app\templates\plantilla_pedigree.xlsx')
