<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportSchedule;

class ReportScheduleController extends Controller
{

    public function edit(){

   // $schedule =ReportSchedule::first();  // get the first schedule
   // return view('settings.reports',compact('schedule'));

    $weekly  = ReportSchedule::where('type', 'weekly')->first();
    $monthly = ReportSchedule::where('type', 'monthly')->first();

    return view('settings.reports', compact('weekly', 'monthly'));

    }

    public function update1(Request $request){  // this method will handle the form submission
                                                 // its only save to database


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

public function update(Request $request)
{
    $request->validate([
        'schedule_type' => 'required|in:weekly,monthly',
        'hour' => 'required|integer|min:0|max:23',
        'minute' => 'required|integer|min:0|max:59',
    ]);

    $type = $request->schedule_type;

    if ($type === 'weekly') {

        $request->validate([
            'day' => 'required|integer|min:0|max:6',
        ]);

        // minute hour * * day_of_week
        $cron = sprintf(
            '%d %d * * %d',
            $request->minute,
            $request->hour,
            $request->day
        );

    } else { // monthly

        $request->validate([
            'month_day' => 'required|integer|min:1|max:31',
        ]);

        // minute hour day_of_month * *
        $cron = sprintf(
            '%d %d %d * *',
            $request->minute,
            $request->hour,
            $request->month_day
        );
    }

    ReportSchedule::updateOrCreate(
        ['type' => $type],
        [
            'cron_expression' => $cron,
            'enabled' => true,
        ]
    );

    return back()->with('success', 'Schedule updated successfully');
}

}
