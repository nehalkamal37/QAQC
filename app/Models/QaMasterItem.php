<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QaMasterItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'discipline',
        'type',
        'category',
        'item',
        'notes',
    ];
}
