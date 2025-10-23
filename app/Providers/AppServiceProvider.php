<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ExamSubmitted;
use App\Listeners\HandleExamSubmission;

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
        // Register event listeners
        Event::listen(
            ExamSubmitted::class,
            HandleExamSubmission::class
        );

        \Carbon\Carbon::setLocale('id');
    }
}
