<?php

namespace App\Repositories;

use App\Contracts\DnsQueryRepositoryInterface;
use App\Models\DnsQuery;
use Illuminate\Database\Eloquent\Builder;

class MySqlDnsQueryRepository implements DnsQueryRepositoryInterface
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
        'filter_id',
        'cached',
        'elapsed_ms',
        'upstream',
        'disallowed',
    ];

    
    public function getPaginated(
        array $filters = [],
        int $limit = 25,
        int $offset = 0,
        string $orderBy = 'event_time',
        string $orderDirection = 'desc'
    ): array {
        /*
        * Base query untuk menghitung total dalam rentang waktu aktif.
        * Hanya filter waktu yang diterapkan.
        */
        $totalQuery = DnsQuery::query();

        $this->applyDateFilters($totalQuery, $filters);

        $total = $totalQuery->count();

        /*
        * Query utama dengan seluruh filter.
        */
        $query = DnsQuery::query();

        $this->applyFilters($query, $filters);

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
            ->orderBy($orderBy, $orderDirection)
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

    private function applyDateFilters(
        Builder $query,
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
    }


    private function buildFilteredQuery(array $filters): Builder
    {
        $query = DnsQuery::query();

        // $this->applyFilters($query, $filters);

        return $query;
    }


    private function applyFilters(
        Builder $query,
        array $filters
    ): void 
    {
        $this->applyDateFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            preg_match_all(
                '/"([^"]+)"|(\S+)/',
                $search,
                $matches,
                PREG_SET_ORDER
            );

            $keywords = [];

            foreach ($matches as $match) {
                $keywords[] = !empty($match[1])
                    ? $match[1]
                    : $match[2];
            }

            $searchableColumns = [
                'client_ip',
                'client_name',
                'vlan_name',
                'domain',
                'query_type',
                'status',
                'reason',
                'upstream',
                'matched_rule',
            ];

            foreach ($keywords as $keyword) {
                $query->where(function ($query) use (
                    $keyword,
                    $searchableColumns
                ) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere(
                            $column,
                            'like',
                            '%' . $keyword . '%'
                        );
                    }
                });
            }
        }


        if (!empty($filters['vlan_name'])) {
            $query->where('vlan_name', $filters['vlan_name']);
        }

        if (!empty($filters['client_ip'])) {
            $query->where('client_ip', $filters['client_ip']);
        }

        if (!empty($filters['client_name'])) {
            $query->where(
                'client_name',
                'like',
                '%' . $filters['client_name'] . '%'
            );
        }

        if (!empty($filters['domain'])) {
            $query->where(
                'domain',
                'like',
                '%' . $filters['domain'] . '%'
            );
        }

        if (!empty($filters['query_type'])) {
            $query->where('query_type', $filters['query_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['reason'])) {
            $query->where('reason', $filters['reason']);
        }

        if (
            array_key_exists('disallowed', $filters)
            && $filters['disallowed'] !== null
            && $filters['disallowed'] !== ''
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
            array_key_exists('cached', $filters)
            && $filters['cached'] !== null
            && $filters['cached'] !== ''
        ) {
            $query->where(
                'cached',
                filter_var(
                    $filters['cached'],
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if (!empty($filters['upstream'])) {
            $query->where('upstream', $filters['upstream']);
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

        if (!empty($filters['matched_rule'])) {
            $query->where(
                'matched_rule',
                'like',
                '%' . $filters['matched_rule'] . '%'
            );
        }
    }

    private function getTimeBucketExpression(string $interval): string
    {
        return match ($interval) {
            'minute' => "DATE_FORMAT(event_time, '%Y-%m-%d %H:%i:00')",
            'hour'   => "DATE_FORMAT(event_time, '%Y-%m-%d %H:00:00')",
            'day'    => "DATE_FORMAT(event_time, '%Y-%m-%d 00:00:00')",
            'month'  => "DATE_FORMAT(event_time, '%Y-%m-01 00:00:00')",

            default => "DATE_FORMAT(event_time, '%Y-%m-%d %H:00:00')",
        };
    }


    public function getQueryTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {

        $bucket = $this->getTimeBucketExpression($interval);

        $query = $this->buildFilteredQuery($filters);

        return $query
            ->selectRaw("$bucket AS bucket")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(static function ($row): array {
                return [
                    'time' => $row->bucket,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }


    public function getAllowedBlockedTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {

        $bucket = $this->getTimeBucketExpression($interval);

        $query = $this->buildFilteredQuery($filters);

        return $query
            ->selectRaw("$bucket AS bucket")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN disallowed = 0 THEN 1
                        ELSE 0
                    END
                ) AS allowed
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN disallowed = 1 THEN 1
                        ELSE 0
                    END
                ) AS blocked
            ")
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


    public function getFilterOptions(): array
    {
        return [
            'vlans' => DnsQuery::query()
                ->where('vlan_name', '!=', '')
                ->distinct()
                ->orderBy('vlan_name')
                ->pluck('vlan_name')
                ->values()
                ->toArray(),

            'query_types' => DnsQuery::query()
                ->where('query_type', '!=', '')
                ->distinct()
                ->orderBy('query_type')
                ->pluck('query_type')
                ->values()
                ->toArray(),

            'statuses' => DnsQuery::query()
                ->where('status', '!=', '')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->values()
                ->toArray(),

            'reasons' => DnsQuery::query()
                ->where('reason', '!=', '')
                ->distinct()
                ->orderBy('reason')
                ->pluck('reason')
                ->values()
                ->toArray(),

            'upstreams' => DnsQuery::query()
                ->where('upstream', '!=', '')
                ->distinct()
                ->orderBy('upstream')
                ->pluck('upstream')
                ->values()
                ->toArray(),
        ];
    }


    public function findByQueryId(string $queryId): ?array
    {
        $query = DnsQuery::query()
            ->where('query_id', $queryId)
            ->first();

        return $query?->toArray();
    }


    public function getDashboardSummary(array $filters = []): array
    {
        $query = $this->buildFilteredQuery($filters);

        $summary = $query
            ->selectRaw('COUNT(*) as total_queries')
            ->selectRaw('SUM(CASE WHEN disallowed = 1 THEN 1 ELSE 0 END) as blocked_queries')
            ->selectRaw('SUM(CASE WHEN disallowed = 0 THEN 1 ELSE 0 END) as allowed_queries')
            ->selectRaw('SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cached_queries')
            ->selectRaw('AVG(elapsed_ms) as avg_response_time')
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


    public function getTopDomains(
        array $filters = [],
        int $limit = 10
    ): array {
       
        $query = $this->buildFilteredQuery($filters);

        return $query
            ->selectRaw('domain, COUNT(*) as total')
            ->where('domain', '!=', '')
            ->groupBy('domain')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(static function (DnsQuery $row): array {
                return [
                    'domain' => $row->domain,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }


    public function getTopClients(
        array $filters = [],
        int $limit = 10
    ): array {

        $query = $this->buildFilteredQuery($filters);

        return $query
            ->selectRaw('
                client_ip,
                client_name,
                vlan_name,
                COUNT(*) as total
            ')
            ->where('client_ip', '!=', '')
            ->groupBy(
                'client_ip',
                'client_name',
                'vlan_name'
            )
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(static function (DnsQuery $row): array {
                return [
                    'client_ip' => $row->client_ip,
                    'client_name' => $row->client_name,
                    'vlan_name' => $row->vlan_name,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }

    public function getTopVlans(
        array $filters = [],
        int $limit = 10
    ): array {
        
        $query = $this->buildFilteredQuery($filters);

        return $query
            ->selectRaw('vlan_name, COUNT(*) as total')
            ->where('vlan_name', '!=', '')
            ->groupBy('vlan_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(static function (DnsQuery $row): array {
                return [
                    'vlan_name' => $row->vlan_name,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }
    
    public function getTopBlockedDomains(
        array $filters = [],
        int $limit = 10
    ): array {
        
        $query = $this->buildFilteredQuery($filters);

        return $query
            ->where('disallowed', true)
            ->where('domain', '!=', '')
            ->selectRaw('domain, COUNT(*) as total')
            ->groupBy('domain')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(static function ($row): array {
                return [
                    'domain' => $row->domain,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }
}