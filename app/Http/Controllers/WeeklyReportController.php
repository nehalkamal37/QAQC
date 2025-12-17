<?php

namespace App\Http\Controllers;
use App\Services\WeeklyReportService;
use Barryvdh\DomPDF\Facade\Pdf;  //This package converts HTML → PDF.

class WeeklyReportController extends Controller
{


    public function show(WeeklyReportService $service) //Laravel automatically injects WeeklyReportService (This is called dependency injection.)
    {
        $report = $service->generate();  //Gets the weekly report data array

        return view('reports.weekly', compact('report'));
    }


    public function pdf(WeeklyReportService $service)  //Same dependency injection as before.
   {
    $report = $service->generate();   //Same data, reused again.

    $pdf = Pdf::loadView('reports.weekly-pdf', compact('report'));   
     //Loads a Blade view designed for PDF
     //Renders it as HTML
     //Converts it into a PDF document

    return $pdf->download('weekly-report.pdf');   //Forces a file download
   }

public function csv(WeeklyReportService $service)
  {
    $report = $service->generate();

    $headers = [
        'Content-Type' => 'text/csv',   //These headers tell the browser:This is a CSV file,Download it (don’t display it)
        'Content-Disposition' => 'attachment; filename="weekly-report.csv"',
    ];

    $callback = function () use ($report) {  //This function writes data directly to the output stream.
        $file = fopen('php://output', 'w');  //Opens a write stream to the response output

        fputcsv($file, ['Status', 'Count']);  //Writes the CSV header row

        foreach ($report['by_status'] as $status => $count) {
            fputcsv($file, [$status, $count]);   //Writes each status row ,Uses the report’s grouped data
        }

        fclose($file);  //Closes the output stream
    };

    return response()->stream($callback, 200, $headers);  //Sends the CSV to the browser as a streamed response
   }


}
