<?php

namespace App\Http\Controllers;

use App\Services\WeeklyReportService;
use App\Services\WeeklyReportExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WeeklyReportController extends Controller
{
    /**
     * Weekly report screen (UI)
     * Lightweight, fast, dashboard-oriented
     */
    public function show(WeeklyReportService $service)
    {
        $report = $service->generate();

        return view('reports.weekly', compact('report'));
    }

    /**
     * Weekly PDF export
     * Heavy, analytical, archival
     */
    public function pdf(WeeklyReportExportService $exportService)
    {
        $report = $exportService->build();

        $pdf = Pdf::loadView('reports.weekly-pdf', compact('report'))
            ->setPaper('a4', 'portrait');

        return $pdf->download(
            'weekly-qa-qc-report-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    /**
     * Weekly CSV export
     * Structured for Excel / Power BI
     */
    public function csv(WeeklyReportExportService $exportService): StreamedResponse
    {
        $report = $exportService->build();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="weekly-qa-qc-report-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($report) {
            $file = fopen('php://output', 'w');

            /**
             * Sectioned CSV (NOT flat status/count)
             * This is what makes it powerful.
             */

            // ===== META =====
            fputcsv($file, ['SECTION', 'KEY', 'VALUE']);
            fputcsv($file, ['meta', 'from', $report['meta']['from']]);
            fputcsv($file, ['meta', 'to', $report['meta']['to']]);
            fputcsv($file, ['meta', 'generated_at', $report['meta']['generated_at']]);

            // ===== THROUGHPUT =====
            foreach ($report['throughput'] as $key => $value) {
                fputcsv($file, ['throughput', $key, $value]);
            }

            // ===== STATUS DISTRIBUTION =====
            foreach ($report['status_distribution'] as $status => $row) {
                fputcsv($file, [
                    'status',
                    $status,
                    $row['count'],
                    $row['percentage'] . '%'
                ]);
            }

            // ===== AIC =====
            foreach ($report['aic'] as $key => $value) {
                fputcsv($file, ['aic', $key, $value]);
            }

            // ===== OVERDUE =====
            foreach ($report['overdue'] as $key => $value) {
                fputcsv($file, ['overdue', $key, $value]);
            }

            // ===== ACTIVITY SUMMARY =====
            foreach ($report['activity_summary'] as $action => $count) {
                fputcsv($file, ['activity', $action, $count]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
