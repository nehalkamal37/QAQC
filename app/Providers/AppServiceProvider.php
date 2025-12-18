<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\QAItem;
use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   
    public function boot1()
    {
        // Share assignedCount with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $assignedCount = QAItem::where('assigned_to', Auth::id())
                    ->whereNotIn('status', ['closed', 'verified'])
                    ->count();
                
                $view->with('assignedCount', $assignedCount);
            } else {
                $view->with('assignedCount', 0);
            }
        });
        
        ActivityLog::observe(ActivityLogObserver::class);

}


public function boot()
    {
View::composer('*', function ($view) {
    if (!Auth::check()) {
        return;
    }

    $userId = Auth::id();

    $assignedCount = Cache::remember(
        "assigned_count_user_{$userId}",
        now()->addMinutes(5),
        function () use ($userId) {
            return QAItem::where('assigned_to', $userId)
                ->whereNotIn('status', ['closed', 'verified'])
                ->count();
        }
    );

    $view->with('assignedCount', $assignedCount);
});


        ActivityLog::observe(ActivityLogObserver::class);


    }
}
