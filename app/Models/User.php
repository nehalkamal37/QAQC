<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\QAItem;
use App\Models\Role;
use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function role()
{
    return $this->belongsTo(Role::class);
}

public function hasRole($roles)
{
    // نعرف خريطة IDs إلى أسماء الأدوار
    $roleMap = [
        1 => 'Admin',
        2 => 'PM',
        3 => 'Senior Reviewer',
        4 => 'Engineer',
        5 => 'Night Vision',
        6 => 'Reviewer',

    ];

    // نجيب اسم الدور الحالي بناءً على role_id
    $currentRole = $roleMap[$this->role_id] ?? null;

    // لو اللي جاي Array (مثلاً ['Reviewer', 'PM'])
    if (is_array($roles)) {
        return in_array($currentRole, $roles);
    }

    // لو جاي single string (مثلاً 'Admin')
    return $currentRole === $roles;
}

public function qaItemsAssigned()
{
    return $this->hasMany(QaItem::class, 'assigned_to', 'name'); 
}




    // Add this to your existing User model
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->active();
    }

    // Helper methods for role checking
    public function isAssignedToProject($projectId): bool
    {
        return $this->activeAssignments()->where('project_id', $projectId)->exists();
    }

    public function getProjectRole($projectId): ?string
    {
        $assignment = $this->activeAssignments()
            ->where('project_id', $projectId)
            ->first();

        return $assignment ? $assignment->role : null;
    }

    public function getAssignedProjects()
    {
        return Project::whereHas('assignments', function ($query) {
            $query->where('user_id', $this->id)->active();
        })->get();
    }

    public function assignedTo(Project $project): bool
    {
        return $this->activeAssignments()
            ->where('project_id', $project->id)
            ->exists();
    }

    // app/Models/User.php
// Change from 'notifications' to 'userNotifications'
public function userNotifications()
{
    return $this->hasMany(\App\Models\Notification::class);
}

public function unreadUserNotifications()
{
    return $this->userNotifications()->where('is_read', false);
}
}