import sys
import fitz
import re
import json
import os
import contextlib

# --- Silence ALL PyMuPDF output (stderr + stdout) ---
@contextlib.contextmanager
def silence():
    with open(os.devnull, 'w') as fnull:
        old_out = sys.stdout
        old_err = sys.stderr
        sys.stdout = fnull
        sys.stderr = fnull
        try:
            yield
        finally:
            sys.stdout = old_out
            sys.stderr = old_err

pdf_path = sys.argv[1]

rows = []
current_section = None

with silence():   # << HERE WE SILENCE EVERYTHING
    doc = fitz.open(pdf_path)

    for page in doc:
        blocks = page.get_text("blocks")
        blocks_sorted = sorted(blocks, key=lambda b: (b[1], b[0]))

        for (x0, y0, x1, y1, text, block_no, block_type) in blocks_sorted:
            t = text.strip()
            if not t:
                continue

            # detect section
            flat_section = re.sub(r'\s+', ' ', t).strip()
            if flat_section.isupper() and len(flat_section) > 4:
                current_section = flat_section
                continue

            # flatten line
            flat = re.sub(r'\s+', ' ', t).strip()

            m = re.match(r'^([☐☒☑])\s+([☐☒☑])\s+([☐☒☑])\s+(.+)$', flat)
            if not m:
                continue

            b1, b2, b3, item = m.groups()

            status = None
            def checked(ch): return ch in ('☒', '☑')

            if checked(b1):
                status = "applicable"
            elif checked(b2):
                status = "incorporated"
            elif checked(b3):
                status = "confirmed"
            else:
                continue

            rows.append({
                "section": current_section,
                "item": item,
                "status": status
            })

# NOW we print ONLY JSON
print(json.dumps({"rows": rows}, ensure_ascii=False))
