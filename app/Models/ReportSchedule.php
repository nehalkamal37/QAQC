<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    protected $fillable = [
        'type',
        'cron_expression',
        'enabled',
    ];


}
