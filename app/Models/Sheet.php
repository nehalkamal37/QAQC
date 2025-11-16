<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase_id',
        'discipline',
        'number',
        'title',
        'version',
        'status'
    ];

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function qaItems()
    {
        return $this->hasMany(QAItem::class);
    }


    // Calculate completion percentage based on associated QA items
    public function completionPercentage()
{
    $total = $this->qaItems()->count();
    if ($total == 0) return 0;

    $completed = $this->qaItems()->whereIn('status', ['verified', 'closed'])->count();

    return round(($completed / $total) * 100);
}

public function isCompleted()
{
    return $this->completionPercentage() == 100;
}

}
