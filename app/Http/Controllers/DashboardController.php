<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardDataRequest;
use App\Services\DnsQueryService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DnsQueryService $dnsQueryService
    ) {
    }

    public function index()
    {
        return view('dashboard.index');
    }

    public function summary(DashboardDataRequest $request): JsonResponse
    {
        $filters = $request->validated();

        if (empty($filters['date_from'])) {
            $filters['date_from'] = now('Asia/Jakarta')
                ->startOfYear()
                ->startOfDay()
                ->format('Y-m-d H:i:s');
        }

        if (empty($filters['date_to'])) {
            $filters['date_to'] = now('Asia/Jakarta')
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        return response()->json([
            'data' => $this->dnsQueryService->getDashboardData($filters),
        ]);
    }

}