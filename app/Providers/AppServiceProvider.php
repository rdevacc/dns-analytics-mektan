<?php

namespace App\Providers;

use App\Contracts\DnsQueryRepositoryInterface;
use App\Repositories\MySqlDnsQueryRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DnsQueryRepositoryInterface::class,
            MySqlDnsQueryRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}