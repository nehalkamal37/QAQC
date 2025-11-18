<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;;
use App\Models\Sheet;


class Phase extends Model
{

    protected $fillable = ['project_id', 'type', 'due_date', 'status'];

    public function project()
{
    return $this->belongsTo(Project::class);
}




    // Allowed statuses
  public const PLANNING          = 'planning';
public const IN_REVIEW         = 'in_review';
public const CHANGES_REQUIRED  = 'changes_required';
public const READY_FOR_SIGNOFF = 'ready_for_signoff';
public const CLOSED            = 'closed';

public static function statuses()
{
    return [
        self::PLANNING,
        self::IN_REVIEW,
        self::CHANGES_REQUIRED,
        self::READY_FOR_SIGNOFF,
        self::CLOSED,
    ];
}

public function canTransitionTo($to)
{
    $map = [
        self::PLANNING          => [self::IN_REVIEW],
        self::IN_REVIEW         => [self::CHANGES_REQUIRED, self::READY_FOR_SIGNOFF],
        self::CHANGES_REQUIRED  => [self::IN_REVIEW],
        self::READY_FOR_SIGNOFF => [self::CLOSED],
        self::CLOSED            => [],
    ];

    return in_array($to, $map[$this->status] ?? []);
}


public function completionPercentage()
{
    $sheets = $this->sheets;
    if ($sheets->count() == 0) return 0;

    $sum = 0;
    foreach ($sheets as $sheet) {
        $sum += $sheet->completionPercentage();  // 
    }

    return round($sum / $sheets->count());
}

public function isCompleted()
{
    return $this->completionPercentage() == 100;
}

public function sheets()
{
    return $this->hasManyThrough(
        Sheet::class,
        Phase::class,
        'project_id', // phases.project_id
        'phase_id',   // sheets.phase_id
        'id',         // projects.id
        'id'          // phases.id
    );
}


}
