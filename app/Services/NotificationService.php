<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserNotificationMail;
use App\Events\NotificationCreated;

class NotificationService
{

    /*
    public static function send($userId, $type, $title, $message, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false
        ]);
    }
        */

    //new
    
/*
public static function send($userId, $type, $title, $message, $data = null)
{
    // Save notification in DB
    $notification = Notification::create([
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'data' => $data,
        'is_read' => false
    ]);

    // Send email version
    $user = User::find($userId);

    if ($user && $user->email) {
        BrevoMailService::send(
    $user->email,
    $title,
    "<p>$message</p>"
);

    //    Mail::to($user->email)->send(
      //      new UserNotificationMail($title, $message)   );
        
    }
        

    return $notification;
}

*/


public static function send(
    int $userId,
    string $type,
    string $title,
    string $message,
    array $data = [],
    ?int $actorId = null,
    ?string $subjectType = null,
    ?int $subjectId = null
) {
    $notification = Notification::create([
        'user_id'      => $userId,
        'actor_id'     => $actorId,
        'type'         => $type,
        'title'        => $title,
        'message'      => $message,
        'data'         => $data,
        'subject_type' => $subjectType,
        'subject_id'   => $subjectId,
        'is_read'      => false,
    ]);


broadcast(new NotificationCreated($notification))->toOthers();

    // Email (unchanged)
    $user = User::find($userId);
    if ($user && $user->email) {
        BrevoMailService::send(
            $user->email,
            $title,
            "<p>$message</p>"
        );
    }

    return $notification;
}


    //  old
    // Send to multiple users
    public static function sendToMany($userIds, $type, $title, $message, $data = null)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Notification::insert($notifications);
    }

    // Specific notification types


public static function notifyAssignment($userId, $projectName, $role, $assignedBy)
{
    return self::send(
        $userId,
        'assignment',
        'New Assignment',
        "You have been assigned as {$role} to project: {$projectName}",
        ['assigned_by' => $assignedBy, 'type' => 'assignment']
    );
}
/*
    public static function notifyQaItemAssigned1($userId, $itemId, $projectName)
    {
        return self::send(
            $userId,
            'qa_item_assigned',
            'QA Item Assigned',
            "A new QA item has been assigned to you in project: {$projectName}",
            ['qa_item_id' => $itemId, 'type' => 'qa_item_assigned']  
        );

    }

    public static function notifyQaItemAssigned($userId, $itemId, $projectName)
{
    return self::send(
        $userId,
        'qa_item_assigned',
        'QA Item Assigned',
        "QA item #{$itemId} has been assigned to you in project: {$projectName}",
        ['qa_item_id' => $itemId, 'type' => 'qa_item_assigned']
    );
}

    public static function notifyStatusChange($userId, $itemId, $oldStatus, $newStatus)
    {
        return self::send(
            $userId,
            'status_change',
            'Status Updated',
            "QA item #{$itemId} status changed from {$oldStatus} to {$newStatus}",
            ['qa_item_id' => $itemId, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

/*
    public static function notifyDueDateReminder($userId, $itemId, $daysUntilDue)
    {
        return self::send(
            $userId,
            'due_date_reminder',
            'Due Date Approaching',
            "QA item #{$itemId} is due in {$daysUntilDue} days",
            ['qa_item_id' => $itemId, 'days_until_due' => $daysUntilDue]
        );
    }

public static function notifyDueDateReminder($userId, $itemId, $daysUntilDue)
{
    // Fix the calculation to handle overdue items
    if ($daysUntilDue < 0) {
        $daysOverdue = abs($daysUntilDue);
        $message = $daysOverdue == 1 
            ? "QA item #{$itemId} is 1 day overdue!"
            : "QA item #{$itemId} is {$daysOverdue} days overdue!";
            
        $title = "Item Overdue";
    } else if ($daysUntilDue == 0) {
        $message = "QA item #{$itemId} is due today!";
        $title = "Due Today";
    } else if ($daysUntilDue == 1) {
        $message = "QA item #{$itemId} is due in 1 day";
        $title = "Due Tomorrow";
    } else {
        $message = "QA item #{$itemId} is due in {$daysUntilDue} days";
        $title = "Due Date Reminder";
    }
        
    return self::send(
        $userId,
        'due_date_reminder',
        $title,
        $message,
        ['qa_item_id' => $itemId, 'days_until_due' => $daysUntilDue]
    );
}
*/



/// new enhanced algorithm


/*
public static function notifyStatusChange(
    int $recipientId,
    int $actorId,
    int $qaItemId,
    string $oldStatus,
    string $newStatus
) {
    return self::send(
        userId: $recipientId,
        type: 'status_change',
        title: 'QA Item Status Updated',
        message: "changed QA item #{$qaItemId} status from {$oldStatus} to {$newStatus}",
        data: [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ],
        actorId: $actorId,
        subjectType: 'QAItem',
        subjectId: $qaItemId
    );
}
*/

}