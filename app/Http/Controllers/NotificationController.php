<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   // app/Http/Controllers/NotificationController.php
public function index()


{
 //   $notifications = auth()->user()->userNotifications()->latest()->paginate(20);

    $notifications = auth()->user()
    ->userNotifications()
    ->with('actor')
    ->latest()
    ->paginate(20);

    return view('notifications.index', compact('notifications'));
}



    public function destroy(Notification $notification)
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();
        return back()->with('success', 'Notification deleted');
    }

  
    

    public function markAsRead(Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return back()->with('success', 'Notification marked as read');
    }

    
    public function markAllAsRead()
    {
        auth()->user()->userNotifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'All notifications marked as read');
    }

  

}