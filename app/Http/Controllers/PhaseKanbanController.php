<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\QAItem; // Add this import
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhaseKanbanController extends Controller
{

    public function show(Phase $phase, Request $request)
    {
        // Simple role-based authorization
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Please log in.');
        }

        // Define allowed roles for kanban access
        $allowedRoles = ['Admin', 'PM', 'Senior Reviewer', 'Reviewer', 'Engineer', 'Night Vision'];
        
        if (!in_array($user->role->name, $allowedRoles)) {
            abort(403, "Your role ({$user->role->name}) does not have access to the kanban board.");
        }


 

    // Use direct request input instead of array_merge
    $filters = [
        'discipline' => $request->input('discipline', ''),
        'severity' => $request->input('severity', ''),
        'assigned_to' => $request->input('assigned_to', ''),
        'category' => $request->input('category', '')
    ];

    // DEBUG: Let's see what's happening
    \Log::info('Filter Debug:', [
        'url_params' => $request->all(),
        'processed_filters' => $filters
    ]);

  
        // Get QA items with proper grouping
        $qaItems = $phase->getQaItemsWithFilters($filters);
        
        // DEBUG: Check the actual status values
        logger('QA Items Status Values:', [
            'unique_statuses' => $qaItems->pluck('status')->unique()->values(),
            'first_item_status' => $qaItems->first() ? $qaItems->first()->status : 'no items',
            'all_statuses' => $qaItems->pluck('status')->toArray()
        ]);


        $assignees = $phase->getAssignees();
        $disciplineOptions = $phase->getDisciplineOptions();
        $statuses = QAItem::STATUSES;

        // DEBUG: Pass debug info to view
        $debugInfo = [
            'sheets_count' => $phase->sheets()->count(),
            'total_qa_items' => $qaItems->count(),
            'qa_items_by_status' => $qaItems->groupBy('status')->map->count()->toArray(),
            'unique_statuses_found' => $qaItems->pluck('status')->unique()->values()->toArray()
        ];

        return view('phases.kanban', compact(
            'phase', 'qaItems', 'assignees', 'disciplineOptions', 'filters', 'statuses', 'debugInfo'
        ));
    }

    


    public function updateQaItemStatus(Request $request, QAItem $qaItem)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,needs_info,resolved,verified,closed',
            'note' => 'sometimes|string|max:500'
        ]);

        // Check permission
        if (!auth()->user()->can('update', $qaItem)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        DB::transaction(function () use ($request, $qaItem) {
            $oldStatus = $qaItem->status;
            $newStatus = $request->status;

            $qaItem->update([
                'status' => $newStatus
            ]);

            // Log the status change
            $this->logStatusChange($qaItem, $oldStatus, $newStatus, $request->note);
        });

        return response()->json([
            'success' => true,
            'message' => 'QA item status updated successfully'
        ]);
    }
/*
    public function bulkUpdate(Request $request, Phase $phase)
    {
        $request->validate([
            'qa_item_ids' => 'required|array',
            'qa_item_ids.*' => 'exists:qa_items,id',
            'action' => 'required|in:assign,update_status',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
            'status' => 'required_if:action,update_status|in:open,in_progress,needs_info,resolved,verified,closed'
        ]);

        // Check permission
        if (!auth()->user()->can('update', $phase)) {
            return back()->with('error', 'Unauthorized action.');
        }

        $qaItemIds = $request->qa_item_ids;

        DB::transaction(function () use ($request, $qaItemIds, $phase) {
            $qaItems = QAItem::whereIn('id', $qaItemIds)
                ->whereHas('sheet', function ($q) use ($phase) {
                    $q->where('phase_id', $phase->id);
                })
                ->get();

            foreach ($qaItems as $qaItem) {
                switch ($request->action) {
                    case 'assign':
                        $qaItem->update(['assigned_to' => $request->assigned_to]);
                        $this->logBulkAction($qaItem, 'assigned', [
                            'to' => User::find($request->assigned_to)->name
                        ]);
                        break;

                    case 'update_status':
                        $oldStatus = $qaItem->status;
                        $qaItem->update(['status' => $request->status]);
                        $this->logStatusChange($qaItem, $oldStatus, $request->status, "Bulk status update");
                        break;
                }
            }
        });

        return back()->with('success', 'Bulk action completed successfully');
    }
*/
    // In PhaseKanbanController
public function bulkUpdate(Request $request, Phase $phase)
{
    $request->validate([
        'qa_item_ids' => 'required|array',
        'qa_item_ids.*' => 'exists:qa_items,id',
        'action' => 'required|in:assign,update_status',
        'assigned_to' => 'required_if:action,assign|exists:users,id',
        'status' => 'required_if:action,update_status|in:open,in_progress,needs_info,resolved,verified,closed'
    ]);

    DB::transaction(function () use ($request, $phase) {
        $qaItems = QAItem::whereIn('id', $request->qa_item_ids)
            ->whereHas('sheet', function ($q) use ($phase) {
                $q->where('phase_id', $phase->id);
            })
            ->get();

        foreach ($qaItems as $qaItem) {
            if ($request->action === 'assign') {
                $qaItem->update(['assigned_to' => $request->assigned_to]);
            } elseif ($request->action === 'update_status') {
                $qaItem->update(['status' => $request->status]);
            }
        }
    });

    return back()->with('success', 'Bulk action completed successfully.');
}
    private function logStatusChange($qaItem, $fromStatus, $toStatus, $note = null)
    {
        // You can create an audit log here
        // For now, we'll just update the QA item
        activity()
            ->performedOn($qaItem)
            ->causedBy(auth()->user())
            ->withProperties([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note
            ])
            ->log('status_changed');
    }

    private function logBulkAction($qaItem, $action, $data = [])
    {
        activity()
            ->performedOn($qaItem)
            ->causedBy(auth()->user())
            ->withProperties($data)
            ->log($action);
    }


    // Add to PhaseKanbanController
public function getItemDetails(QAItem $qaItem)
{
    $item = $qaItem->load([
        'assignedUser',
        'sheet',
        'attachments',
        'reviews.user'
    ]);

    return response()->json([
        'success' => true,
        'item' => [
            'id' => $item->id,
            'item_description' => $item->item_description,
            'notes' => $item->notes,
            'status' => $item->status,
            'severity' => $item->severity,
            'due_date' => $item->due_date,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'assigned_user' => $item->assignedUser ? [
                'id' => $item->assignedUser->id,
                'name' => $item->assignedUser->name,
                'email' => $item->assignedUser->email
            ] : null,
            'sheet' => $item->sheet ? [
                'id' => $item->sheet->id,
                'number' => $item->sheet->number,
                'title' => $item->sheet->title
            ] : null,
            'attachments' => $item->attachments->map(function($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'url' => Storage::url($attachment->path),
                    'created_at' => $attachment->created_at
                ];
            }),
            'reviews' => $item->reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'user' => $review->user->name,
                    'comments' => $review->comments,
                    'created_at' => $review->created_at->format('M d, Y H:i')
                ];
            })
        ]
    ]);
}
}