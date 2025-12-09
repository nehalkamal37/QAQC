import cv2
import numpy as np
import sys
import json

image_path = sys.argv[1]

img = cv2.imread(image_path)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# threshold
_, th = cv2.threshold(gray, 180, 255, cv2.THRESH_BINARY_INV)

# contours
contours, _ = cv2.findContours(th, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

boxes = []
for c in contours:
    x, y, w, h = cv2.boundingRect(c)
    if 18 < w < 40 and 18 < h < 40:   # checkbox size filter
        boxes.append((x, y, w, h))

checkboxes = []

for (x, y, w, h) in boxes:
    roi = th[y+3:y+h-3, x+3:x+w-3]   # crop inside checkbox

    # measure how filled the checkbox is
    black_ratio = cv2.countNonZero(roi) / roi.size

    checked = black_ratio > 0.14   # threshold for ✓ / X

    checkboxes.append({
        "x": x,
        "y": y,
        "checked": checked
    })

checkboxes = sorted(checkboxes, key=lambda b: (b["y"], b["x"]))

print(json.dumps({"checkboxes": checkboxes}))
