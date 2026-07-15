<?php

namespace App\Services;

use App\Contracts\DnsQueryRepositoryInterface;
use Carbon\Carbon;

class DnsQueryService
{
    public function __construct(
        private readonly DnsQueryRepositoryInterface $repository
    ) {
    }

    public function getPaginated(
        array $filters = [],
        int $limit = 25,
        int $offset = 0,
        string $orderBy = 'event_time',
        string $orderDirection = 'desc'
    ): array {
        $filters = $this->normalizeFilters($filters);

        $result = $this->repository->getPaginated(
            filters: $filters,
            limit: $limit,
            offset: $offset,
            orderBy: $orderBy,
            orderDirection: $orderDirection
        );

        $result['data'] = array_map(
            fn (array $row): array => $this->transformRow($row),
            $result['data']
        );

        return $result;
    }

    private function normalizeFilters(array $filters): array
    {
        if (!empty($filters['date_from'])) {
            $filters['date_from'] = Carbon::parse(
                $filters['date_from'],
                'Asia/Jakarta'
            )
                ->format('Y-m-d H:i:s');
        }

        if (!empty($filters['date_to'])) {
            $filters['date_to'] = Carbon::parse(
                $filters['date_to'],
                'Asia/Jakarta'
            )
                ->format('Y-m-d H:i:s');
        }

        return $filters;
    }

    private function transformRow(array $row): array
    {
        if (!empty($row['event_time'])) {
            $row['event_time_wib'] = Carbon::parse(
                $row['event_time'],
                'UTC'
            )
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s.u');
        } else {
            $row['event_time_wib'] = null;
        }

        return $row;
    }

    public function getFilterOptions(): array
    {
        return $this->repository->getFilterOptions();
    }

    public function findByQueryId(string $queryId): ?array
    {
        $query = $this->repository->findByQueryId($queryId);

        if ($query === null) {
            return null;
        }

        return $this->transformRow($query);
    }

    public function getDashboardSummary(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getDashboardSummary($filters);
    }

    public function getTopDomains(
        array $filters = [],
        int $limit = 10
    ): array {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getTopDomains(
            filters: $filters,
            limit: $limit
        );
    }


    public function getTopClients(
        array $filters = [],
        int $limit = 10
    ): array {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getTopClients(
            filters: $filters,
            limit: $limit
        );
    }

    public function getTopVlans(
        array $filters = [],
        int $limit = 10
    ): array {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getTopVlans(
            filters: $filters,
            limit: $limit
        );
    }

    public function getTopBlockedDomains(
        array $filters = [],
        int $limit = 10
    ): array {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getTopBlockedDomains(
            filters: $filters,
            limit: $limit
        );
    }

    public function getDashboardData(array $filters = []): array
    {
        return [
            'summary' => $this->getDashboardSummary($filters),

            'rankings' => [
                'top_domains' => $this->getTopDomains($filters),
                'top_clients' => $this->getTopClients($filters),
                'top_vlans' => $this->getTopVlans($filters),
                'top_blocked_domains' => $this->getTopBlockedDomains($filters),
            ],

            'charts' => [

                'query_timeline' => $this->getQueryTimeline(
                    $filters,
                    $this->determineTimelineInterval($filters)
                ),

                'allowed_blocked_timeline' => $this->getAllowedBlockedTimeline(
                    $filters,
                    $this->determineTimelineInterval($filters)
                ),

            ],
        ];
    }


    public function getQueryTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {
        $filters = $this->normalizeFilters($filters);

        return $this->repository->getQueryTimeline(
            filters: $filters,
            interval: $interval
        );
    }


    private function determineTimelineInterval(array $filters): string
    {
        if (
            empty($filters['date_from']) ||
            empty($filters['date_to'])
        ) {
            return 'day';
        }

        $from = Carbon::parse($filters['date_from']);
        $to = Carbon::parse($filters['date_to']);

        $days = $from->diffInDays($to);

        return match (true) {
            $days <= 2 => 'hour',
            $days <= 90 => 'day',
            default => 'month',
        };
    }

    public function getAllowedBlockedTimeline(
        array $filters = [],
        string $interval = 'hour'
    ): array {

        $filters = $this->normalizeFilters($filters);

        return $this->repository->getAllowedBlockedTimeline(
            filters: $filters,
            interval: $interval
        );

    }

}