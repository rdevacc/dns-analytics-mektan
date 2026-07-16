<?php

namespace App\Providers;

use App\Contracts\Repositories\DnsQueryRepositoryInterface;
use App\Repositories\Mysql\MySqlDnsQueryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
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