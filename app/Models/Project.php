<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\QaItem;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProjectQAItemStatus;

class Project extends Model
{
    use HasRelationships;
    protected $fillable = [
        'name',
        'client',
        'start_date',
        'due_date',
        'status',
        'pm_id',
    ];

    public function qaStatuses()
    {
        return $this->hasMany(ProjectQAItemStatus::class);
    }

public function pm(){
    return $this->belongsTo(User::class,'pm_id');
}


public function qaItems()
{
    return $this->belongsToMany(QaItem::class, 'project_qa_item_statuses', 'project_id', 'qa_item_id')
                ->withPivot(['status', 'applicable', 'incorporated', 'confirmed'])
                ->withTimestamps();
}

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




public function sheets()
{
    return $this->hasManyThrough(
        Sheet::class,
        Phase::class,
        'project_id', // Foreign key on phases table
        'phase_id',   // Foreign key on sheets table
        'id',         // Local key on projects table
        'id'          // Local key on phases table
    );
}




/*
    public function qaItems()
    {
        return $this->hasManyDeep(
            QaItem::class,
            [Phase::class, Sheet::class],     // ترتيب الجداول الوسيطة
            [
                'project_id',  // phases.project_id
                'phase_id',    // sheets.phase_id
                'sheet_id'     // qa_items.sheet_id
            ],
            [
                'id',          // projects.id
                'id',          // phases.id
                'id'           // sheets.id
            ]
        );
    }

*/



    // Add this to your existing Project model
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->active();
    }

    // Get users by role
    public function getPMs()
    {
        return User::whereHas('assignments', function ($query) {
            $query->where('project_id', $this->id)
                  ->where('role', 'pm')
                  ->active();
        })->get();
    }

    public function getSeniorReviewers()
    {
        return User::whereHas('assignments', function ($query) {
            $query->where('project_id', $this->id)
                  ->where('role', 'senior_reviewer')
                  ->active();
        })->get();
    }

    public function getEngineers()
    {
        return User::whereHas('assignments', function ($query) {
            $query->where('project_id', $this->id)
                  ->where('role', 'engineer')
                  ->active();
        })->get();
    }

    public function assignUser($userId, $role, $phaseId = null, $notes = null)
    {
        return Assignment::create([
            'user_id' => $userId,
            'project_id' => $this->id,
            'phase_id' => $phaseId,
            'role' => $role,
            'assigned_at' => now(),
            'notes' => $notes
        ]);
    }
}