<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QaMasterItem;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportQaMasterFromExcel extends Command
{
    protected $signature = 'qa:import-master';
    protected $description = 'Import QA master items from Electrical QC Checklist Excel file';

    public function handle(): int
    {
        $filePath = public_path('demo/Copy of Electrical QC Checklist_1.xlsx');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Reading: {$filePath}");

        // اقرأ الإكسل
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray();

        $insertCount = 0;

        foreach ($rows as $index => $row) {
            // نفس اللوجيك اللي كنتِ عاملاه في DemoController (عدلي الإندكس لو مختلف)
            // مثال: $row[3] = Type, $row[4] = Category, $row[5] = Item, $row[6] = Notes
            if ($index < 3) {
                // أول 3 صفوف header/meta – تجاهلهم
                continue;
            }

            $type     = $row[3] ?? null;
            $category = $row[4] ?? null;
            $item     = $row[5] ?? null;
            $notes    = $row[6] ?? null;

            // لو الصف فاضي تقريبًا، نطنّش
            if (! $type && ! $category && ! $item) {
                continue;
            }

            QaMasterItem::create([
                'discipline' => 'Electrical',
                'type'       => trim((string) $type),
                'category'   => trim((string) $category),
                'item'       => trim((string) $item),
                'notes'      => trim((string) $notes),
            ]);

            $insertCount++;
        }

        $this->info("Imported {$insertCount} master QA items successfully.");

        return self::SUCCESS;
    }
}
