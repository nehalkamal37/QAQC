<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_description',
        'is_checked',
        'category',
        'sub_category',
        'project_name',
        'project_number'
    ];
}