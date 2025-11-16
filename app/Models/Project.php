<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
   
    protected $fillable = [
        'name',
        'client',
        'start_date',
        'due_date',
        'status',
    ];

    public function phases()
{
    return $this->hasMany(Phase::class);
}


public function completionPercentage()
{
    $phases = $this->phases;
    if ($phases->count() == 0) return 0;

    $sum = 0;
    foreach ($phases as $phase) {
        $sum += $phase->completionPercentage();
    }

    return round($sum / $phases->count()); // Average completion percentage

}

public function isCompleted()
{
    return $this->completionPercentage() == 100;
}

}
