<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Separate counters per feature: plain "throttle:N,1" shares one counter per user/IP across all routes
        RateLimiter::for('assistant-message', function (Request $request) {
            return Limit::perMinute(20)->by('assistant-message:' . ($request->user()?->id ?: $request->ip()));
        });
        RateLimiter::for('assistant-request', function (Request $request) {
            return Limit::perMinute(5)->by('assistant-request:' . ($request->user()?->id ?: $request->ip()));
        });
        RateLimiter::for('ai-writer', function (Request $request) {
            return Limit::perMinute(10)->by('ai-writer:' . ($request->user()?->id ?: $request->ip()));
        });
        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(5)->by('public-forms:' . optional($request->route())->getName() . ':' . $request->ip());
        });
        // public invoice pay page: generous enough for reloads, tight enough to stop token guessing
        RateLimiter::for('pay-link', function (Request $request) {
            return Limit::perMinute(30)->by('pay-link:' . $request->ip());
        });
        // mobile sign-in: per email and per device address, to stop password guessing
        RateLimiter::for('mobile-login', function (Request $request) {
            return [
                Limit::perMinute(5)->by('mobile-login:' . mb_strtolower((string) $request->input('email')) . '|' . $request->ip()),
                Limit::perMinute(20)->by('mobile-login-ip:' . $request->ip()),
            ];
        });
        RateLimiter::for('whatsapp-webhook', function (Request $request) {
            return Limit::perMinute(120)->by('whatsapp-webhook:' . $request->ip());
        });
    }
}
