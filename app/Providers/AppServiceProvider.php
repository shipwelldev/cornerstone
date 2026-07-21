<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Date::useClass(dateClass: CarbonImmutable::class);

        Model::shouldBeStrict();

        Password::defaults(
            static fn (): Password => app()->isProduction()
                ? Password::min(12)->uncompromised()
                : Password::min(8),
        );

        DB::prohibitDestructiveCommands(prohibit: app()->isProduction());

        if (app()->isProduction()) {
            URL::forceScheme(scheme: 'https');
        }
    }
}
