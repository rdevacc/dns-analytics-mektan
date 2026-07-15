<?php

namespace Database\Seeders;

use App\Models\DnsQuery;
use Illuminate\Database\Seeder;

class DnsQuerySeeder extends Seeder
{
    public function run(): void
    {
        DnsQuery::factory()
            ->count(10000)
            ->create();
    }
}