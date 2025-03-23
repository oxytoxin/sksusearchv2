<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Model;
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
        Model::unguard();
        DatePicker::configureUsing(function (DatePicker $datePicker) {
            return $datePicker->native(false);
        });
    }
}
