<?php

namespace App\Repositories\ClickHouse;

use App\Contracts\Repositories\DnsQueryRepositoryInterface;
use BadMethodCallException;
use Illuminate\Support\Facades\DB;

class ClickHouseDnsQueryRepository implements DnsQueryRepositoryInterface
{

    protected function query()
    {
        return DB::connection('clickhouse')
            ->table('dns_queries');
    }

    public function getPaginated(
        array $filters = [],
        int $limit = 25,
        int $offset = 0,
        string $orderBy = 'event_time',
        string $orderDirection = 'desc'
    ): array {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getFilterOptions(): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function findByQueryId(string $queryId): ?array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getDashboardSummary(array $filters = []): array
    {
        $query = $this->query();

        if (!empty($filters['date_from'])) {
            $query->where(
                'event_time',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->where(
                'event_time',
                '<=',
                $filters['date_to']
            );
        }

        $summary = $query
            ->selectRaw('COUNT(*) AS total_queries')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN disallowed = 1 THEN 1
                        ELSE 0
                    END
                ) AS blocked_queries
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN disallowed = 0 THEN 1
                        ELSE 0
                    END
                ) AS allowed_queries
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN cached = 1 THEN 1
                        ELSE 0
                    END
                ) AS cached_queries
            ")
            ->selectRaw('AVG(elapsed_ms) AS avg_response_time')
            ->first();

        $totalQueries = (int) ($summary->total_queries ?? 0);
        $blockedQueries = (int) ($summary->blocked_queries ?? 0);

        return [
            'total_queries' => $totalQueries,
            'blocked_queries' => $blockedQueries,
            'allowed_queries' => (int) ($summary->allowed_queries ?? 0),
            'cached_queries' => (int) ($summary->cached_queries ?? 0),
            'blocked_percentage' => $totalQueries > 0
                ? round(($blockedQueries / $totalQueries) * 100, 2)
                : 0.0,
            'avg_response_time' => round(
                (float) ($summary->avg_response_time ?? 0),
                2
            ),
        ];
    }

    public function getTopDomains(array $filters = [], int $limit = 10): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getTopClients(array $filters = [], int $limit = 10): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getTopVlans(array $filters = [], int $limit = 10): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getTopBlockedDomains(array $filters = [], int $limit = 10): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getQueryTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getAllowedBlockedTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {
        throw new BadMethodCallException('Not implemented.');
    }
}