import cv2
import numpy as np
import json
import sys

path = sys.argv[1]

img = cv2.imread(path)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# Increase contrast to highlight boxes
gray = cv2.equalizeHist(gray)

# Edge detection
edges = cv2.Canny(gray, 30, 120, apertureSize=3)

# Hough line detection
lines = cv2.HoughLinesP(edges, 1, np.pi/180, threshold=50,
                        minLineLength=15, maxLineGap=4)

if lines is None:
    print(json.dumps({"checkboxes": []}))
    sys.exit()

boxes = []

# Collect horizontal and vertical lines
horizontal = []
vertical = []

for l in lines:
    x1, y1, x2, y2 = l[0]
    if abs(y1 - y2) < 3:       # horizontal line
        horizontal.append((x1, y1, x2, y2))
    elif abs(x1 - x2) < 3:     # vertical line
        vertical.append((x1, y1, x2, y2))

# match lines forming rectangles
for hx1, hy1, hx2, hy2 in horizontal:
    for vx1, vy1, vx2, vy2 in vertical:

        # Check intersection (lines crossing)
        if abs(hy1 - vy1) < 15 and abs(hx1 - vx1) < 15:

            # estimated square box
            x = vx1
            y = hy1
            w = 35
            h = 35

            roi = gray[y:y+h, x:x+w]
            if roi.size == 0:
                continue

            # compute black density
            _, th = cv2.threshold(roi, 180, 255, cv2.THRESH_BINARY_INV)
            black = np.sum(th == 255)
            ratio = black / roi.size

            checked = ratio > 0.05  # only 5% needed

            boxes.append({
                "x": int(x),
                "y": int(y),
                "checked": bool(checked)
            })

# sort boxes
boxes_sorted = sorted(boxes, key=lambda b: (b["y"], b["x"]))

print(json.dumps({"checkboxes": boxes_sorted}))
