<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\QAItem;
use App\Models\User;

class ProjectQAItemStatus extends Model
{
    protected $table = 'project_qa_item_statuses';

    protected $fillable = [
        'project_id',
        'qa_item_id',
        'applicable',
        'incorporated',
        'confirmed',
        'comments',
        'due_date',
        'assigned_to',
        'category',
        'title'
    ];

    protected $casts = [
        'applicable'   => 'boolean',
        'incorporated' => 'boolean',
        'confirmed'    => 'boolean',
        'due_date'     => 'date',
    ];

    /**
     * Belongs to Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Belongs to QA Item Template
     */

   

    public function qaItem()
    {
        return $this->belongsTo(QAItem::class, 'qa_item_id');
    }

  
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getDynamicStatusAttribute()
{
    // Manual statuses override logic
    if (in_array($this->attributes['status'] ?? '', ['verified', 'closed'])) {
        return $this->attributes['status'];
    }

    $a = $this->applicable;
    $i = $this->incorporated;
    $c = $this->confirmed;

    // Logic
    if (!$a && !$i && !$c) return 'open';
    if ($a && !$i && !$c) return 'in_progress';
    if ($a && $i && !$c) return 'needs_info';
    if ($a && $i && $c) return 'resolved';

    return 'open';
}

// for dashboard summary
public function getDerivedStatusAttribute()
{
    if (!$this->applicable) return 'open';
    if ($this->applicable && !$this->incorporated) return 'in_progress';
    if ($this->applicable && $this->incorporated && !$this->confirmed) return 'resolved';
    if ($this->applicable && $this->incorporated && $this->confirmed) return 'closed';
}


}
