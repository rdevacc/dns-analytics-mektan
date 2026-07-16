<?php

namespace App\Providers;

use App\Contracts\Repositories\DnsQueryRepositoryInterface;
use App\Repositories\ClickHouse\ClickHouseDnsQueryRepository;
use App\Repositories\Mysql\MySqlDnsQueryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DnsQueryRepositoryInterface::class,
            function ($app) {

                return match (
                    config('dns-analytics.repository')
                ) {

                    'clickhouse' => $app->make(
                        ClickHouseDnsQueryRepository::class
                    ),

                    'mysql' => $app->make(
                        MySqlDnsQueryRepository::class
                    ),

                    default => throw new \InvalidArgumentException(
                        'Unsupported DNS repository driver.'
                    ),

                };

            }
        );
        
    }

    public function boot(): void
    {
        //
    }
}