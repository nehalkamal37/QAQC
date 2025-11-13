<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
   
    protected $fillable = [
        'name',
        'client',
        'start_date',
        'due_date',
        'status',
    ];

    public function phases()
{
    return $this->hasMany(Phase::class);
}

}
