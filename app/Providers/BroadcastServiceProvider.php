<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
{
        // CRITICAL: Ensure 'auth' is NOT in this array. Only use 'web'.
        Broadcast::routes(['middleware' => ['web']]);

        require base_path('routes/channels.php');
    }
}
