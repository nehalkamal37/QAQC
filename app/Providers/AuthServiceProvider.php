<?php 

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\QaItem;
use App\Policies\QaItemPolicy;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        QaItem::class => QaItemPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Missing: Gate definitions for role-based access
// Suggested in AuthServiceProvider:
Gate::define('manage-project', function (User $user, Project $project) {
    return $user->assignments()
        ->where('project_id', $project->id)
        ->whereIn('role', ['pm', 'admin'])
        ->active()
        ->exists();
});

Gate::define('review-qa', function (User $user, Project $project) {
    return $user->assignments()
        ->where('project_id', $project->id)
        ->whereIn('role', ['pm', 'senior_reviewer'])
        ->active()
        ->exists();
});
    }


}