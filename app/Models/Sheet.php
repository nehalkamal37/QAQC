<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase_id',
        'discipline',
        'number',
        'title',
        'version',
        'status'
    ];

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function qaItems()
    {
        return $this->hasMany(QAItem::class);
    }
}
