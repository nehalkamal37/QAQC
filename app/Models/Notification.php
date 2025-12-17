<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'is_read', 'read_at','actor_id','subject_type','subject_id'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    // Scope for unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // app/Models/User.php
public function notifications()
{
    return $this->hasMany(Notification::class);
}


    // Helper عشان نجيب لينك الـ QA item
    public function getQaItemLinkAttribute()
    {
        if (!isset($this->data['qa_item_id'])) {
            return null;
        }

        $qaItem = \App\Models\QAItem::find($this->data['qa_item_id']);
        if (!$qaItem) {
            return null;
        }

        // صفحة الـ QA items + هاش للـ row بتاع الـ item
        return route('qa_items.index', $qaItem->sheet_id) . '#item-' . $qaItem->id;
    }

public function actor()
{
    return $this->belongsTo(\App\Models\User::class, 'actor_id');
}

public function subject()
{
    return $this->morphTo(null, 'subject_type', 'subject_id');
}

}