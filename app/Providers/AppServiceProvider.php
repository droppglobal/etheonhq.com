<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
            Schema::defaultStringLength(191);

        // push notifications for repair, visitor, lead and application events
        foreach ([\App\Models\MaintenanceRequest::class, \App\Models\VisitorPass::class, \App\Models\Lead::class, \App\Models\RentalApplication::class,
                  \App\Models\NoticeBoard::class, \App\Models\Poll::class, \App\Models\Agreement::class, \App\Models\FacilityBooking::class, \App\Models\Inspection::class] as $model) {
            $model::observe(\App\Observers\PushObserver::class);
        }
    }
}
