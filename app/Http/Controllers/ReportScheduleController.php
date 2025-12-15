<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportSchedule;

class ReportScheduleController extends Controller
{

    public function edit(){
    $schedule =ReportSchedule::first();  // get the first schedule
    return view('settings.reports',compact('schedule'));
    }

    public function update(Request $request){


$day = $request->day === 'all' ? '*' : $request->day;

$cron = sprintf(
    '%d %d * * %s',
    $request->minute, // 👈 minute الأول
    $request->hour,   // 👈 hour بعده
    $day
);

ReportSchedule::updateOrCreate(
    ['type' => 'weekly'],
    [
        'cron_expression' => $cron,
        'enabled' => true,
    ]
);

    return back()->with('success', 'Schedule updated');
    }
}
