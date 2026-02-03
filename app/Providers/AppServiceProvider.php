<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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

        // Rate limiting (Redis-backed via cache)
        $this->configureRateLimiting();

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {

                // Keep bearer auth
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }

    protected function configureRateLimiting(): void
    {
        // LOGIN — brute force + credential stuffing protection
        RateLimiter::for('auth.login', function (Request $request) {
            return Limit::perHour(25)->by(
                'login|' . strtolower($request->input('email')) . '|' . $request->ip()
            );
        });

        // REGISTER — prevent bot signups
        RateLimiter::for('auth.register', function (Request $request) {
            return Limit::perHour(25)->by(
                'register|' . $request->ip()
            );
        });

        // AUTHENTICATED — prevent token abuse
        RateLimiter::for('auth.general', function (Request $request) {
            return Limit::perHour(120)->by(
                'auth|' . (optional($request->user())->id ?: $request->ip())
            );
        });
    }

}
