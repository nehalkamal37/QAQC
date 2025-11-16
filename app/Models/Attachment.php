<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\QAItem;
use App\Models\User;

class Attachment extends Model
{
    //

    protected $fillable = [
        'qa_item_id', 'user_id', 'path', 'filename', 'mime'
    ];

    public function qaItem()
    {
        return $this->belongsTo(QAItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function attachable()
{
    return $this->morphTo();
}


}
