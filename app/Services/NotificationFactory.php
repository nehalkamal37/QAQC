<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\ProjectQAItemStatus;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserNotificationMail;

class NotificationFactory


{
    
    public static function fromActivity(ActivityLog $log): void
{
    $actorId = $log->user_id;

    // ================= STATUS CHANGE =================
    if ($log->action_type === 'status_change') {

        $recipients = NotificationRecipientResolver::resolve($log); // here we get to know who to notify
        if (empty($recipients)) return;

        $old = self::normalizeValue($log->old_value);
        $new = self::normalizeValue($log->new_value);

        foreach ($recipients as $recipientId) {
            if ($recipientId == $actorId) continue;

            NotificationService::send(
                userId: $recipientId,
                type: 'status_change',
                title: 'QA Item Status Updated',
                message: "changed QA item #{$log->qa_item_id} status",
                data: [
                    'old' => $old,
                    'new' => $new,
                   'qa_item_id' => $log->qa_item_id,

                ],
                actorId: $actorId,
                subjectType: 'QAItem',
                subjectId: $log->qa_item_id
            );
        }
    }

    // ================= ASSIGNMENT =================
    if ($log->action_type === 'qa_item_assigned') {

        $recipients = NotificationRecipientResolver::resolve($log); // here we get to know who to notify
        if (empty($recipients)) return;

        foreach ($recipients as $recipientId) {
            if ($recipientId == $actorId) continue;

            NotificationService::send(
                userId: $recipientId,
                type: 'qa_item_assigned',
                title: 'QA Item Assigned',
                message: "assigned QA item #{$log->qa_item_id} to you",
                data: [
                    'qa_item_id' => $log->qa_item_id,
                ],
                actorId: $actorId,
                subjectType: 'QAItem',
                subjectId: $log->qa_item_id
            );
        }
    }

    // ================= DUE DATE CHANGED =================
    if ($log->action_type === 'due_date_changed') {  

    $old = self::normalizeValue($log->old_value);
    $new = self::normalizeValue($log->new_value);

    $oldDate = !empty($old['due_date'])
        ? \Carbon\Carbon::parse($old['due_date'])->format('M d, Y')
        : 'N/A';

    $newDate = !empty($new['due_date'])
        ? \Carbon\Carbon::parse($new['due_date'])->format('M d, Y')
        : 'N/A';

    $assigneeId = \App\Models\ProjectQAItemStatus::where('project_id', $log->project_id)  //get to know who is assigned to this QA item
        ->where('qa_item_id', $log->qa_item_id)
        ->value('assigned_to');

    if (!$assigneeId || $assigneeId == $log->user_id) return;

    NotificationService::send(
        userId: $assigneeId,
        type: 'due_date_changed',
        title: 'Due Date Updated',
        message: "updated due date for QA item #{$log->qa_item_id}  to {$newDate}",
        data: [
            'old_due_date' => $old['due_date'] ?? null,
            'new_due_date' => $new['due_date'] ?? null,
                                'qa_item_id' => $log->qa_item_id,

        ],
        actorId: $log->user_id,
       
        subjectType: 'QAItem',
        subjectId: $log->qa_item_id
    );
}

}


    private static function normalizeValue($value): array  // helper to decode JSON or return array
{
    if (is_array($value)) {
        return $value;
    }

    if (is_string($value)) {
        return json_decode($value, true) ?? [];
    }

    return [];
}

}
