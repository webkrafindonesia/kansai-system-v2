<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Gate;
use App\Policies\ActivityPolicy;
use Spatie\Activitylog\Models\Activity;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

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
        FilamentAsset::register([
            Js::make('custom-script', __DIR__ . '/../../resources/js/helpers.js'),
        ]);

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
            'victory' => Color::generateV3Palette('#8400ffff'),
        ]);

        Gate::policy(Activity::class, ActivityPolicy::class);

        if(env('FORCE_HTTPS', false)){
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
