import sys
import json
import pandas as pd
import numpy as np

def to_bool(v):
    if v is None or (isinstance(v, float) and np.isnan(v)):
        return False
    v = str(v).strip().lower()
    return v in ["1", "true", "yes", "y", "x", "✓", "✔", "checked"]

def extract_excel(path):
    try:
        df = pd.read_excel(path, header=None)

        results = {}

        for i, row in df.iterrows():
            if i == 0:
                continue  # skip header

            applicable   = to_bool(row[0])
            incorporated = to_bool(row[1])
            confirmed    = to_bool(row[2])

            # Skip rows with all false (same as PDF importer)
            if not (applicable or incorporated or confirmed):
                continue

            topic    = str(row[3]).strip() if row[3] is not None else ""
            category = str(row[4]).strip() if row[4] is not None else ""
            item     = str(row[5]).strip() if row[5] is not None else ""
            notes    = str(row[6]).strip() if row[6] is not None else ""

            # Build description exactly like how your old system did
            description = " - ".join(x for x in [topic, category, item, notes] if x)

            if len(description) < 3:
                continue

            results[description] = {
                "applicable": applicable,
                "incorporated": incorporated,
                "confirmed": confirmed
            }

        return results

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No Excel file path provided"}))
        sys.exit(1)

    path = sys.argv[1]
    out = extract_excel(path)
    print(json.dumps(out))
