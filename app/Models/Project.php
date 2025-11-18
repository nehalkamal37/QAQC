<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\QaItem;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Project extends Model
{
    use HasRelationships;
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



}
