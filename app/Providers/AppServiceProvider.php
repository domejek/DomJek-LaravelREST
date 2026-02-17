<?php

namespace App\Providers;

use App\Events\TaskUpdated;
use App\Listeners\CheckTaskDeadline;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            TaskUpdated::class,
            CheckTaskDeadline::class
        );
    }
}
