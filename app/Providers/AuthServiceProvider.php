<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Policies\CountryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ShopPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Country::class => CountryPolicy::class,
        Shop::class => ShopPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
