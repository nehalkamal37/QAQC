import fitz
import json
import sys

pdf_path = sys.argv[1]

doc = fitz.open(pdf_path)

checkboxes = []

for page_num, page in enumerate(doc, start=1):
    widgets = page.widgets()

    if widgets:
        for w in widgets:
            if w.field_type == fitz.PDF_WIDGET_TYPE_CHECKBOX:
                checkboxes.append({
                    "page": page_num,
                    "name": w.field_name,
                    "value": w.field_value,
                    "checked": w.field_value == "Yes",
                    "rect": list(w.rect)
                })

print(json.dumps({"checkboxes": checkboxes}))
