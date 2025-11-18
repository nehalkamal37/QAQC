<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QAItem;
use App\Models\User;

class QaItemReview extends Model
{
    use HasFactory;

 
 
    protected $fillable = [
        'qa_item_id',
        'user_id',
        'role',
        'status',
        'comment'
    ];

 
    public const REVIEW_STATUSES = [
        'noted',
        'open',
        'in_progress',
        'resolved',
        'verified',
        'closed',
    ];


    public static function isValidStatus($status)
    {
        return in_array($status, self::REVIEW_STATUSES);
    }


    public function qaItem()
    {
        return $this->belongsTo(QaItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function item()
    {
        return $this->belongsTo(QAItem::class, 'qa_item_id');
    }

}
