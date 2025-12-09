<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcItem extends Model
{
    protected $fillable = [
        'item_text',
        'applicable',
        'incorporated',
        'confirmed',
    ];
}
