<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Models\QcItem;

class QcUploadController extends Controller
{
   public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:pdf',
    ]);

    $pdf = (new \Smalot\PdfParser\Parser())->parseFile($request->file('file')->getRealPath());
    $text = $pdf->getText();

    $lines = preg_split("/\r\n|\r|\n/", $text);

    $data = [];
    $bufferCheckboxes = [];
    $currentItem = '';

    foreach ($lines as $line) {

        // Detect checkbox states
        if (preg_match('/Check Box\d+:\s*(Yes|Off)/i', $line, $m)) {
            $bufferCheckboxes[] = strtolower($m[1]) === 'yes' ? 1 : 0;
            continue;
        }

        // Real checklist item begins with text + colon
        if (strpos($line, ':') !== false) {

            if (count($bufferCheckboxes) >= 3) {
                // Clean item text
                $parts = explode(':', $line);
                $itemText = trim($parts[0]);

                $data[] = [
                    'item_text'     => $itemText,
                    'applicable'    => $bufferCheckboxes[0],
                    'incorporated'  => $bufferCheckboxes[1],
                    'confirmed'     => $bufferCheckboxes[2],
                ];
            }

            $bufferCheckboxes = []; // reset for next row
        }
    }

    // Save real data to DB
    \App\Models\QcItem::insert($data);

    return response()->json([
        'message' => 'Real PDF imported successfully',
        'rows_inserted' => count($data),
    ]);
}

    public function viewChecklist()
{
    $items = \App\Models\QcItem::orderBy('id')->get();
    return view('qc_items', compact('items'));
}



}
