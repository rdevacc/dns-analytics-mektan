<?php

namespace App\Http\Controllers;

use App\Services\DnsQueryService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DnsQueryDataRequest;
use Illuminate\Http\Request;

class DnsQueryController extends Controller
{
    public function __construct(
        private readonly DnsQueryService $dnsQueryService
    ) {
    }

    public function index()
    {
        return view('dns-queries.index');
    }

    public function data(DnsQueryDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $draw = (int) ($validated['draw'] ?? 1);
        $offset = (int) ($validated['start'] ?? 0);
        $limit = (int) ($validated['length'] ?? 25);

        $columns = [
            0 => 'event_time',
            1 => 'client_ip',
            2 => 'client_name',
            3 => 'vlan_name',
            4 => 'domain',
            5 => 'query_type',
            6 => 'status',
            7 => 'reason',
            8 => 'cached',
            9 => 'elapsed_ms',
            10 => 'upstream',
            11 => 'disallowed',
        ];

        $orderColumnIndex = (int) data_get(
            $validated,
            'order.0.column',
            0
        );

        $orderBy = $columns[$orderColumnIndex] ?? 'event_time';

        $orderDirection = data_get(
            $validated,
            'order.0.dir',
            'desc'
        );

        $filters = [
            'search' => data_get($validated, 'search.value'),
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'vlan_name' => $validated['vlan_name'] ?? null,
            'client_ip' => $validated['client_ip'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'domain' => $validated['domain'] ?? null,
            'query_type' => $validated['query_type'] ?? null,
            'status' => $validated['status'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'disallowed' => $validated['disallowed'] ?? null,
            'cached' => $validated['cached'] ?? null,
            'upstream' => $validated['upstream'] ?? null,
            'filter_id' => $validated['filter_id'] ?? null,
            'matched_rule' => $validated['matched_rule'] ?? null,
        ];

        $result = $this->dnsQueryService->getPaginated(
           $filters,
           $limit,
           $offset,
           $orderBy,
           $orderDirection
        );


        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered_total'],
            'data' => $result['data'],
        ]);
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json(
            $this->dnsQueryService->getFilterOptions()
        );
    }

    public function show(string $queryId): JsonResponse
    {
        $query = $this->dnsQueryService->findByQueryId($queryId);

        if ($query === null) {
            return response()->json([
                'message' => 'DNS query tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => $query,
        ]);
    }
}