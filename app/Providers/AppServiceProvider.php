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
        $this->configureUrlForSubpathDeployment();
    }

    /**
     * Force all generated URLs (route(), redirect('/...'), url()) to use APP_URL's
     * scheme and path prefix. Behind the Apache reverse proxy, Laravel's own root()
     * detection only sees host:port, not the /laporanujian prefix or that the
     * original request was https — so login redirects etc. would otherwise drop both.
     * No-op when APP_URL has no path (dev lokal).
     */
    protected function configureUrlForSubpathDeployment(): void
    {
        $subPath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($subPath === '') {
            return;
        }

        if (parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https') {
            URL::forceScheme('https');
        }

        URL::forceRootUrl(config('app.url'));
    }
}
