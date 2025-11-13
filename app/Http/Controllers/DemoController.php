<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DemoController extends Controller
{
    public function qcChecklistDemo()
    {
        $filePath = public_path('demo/Copy of Electrical QC Checklist_1.xlsx');

        // نقرأ الإكسل باستخدام PhpSpreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // نبدأ من الصف الرابع تقريبًا (عشان أول 3 صفوف meta info)
        $data = [];
        foreach ($rows as $index => $row) {
            if ($index < 3) continue;

            $type = $row[3] ?? '';
            $category = $row[4] ?? '';
            $item = $row[5] ?? '';
            $notes = $row[6] ?? '';

            if (trim($item) != '') {
                $data[] = [
                    'type' => $type,
                    'category' => $category,
                    'item' => $item,
                    'notes' => $notes,
                ];
            }
        }

        return view('demo.qc_checklist', compact('data'));
    }
}
