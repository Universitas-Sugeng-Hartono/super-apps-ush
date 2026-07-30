<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
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
    public function boot(): void
    {
        $hour = Carbon::now()->hour;
        $greeting = 'Malam';

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Pagi';
        } elseif ($hour >= 12 && $hour < 15) {
            $greeting = 'Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Sore';
        }
        view()->composer('*', function ($view) use ($greeting) {
            $view->with('pageTitle', session('pageTitle', 'USH Super Apps'));
            $view->with('greeting', $greeting);


        $unreadCount = 0;
        try {
            if (auth()->check()) {
                $unreadCount = \App\Models\Notification::query()
                    ->where('recipient_type', 'user')
                    ->where('recipient_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
            } elseif (session()->has('student_id')) {
                $studentId = decrypt(session('student_id'));
                $unreadCount = \App\Models\Notification::query()
                    ->where('recipient_type', 'student')
                    ->where('recipient_id', $studentId)
                    ->whereNull('read_at')
                    ->count();
            }
        } catch (\Throwable) {
            $unreadCount = 0;
        }
        $view->with('globalUnreadCount', $unreadCount);

        });
    }
}
