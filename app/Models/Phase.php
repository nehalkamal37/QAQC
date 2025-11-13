<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{

    protected $fillable = ['project_id', 'type', 'due_date', 'status'];

    public function project()
{
    return $this->belongsTo(Project::class);
}

public function sheets()
{
    return $this->hasMany(Sheet::class);
}

}
