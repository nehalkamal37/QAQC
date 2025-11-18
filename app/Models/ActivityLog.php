<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'project_id',
        'phase_id',
        'sheet_id',
        'qa_item_id',
        'user_id',
        'action_type',
        'old_value',
        'new_value',
        'note',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    // Relations
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
        return $this->belongsTo(QAItem::class, 'qa_item_id');
    }

    public function sheet() {
        return $this->belongsTo(Sheet::class);
    }

    public function phase() {
        return $this->belongsTo(Phase::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
