import sys
import json
import re
from PyPDF2 import PdfReader
from pdfminer.high_level import extract_text

def is_checked(field):
    """Return True if checkbox is checked."""
    if not field:
        return False
    value = field.get('/V') or field.get('/AS')
    if not value:
        return False
    return str(value) not in ['Off', '/Off', '']

def extract_checklist_data(pdf_path):
    reader = PdfReader(pdf_path)
    fields = reader.get_fields() or {}

    # Extract checkbox fields only
    checkbox_fields = [(name, f) for name, f in fields.items() if f.get('/FT') == '/Btn']

    # Sort checkbox fields numerically
    def sort_key(item):
        name = item[0]
        m = re.match(r"Check Box(\d+)", name)
        return int(m.group(1)) if m else 999999

    checkbox_fields = sorted(checkbox_fields, key=sort_key)

    # Extract text VERY FAST using pdfminer (not PyPDF2)
    text = extract_text(pdf_path)

    # Extract descriptions from printed checklist rows
    raw_lines = text.split("\n")
    descriptions = []

    for line in raw_lines:
        line = line.strip()
        if not line:
            continue

        # Detect rows like: "☐ ☐ ☐ Some description..."
        if re.match(r"^[☐☑☒■□▪•○◯]\s*[☐☑☒■□▪•○◯]\s*[☐☑☒■□▪•○◯]\s+", line):
            clean = re.sub(r"[☐☑☒■□▪•○◯]\s*", "", line).strip()
            if len(clean) >= 4:
                descriptions.append(clean)

    results = {}
    idx = 0

    # Each description gets 3 checkboxes (applicable/incorporated/confirmed)
    for desc in descriptions:
        applicable = False
        incorporated = False
        confirmed = False

        if idx < len(checkbox_fields):
            applicable = is_checked(checkbox_fields[idx][1])
            idx += 1

        if idx < len(checkbox_fields):
            incorporated = is_checked(checkbox_fields[idx][1])
            idx += 1

        if idx < len(checkbox_fields):
            confirmed = is_checked(checkbox_fields[idx][1])
            idx += 1

        results[desc] = {
            "applicable": applicable,
            "incorporated": incorporated,
            "confirmed": confirmed
        }

    return results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF path provided"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    out = extract_checklist_data(pdf_path)
    print(json.dumps(out))
