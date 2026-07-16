<?php

namespace App\Services\ClickHouse;

use ClickHouseDB\Client;

class ClickHouseService
{
    public function __construct(
        private Client $client
    ) {
    }

    public function select(
        string $sql,
        array $bindings = []
    ): array {
        return $this->client
            ->select($sql, $bindings)
            ->rows();
    }
}