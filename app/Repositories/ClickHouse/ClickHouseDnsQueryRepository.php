<?php

namespace App\Repositories\ClickHouse;

use App\Contracts\Repositories\DnsQueryRepositoryInterface;
use BadMethodCallException;
use PhpClickHouseLaravel\Builder;
use Illuminate\Support\Facades\DB;
use Tinderbox\ClickhouseBuilder\Query\Expression;

class ClickHouseDnsQueryRepository implements DnsQueryRepositoryInterface
{

    private const ALLOWED_SORT_COLUMNS = [
        'event_time',
        'client_ip',
        'client_name',
        'vlan_name',
        'domain',
        'query_type',
        'status',
        'reason',
        'cached',
        'elapsed_ms',
        'upstream',
        'disallowed',
    ];

    protected function query()
    {
        return DB::connection('clickhouse')
            ->table('dns_queries');
    }

    private function getTimeBucketExpression(string $interval): string
    {
        return match ($interval) {
            'hour' => 'toStartOfHour(event_time)',
            'day' => 'toStartOfDay(event_time)',
            'month' => 'toStartOfMonth(event_time)',
            default => 'toStartOfDay(event_time)',
        };
    }

    private function buildFilteredQuery(array $filters)
    {
        $query = $this->query();

        $this->applyFilters(
            $query,
            $filters
        );

        return $query;
    }

