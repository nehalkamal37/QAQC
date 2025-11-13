<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QAItem extends Model
{
    use HasFactory;

    protected $table = 'qa_items'; // 👈 أضف هذا السطر لتحديد اسم الجدول الصحيح

  
    protected $fillable = [
    'sheet_id', 'item_description', 'category', 'type', 'notes',
    'assigned_to', 'due_date', 'status', 'severity'
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

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }


public function reviews()
{
    return $this->hasMany(QaItemReview::class, 'qa_item_id');
}


}
