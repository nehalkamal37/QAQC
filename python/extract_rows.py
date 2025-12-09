import fitz
import json
import sys

pdf_path = sys.argv[1]

doc = fitz.open(pdf_path)

entries = []

# Collect all checkboxes + text blocks
items = []

for page_num, page in enumerate(doc, start=1):
    widgets = page.widgets() or []

    for w in widgets:
        rect = list(w.rect)
        items.append({
            "page": page_num,
            "name": w.field_name,
            "value": w.field_value,
            "checked": (w.field_value == "Yes"),
            "rect": rect,
            "type": "checkbox"
        })

    # Also read text blocks
    blocks = page.get_text("blocks")
    for b in blocks:
        rect = b[:4]
        text = b[4].strip()
        if text:
            items.append({
                "page": page_num,
                "name": text,
                "checked": False,
                "rect": rect,
                "type": "text"
            })

# Sort everything top-to-bottom
items = sorted(items, key=lambda x: (x["page"], x["rect"][1]))

# Group into rows
rows = []
current_row = []
last_y = None

for item in items:
    y = item["rect"][1]

    if last_y is None:
        last_y = y

    # new row?
    if abs(y - last_y) > 8:
        if current_row:
            rows.append(current_row)
        current_row = []
    current_row.append(item)
    last_y = y

if current_row:
    rows.append(current_row)

# Build final structured rows
final_rows = []

for row in rows:
    checkboxes = [i for i in row if i["type"] == "checkbox"]
    texts = [i for i in row if i["type"] == "text"]

    if not texts:
        continue

    # Use longest text as "item"
    item_text = max(texts, key=lambda t: len(t["name"]))["name"]

    final_rows.append({
        "item": item_text,
        "checked": any(cb["checked"] for cb in checkboxes),
        "checkboxes": [cb["checked"] for cb in checkboxes]
    })

print(json.dumps({"rows": final_rows}))
