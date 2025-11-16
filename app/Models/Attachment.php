<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


}
