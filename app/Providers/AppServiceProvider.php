<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

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
        View::composer('admin.*', function ($view) {
            $view->with([
                'pendingLeaveCount' => LeaveRequest::where('status', 'pending')->count(),
                'pendingUsersCount' => User::where('status', 'pending')->count(),
            ]);
        });
    }

}