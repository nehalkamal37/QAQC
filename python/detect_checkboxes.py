import cv2
import json
import sys
import os
from pdf2image import convert_from_path

pdf_path = sys.argv[1]

# Convert PDF → images
pages = convert_from_path(pdf_path, dpi=200)
result = []

i = 1
for page in pages:
    img_path = f"page_{i}.png"
    page.save(img_path)

    # Load image
    img = cv2.imread(img_path)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # Threshold for checkbox detection
    _, thresh = cv2.threshold(gray, 170, 255, cv2.THRESH_BINARY_INV)

    # Find contours
    contours, _ = cv2.findContours(thresh, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)

    checkboxes = []
    for cnt in contours:
        x, y, w, h = cv2.boundingRect(cnt)

        # Detect square-like shapes
        if 20 < w < 45 and 20 < h < 45 and abs(w - h) < 10:

            cropped = thresh[y:y+h, x:x+w]
            black_pixels = cv2.countNonZero(cropped)
            total_pixels = w * h
            ratio = black_pixels / total_pixels

            is_checked = ratio > 0.18   # 18% threshold

            checkboxes.append({
                "x": x,
                "y": y,
                "checked": is_checked
            })

    result.append({
        "page": i,
        "checkboxes": sorted(checkboxes, key=lambda b: (b["y"], b["x"]))
    })

    i += 1

print(json.dumps(result))
