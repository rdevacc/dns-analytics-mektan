<?php

namespace App\Contracts;

interface DnsQueryRepositoryInterface
{
    public function getPaginated(
        array $filters = [],
        int $limit = 25,
        int $offset = 0,
        string $orderBy = 'event_time',
        string $orderDirection = 'desc'
    ): array;

    public function getFilterOptions(): array;

    public function findByQueryId(string $queryId): ?array;

    public function getDashboardSummary(array $filters = []): array;

    public function getTopDomains(
        array $filters = [],
        int $limit = 10
    ): array;

    public function getTopClients(
        array $filters = [],
        int $limit = 10
    ): array;

    public function getTopVlans(
        array $filters = [],
        int $limit = 10
    ): array;

    public function getTopBlockedDomains(
        array $filters = [],
        int $limit = 10
    ): array;

    public function getQueryTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array;

    public function getAllowedBlockedTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array;

}