    private function applyFilters(
        $query,
        array $filters
    ): void {

        $this->applyDateFilters(
            $query,
            $filters
        );

        if (!empty($filters['vlan_name'])) {
            $query->where(
                'vlan_name',
                $filters['vlan_name']
            );
        }

        if (!empty($filters['client_ip'])) {
            $query->where(
                'client_ip',
                $filters['client_ip']
            );
        }

        if (!empty($filters['query_type'])) {
            $query->where(
                'query_type',
                $filters['query_type']
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (!empty($filters['reason'])) {
            $query->where(
                'reason',
                $filters['reason']
            );
        }

        if (!empty($filters['upstream'])) {
            $query->where(
                'upstream',
                $filters['upstream']
            );
        }

        if (
            array_key_exists('cached', $filters)
            && $filters['cached'] !== ''
            && $filters['cached'] !== null
        ) {
            $query->where(
                'cached',
                filter_var(
                    $filters['cached'],
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if (
            array_key_exists('disallowed', $filters)
            && $filters['disallowed'] !== ''
            && $filters['disallowed'] !== null
        ) {
            $query->where(
                'disallowed',
                filter_var(
                    $filters['disallowed'],
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if (
            isset($filters['filter_id'])
            && $filters['filter_id'] !== ''
        ) {
            $query->where(
                'filter_id',
                (int) $filters['filter_id']
            );
        }

        $this->applySearchFilter(
            $query,
            $filters['search'] ?? null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    private function applyDateFilters(
        $query,
        array $filters
    ): void {

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

        $this->applyContainsFilter(
            $query,
            'domain',
            $filters['domain'] ?? null
        );

        $this->applyContainsFilter(
            $query,
            'client_name',
            $filters['client_name'] ?? null
        );

        $this->applyContainsFilter(
            $query,
            'matched_rule',
            $filters['matched_rule'] ?? null
        );

    }

    private function applyContainsFilter(
        Builder $query,
        string $column,
        ?string $value
    ): void {

        if (blank($value)) {
            return;
        }

        $escaped = str_replace(
            "'",
            "\\'",
            trim($value)
        );

        $query->whereRaw(
            "positionCaseInsensitive($column, '{$escaped}') > 0"
        );
    }

    private function applySearchFilter(
        Builder $query,
        ?string $search
    ): void {

        if (blank($search)) {
            return;
        }

        $keywords = preg_split(
            '/\s+/',
            trim($search),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($keywords as $keyword) {

            $escaped = str_replace(
                "'",
                "\\'",
                $keyword
            );

            $query->where(function (Builder $query) use ($escaped) {

                $query->whereRaw(
                    "positionCaseInsensitive(domain, '{$escaped}') > 0"
                );

                $query->orWhereRaw(
                    "positionCaseInsensitive(client_name, '{$escaped}') > 0"
                );

                $query->orWhereRaw(
                    "positionCaseInsensitive(client_ip, '{$escaped}') > 0"
                );

                $query->orWhereRaw(
                    "positionCaseInsensitive(matched_rule, '{$escaped}') > 0"
                );

            });

        }

    }

    public function getPaginated(
        array $filters = [],
        int $limit = 25,
        int $offset = 0,
        string $orderBy = 'event_time',
        string $orderDirection = 'desc'
    ): array {

        /*
        * Total data hanya berdasarkan filter tanggal.
        */
        $totalQuery = $this->query();

        $this->applyDateFilters(
            $totalQuery,
            $filters
        );

        $total = $totalQuery->count();

        /*
        * Query utama.
        */
        $query = $this->buildFilteredQuery($filters);

        $filteredTotal = (clone $query)->count();

        $orderBy = in_array(
            $orderBy,
            self::ALLOWED_SORT_COLUMNS,
            true
        )
            ? $orderBy
            : 'event_time';

        $orderDirection = strtolower($orderDirection) === 'asc'
            ? 'asc'
            : 'desc';

        $data = $query
            ->select([
                'query_id',
                'event_time',
                'client_ip',
                'client_name',
                'vlan_name',
                'domain',
                'query_type',
                'status',
                'reason',
                'cached',
                'elapsed_ms',
                'upstream',
                'disallowed',
            ])
            ->orderBy(
                $orderBy,
                $orderDirection
            )
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered_total' => $filteredTotal,
        ];
    }

    public function getFilterOptions(): array
    {
        return [
            'vlans' => $this->getDistinctValues('vlan_name'),

            'query_types' => $this->getDistinctValues('query_type'),

            'statuses' => $this->getDistinctValues('status'),

            'reasons' => $this->getDistinctValues('reason'),

            'upstreams' => $this->getDistinctValues('upstream'),
        ];
    }

    public function findByQueryId(string $queryId): ?array
    {
        $row = $this->query()
            ->where('query_id', $queryId)
            ->first();

        return $row
            ? (array) $row
            : null;
    }

    public function getDashboardSummary(array $filters = []): array
    {
        $query = $this->buildFilteredQuery($filters);

        $rows = $query
                ->select([
                    new Expression('COUNT() AS total_queries'),
                    new Expression('countIf(disallowed = 1) AS blocked_queries'),
                    new Expression('countIf(disallowed = 0) AS allowed_queries'),
                    new Expression('countIf(cached = 1) AS cached_queries'),
                    new Expression('AVG(elapsed_ms) AS avg_response_time'),
                ])
                ->get()
                ->all();

        $summary = $rows[0] ?? new \stdClass();

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

    public function getTopDomains(
        array $filters = [],
        int $limit = 10
    ): array {

        $query = $this->buildFilteredQuery($filters);

        $rows = $query
            ->select([
                'domain',
                new Expression('COUNT() AS total'),
            ])
            ->groupBy('domain')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->all();

        return array_map(
            static function ($row): array {
                return [
                    'domain' => $row->domain,
                    'total' => (int) $row->total,
                ];
            },
            $rows
        );
    }

    public function getTopClients(
        array $filters = [],
        int $limit = 10
    ): array {

        $query = $this->buildFilteredQuery($filters);

        $rows = $query
        ->select([
            'client_name',
            new Expression('COUNT() AS total'),
        ])
        ->groupBy('client_name')
        ->orderByDesc('total')
        ->limit($limit)
        ->get()
        ->all();

    return array_map(
        static function ($row): array {
            return [
                'client_name' => $row->client_name,
                'total' => (int) $row->total,
            ];
        },
        $rows
    );
    }

    public function getTopVlans(
        array $filters = [],
        int $limit = 10
    ): array {

        $query = $this->buildFilteredQuery($filters);

        $rows = $query
        ->select([
            'vlan_name',
            new Expression('COUNT() AS total'),
        ])
        ->groupBy('vlan_name')
        ->orderByDesc('total')
        ->limit($limit)
        ->get()
        ->all();

    return array_map(
        static function ($row): array {
            return [
                'vlan_name' => $row->vlan_name,
                'total' => (int) $row->total,
            ];
        },
        $rows
    );
    }

    public function getTopBlockedDomains(
        array $filters = [],
        int $limit = 10
    ): array {

        $query = $this->buildFilteredQuery($filters);

        $rows = $query
        ->where('disallowed', 1)
        ->select([
            'domain',
            new Expression('COUNT() AS total'),
        ])
        ->groupBy('domain')
        ->orderByDesc('total')
        ->limit($limit)
        ->get()
        ->all();

    return array_map(
        static function ($row): array {
            return [
                'domain' => $row->domain,
                'total' => (int) $row->total,
            ];
        },
        $rows
    );;
    }

    public function getQueryTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {

        $bucket = $this->getTimeBucketExpression($interval);

        $query = $this->buildFilteredQuery($filters);

        $rows = $query
            ->select([
                new Expression("$bucket AS bucket"),
                new Expression('COUNT() AS total'),
            ])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->all();

        return array_map(
            static function ($row): array {
                return [
                    'time' => $row->bucket,
                    'total' => (int) $row->total,
                ];
            },
            $rows
        );
    }

    public function getAllowedBlockedTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {

        $bucket = $this->getTimeBucketExpression($interval);

        $query = $this->buildFilteredQuery($filters);

        return $query
        ->select(
            "$bucket AS bucket",
            "countIf(disallowed = 0) AS allowed",
            "countIf(disallowed = 1) AS blocked"
        )
        ->groupBy('bucket')
        ->orderBy('bucket')
        ->get()
        ->map(static function ($row): array {

            return [
                'time' => $row->bucket,
                'allowed' => (int) $row->allowed,
                'blocked' => (int) $row->blocked,
            ];

        })
        ->toArray();
    } 

    private function getDistinctValues(string $column): array
    {
        return $this->query()
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values()
            ->toArray();
    }
}