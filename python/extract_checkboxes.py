import fitz
import json
import sys

pdf_path = sys.argv[1]

doc = fitz.open(pdf_path)
items = []

for page_number, page in enumerate(doc, start=1):
    widgets = page.widgets()
    if not widgets:
        continue

    for w in widgets:
        if w.field_type == fitz.PDF_WIDGET_TYPE_CHECKBOX:
            items.append({
                "page": page_number,
                "type": "checkbox",
                "name": w.field_name,
                "checked": (w.field_value == "Yes"),
                "rect": list(w.rect)
            })

print(json.dumps({"checkboxes": items}))
