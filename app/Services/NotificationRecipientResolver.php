<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\QAItem;
use App\Models\Project;

class NotificationRecipientResolver
{
    
    public static function resolve(ActivityLog $log): array
    {
        return match ($log->action_type) {

            // QA Item assigned -> assignee only
            'qa_item_assigned' => self::qaItemAssigned($log),

            // Status change -> PM + Senior Reviewer(s)
            'status_change' => self::statusChanged($log),

            // Review added -> assignee (+ optional PM)
            'review_added' => self::reviewAdded($log),

            // Attachment uploaded -> reviewers
            'attachment_uploaded' => self::attachmentUploaded($log),

            default => [],
        };
    }

    private static function qaItemAssigned(ActivityLog $log): array
    {
        // assume you store assigned_to in new_value JSON
        $new = is_array($log->new_value) ? $log->new_value : json_decode($log->new_value, true);
        $assigneeId = $new['assigned_to'] ?? null;

        return $assigneeId ? [$assigneeId] : [];
    }
/*
    private static function statusChanged(ActivityLog $log): array
    {
        // project PM + senior reviewers on that project
        $pmId = Project::where('id', $log->project_id)->value('pm_id');

        $reviewers = Assignment::where('project_id', $log->project_id)
            ->whereIn('role', ['senior_reviewer'])
            ->pluck('user_id')
            ->toArray();

        return array_values(array_unique(array_filter(array_merge([$pmId], $reviewers))));
    }
*/
    private static function statusChanged(ActivityLog $log): array  // send to PM and senior reviewers
{
    $pmFromProject = Project::where('id', $log->project_id)->value('pm_id');

    $pmFromAssignment = Assignment::where('project_id', $log->project_id)
        ->where('role', 'pm')
        ->pluck('user_id')
        ->toArray();

    $reviewers = Assignment::where('project_id', $log->project_id)
        ->whereIn('role', ['senior_reviewer'])
        ->pluck('user_id')
        ->toArray();

    return array_values(array_unique(
        array_filter(array_merge(
            [$pmFromProject],
            $pmFromAssignment,
            $reviewers
        ))
    ));
}

/*
    private static function reviewAdded(ActivityLog $log): array
    {
        $qaItem = QAItem::find($log->qa_item_id);
        if (!$qaItem) return [];

        $pmId = Project::where('id', $log->project_id)->value('pm_id');

        // optional: include PM
        return array_values(array_unique(array_filter([$qaItem->assigned_to, $pmId])));
    }
*/

    private static function reviewAdded(ActivityLog $log): array // send to assignee + optional PM
{
    $assigneeId = \App\Models\ProjectQAItemStatus::where('project_id', $log->project_id)
        ->where('qa_item_id', $log->qa_item_id)
        ->value('assigned_to');

    $pmId = Project::where('id', $log->project_id)->value('pm_id');

    if (!$pmId) {
        $pmId = Assignment::where('project_id', $log->project_id)
            ->where('role', 'pm')
            ->value('user_id'); // 👈 value مش pluck
    }

    return array_values(array_unique(
        array_filter([$assigneeId, $pmId])
    ));
}

   

private static function attachmentUploaded(ActivityLog $log): array 
 // send to reviewers, pm and assignee if not uploader 
{

        $recipients = [];

        // PM
    $pmId = Project::where('id', $log->project_id)->value('pm_id');

  if (!$pmId) {
    $pmId = Assignment::where('project_id', $log->project_id)
        ->where('role', 'pm')
        ->value('user_id'); // 👈 value مش pluck
    }
      // Senior Reviewers
    $reviewers = Assignment::where('project_id', $log->project_id)
        ->where('role', 'senior_reviewer')
        ->pluck('user_id')
        ->toArray();

           $recipients = array_merge($recipients, $reviewers);

        $assigneeId = \App\Models\ProjectQAItemStatus::where('project_id', $log->project_id)
        ->where('qa_item_id', $log->qa_item_id)
        ->value('assigned_to');



        if($assigneeId && $assigneeId !== $l0og->user_id){
            $recipients[] = $assigneeId;
        }

        return array_values(array_unique($recipients));

}

    
}
