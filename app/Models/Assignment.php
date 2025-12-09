<?php
// app/Models/Assignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'phase_id',
        'role',
        'is_active',
        'assigned_at',
        'removed_at',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForPhase($query, $phaseId)
    {
        return $query->where('phase_id', $phaseId);
    }

    // Helper methods
    public function activate()
    {
        $this->update(['is_active' => true, 'removed_at' => null]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false, 'removed_at' => now()]);
    }

    public function isPM()
    {
        return $this->role === 'pm';
    }

    public function isSeniorReviewer()
    {
        return $this->role === 'senior_reviewer';
    }

    public function isEngineer()
    {
        return $this->role === 'engineer';
    }


    // Add these methods to your Assignment model

public function getRoleDisplayName(): string
{
    return match($this->role) {
        'pm' => 'Project Manager',
        'senior_reviewer' => 'Senior Reviewer',
        'engineer' => 'Engineer',
        'eit' => 'EIT',
        'night_vision' => 'Night Vision',
        default => ucfirst(str_replace('_', ' ', $this->role))
    };
}

public function getRoleBadgeColor(): string
{
    return match($this->role) {
        'pm' => 'primary',
        'senior_reviewer' => 'success',
        'engineer' => 'info',
        'eit' => 'warning',
        'night_vision' => 'dark',
        default => 'secondary'
    };
}
public function getRoleIcon(): string
{
    return match($this->role) {
        'pm' => 'fas fa-user-tie',
        'senior_reviewer' => 'fas fa-check-double',
        'engineer' => 'fas fa-cogs',
        'eit' => 'fas fa-hard-hat',
        'night_vision' => 'fas fa-moon',
        default => 'fas fa-user'
    };

}
}