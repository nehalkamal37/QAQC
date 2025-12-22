<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\Sheet;
use App\Models\ProjectQAItemStatus;
use App\Models\QaItemReview;
use App\Models\Phase;
use App\Models\User;
use App\Models\Attachment;

class QAItem extends Model
{
    use HasFactory;

    protected $table = 'qa_items'; // 👈 أضف هذا السطر لتحديد اسم الجدول الصحيح

  
    protected $fillable = [
    'sheet_id', 'item_description', 'category', 'type', 'notes',
    'assigned_to', 'due_date', 'status', 
    'comments',
    'severity',
    'checkbox_values'

];

public const STATUSES = [
    'open',
    'in_progress',
    'needs_info',
    'resolved',
    'verified',
    'closed',
];

public static function isValidStatus($status)
{
    return in_array($status, [
        'open',
        'in_progress',
        'needs_info',
        'resolved',
        'verified',
        'closed'
    ]);
}

   
    public function projectStatuses()
    {
        return $this->hasOne(ProjectQAItemStatus::class);
    }

    /**
     * QA Item is assigned to many projects.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_qa_item_statuses')
                    ->withPivot('applicable', 'incorporated', 'confirmed', 'comments', 'due_date', 'assigned_to')
                    ->withTimestamps();
    }

    

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }


public function reviews()
{
    return $this->hasMany(QaItemReview::class, 'qa_item_id');
}


// Calculate completion percentage based on status
public function completionPercentage()
{
    $status = $this->status;
    switch ($status) {
        case 'closed':
        case 'verified':   
            return 100; 
        case 'resolved':
            return 75;
        case 'in_progress':
            return 50;
        case 'needs_info':
            return 25;
        case 'open':
        default:
            return 0;
    }
}

public function isCompleted()
{
    return in_array($this->status, ['verified', 'closed']);
}

// Attachments relationship
/*
public function attachments()
{
    return $this->hasMany(Attachment::class, 'qa_item_id');
}
*/

public function attachments()
{
    return $this->morphMany(Attachment::class, 'attachable');
}




    // Option 1: Using $dates (Laravel 7 and below)
    protected $dates = ['due_date'];

    // Option 2: Using $casts (Laravel 8 and above - RECOMMENDED)
    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    'checkbox_values' => 'array',

    ];

    // Add this method to check if due date is past
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast();
    }

    // Status color method
    public function getStatusColor()
    {
        return match($this->status) {
            'open' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'verified' => 'primary',
            'closed' => 'secondary',
            default => 'dark',
        };
    }

    
public function getAssignedToAttribute($value)
{
    // لو جاي من join كـ project_assigned_to نرجّحه
    if (array_key_exists('project_assigned_to', $this->attributes) 
        && $this->attributes['project_assigned_to']) {

        return $this->attributes['project_assigned_to'];
    }

    // otherwise return original assigned_to
    return $value;
}

    

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // If you have a direct phase relationship
    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

 // Helper to check if assigned to current user
    public function isAssignedToMe()
    {
        return $this->assigned_to === auth()->id();
    }

    public function getStatusIcon()
    {
        return match($this->status) {
            'open' => 'fas fa-exclamation-circle',
            'in_progress' => 'fas fa-spinner',
            'needs_info' => 'fas fa-info-circle',
            'resolved' => 'fas fa-check-circle',
            'verified' => 'fas fa-thumbs-up',
            'closed' => 'fas fa-lock',
            default => 'fas fa-question-circle',
        };
    }



public function projectStatus()
{
    return $this->hasOne(ProjectQAItemStatus::class, 'qa_item_id');
}

public function assigneeUser()
{
    return $this->belongsTo(User::class, 'assigned_to');
}




}
