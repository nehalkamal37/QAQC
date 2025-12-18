<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\QAItem;
use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;

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
   
    public function boot()
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



}
