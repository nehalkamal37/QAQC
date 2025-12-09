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
    return $this->hasMany(Sheet::class);
}





    // ============ KANBAN SPECIFIC METHODS ============
    
    public function getKanbanColumns()
    {
        return [
            'open' => [
                'title' => 'Open', 
                'color' => 'bg-red-50', 
                'statuses' => ['open']
            ],
            'in_progress' => [
                'title' => 'In Progress', 
                'color' => 'bg-yellow-50', 
                'statuses' => ['in_progress']
            ],
            'needs_info' => [
                'title' => 'Needs Info', 
                'color' => 'bg-orange-50', 
                'statuses' => ['needs_info']
            ],
            'resolved' => [
                'title' => 'Resolved', 
                'color' => 'bg-green-50', 
                'statuses' => ['resolved']
            ],
            'verified' => [
                'title' => 'Verified', 
                'color' => 'bg-blue-50', 
                'statuses' => ['verified']
            ],
            'closed' => [
                'title' => 'Closed', 
                'color' => 'bg-gray-50', 
                'statuses' => ['closed']
            ],
        ];
    }
/*
    public function getQaItemsWithFilters($filters = [])
    {
        $query = QAItem::whereHas('sheet', function($q) {
            $q->where('phase_id', $this->id);
        })
        ->with(['sheet', 'assignedUser', 'attachments']);

        // Apply filters
        if (!empty($filters['discipline'])) {
            $query->whereHas('sheet', function($q) use ($filters) {
                $q->where('discipline', $filters['discipline']);
            });
        }

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->get()->groupBy('status');
    }
*/
   
 
    


    public function getAssignees()
    {
        $assignees = User::whereIn('id', 
            QAItem::whereHas('sheet', function($q) {
                $q->where('phase_id', $this->id);
            })->pluck('assigned_to')->filter()->unique()
        )->get();

        logger('Assignees found:', ['count' => $assignees->count()]);

        return $assignees;
    }

    public function getDisciplineOptions()
    {
        $options = Sheet::where('phase_id', $this->id)
            ->distinct()
            ->pluck('discipline');

        logger('Discipline options:', ['options' => $options->toArray()]);

        return $options;
    }

// In your Phase model
public function getQaItemsWithFilters($filters = [])
{
    $query = QAItem::whereHas('sheet', function($q) {
        $q->where('phase_id', $this->id);
    })
    ->with(['sheet', 'assignedUser', 'attachments']);

    // DEBUG: Log the filters being applied
    logger('Applying Filters:', [
        'phase_id' => $this->id,
        'filters_received' => $filters
    ]);

    // Apply filters
    if (!empty($filters['discipline'])) {
        $query->whereHas('sheet', function($q) use ($filters) {
            $q->where('discipline', $filters['discipline']);
        });
        
        logger('Applied discipline filter:', ['discipline' => $filters['discipline']]);
    }

    if (!empty($filters['severity'])) {
        $query->where('severity', $filters['severity']);
        logger('Applied severity filter:', ['severity' => $filters['severity']]);
    }

    if (!empty($filters['assigned_to'])) {
        $query->where('assigned_to', $filters['assigned_to']);
        logger('Applied assigned_to filter:', ['assigned_to' => $filters['assigned_to']]);
    }

    if (!empty($filters['category'])) {
        $query->where('category', $filters['category']);
        logger('Applied category filter:', ['category' => $filters['category']]);
    }

    $results = $query->get();
    
    logger('Filter Results:', [
        'total_results' => $results->count(),
        'status_distribution' => $results->groupBy('status')->map->count()
    ]);

    return $results;
}
    
}
