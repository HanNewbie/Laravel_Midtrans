<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        // Hapus fungsi dibawah jika ingin menjalankan di localhost

        //Konfigurasi Public via ngrok
        // if(config('app.env') === 'local'){
        //     URL::forceScheme('https');
       
        // }
    }
}
