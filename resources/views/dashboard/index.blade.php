@extends('layouts.app')

@section('title', 'Dashboard DNS Analytics')

@section('content')

<div
    id="dashboard-page"
    data-summary-url="{{ route('dashboard.summary') }}"
>

    <div class="container-fluid">

        <div>

            <h3 class="mb-1">
                Dashboard DNS Analytics
            </h3>

            <small class="text-muted">
                Monitoring aktivitas DNS dari AdGuard Home
            </small>

        </div>

        <div class="text-start">

            <small class="text-muted">
                Last Update
            </small>

            <div id="lastUpdated">
                -
            </div>

        </div>

    </div>

        {{-- Filter --}}
        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-3">

                        <label class="form-label">
                            Dari
                        </label>

                        <input
                            type="datetime-local"
                            id="dateFrom"
                            class="form-control"
                        >

                    </div>

                    <div class="col-lg-3">

                        <label class="form-label">
                            Sampai
                        </label>

                        <input
                            type="datetime-local"
                            id="dateTo"
                            class="form-control"
                        >

                    </div>

                    <div class="col-lg-2">

                        <button
                            id="btnApplyFilter"
                            class="btn btn-primary w-100"
                        >
                            Terapkan
                        </button>

                    </div>

                    <div class="col-lg-2">

                        <button
                            id="btnResetFilter"
                            class="btn btn-outline-secondary w-100"
                        >
                            Reset
                        </button>

                    </div>

                </div>

            </div>

        </div>

        {{-- KPI --}}
        <div class="row mb-4">

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Total Query</small>
                        <h3 id="totalQueries" class="mt-2 mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Blocked Query</small>
                        <h3 id="blockedQueries" class="mt-2 mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Allowed Query</small>
                        <h3 id="allowedQueries" class="mt-2 mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Cached Query</small>
                        <h3 id="cachedQueries" class="mt-2 mb-0">-</h3>
                    </div>
                </div>
            </div>

        </div>

        {{-- Timeline --}}
        <div class="row mb-4">

            <div class="col-12">

                <div class="card shadow-sm">

                    <div class="card-header">
                        <strong>Query Timeline</strong>
                    </div>

                    <div class="card-body p-3">

                        <div style="height:350px">

                            <canvas
                                id="queryTimelineChart"
                            ></canvas>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Chart allowed --}}
        <div class="row mb-4">

            <div class="col-12">

                <div class="card shadow-sm">

                    <div class="card-header">
                       <strong>Allowed vs Blocked Timeline</strong>
                    </div>

                    <div class="card-body p-3">

                        <div style="height:350px">

                            <canvas
                                id="allowedBlockedTimelineChart"
                            ></canvas>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Ranking --}}
        <div class="row mb-4">

            <div class="col-lg-6 mb-3">

                <div class="card shadow-sm h-100">

                    <div class="card-header">
                        Top Domain
                    </div>

                    <div class="card-body">

                        <div id="topDomainsLoading">
                            Loading...
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-6 mb-3">

                <div class="card shadow-sm h-100">

                    <div class="card-header">
                        Top Client
                    </div>

                    <div class="card-body">

                        <div id="topClientsLoading">
                            Loading...
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="row">

            <div class="col-lg-6 mb-3">

                <div class="card shadow-sm h-100">

                    <div class="card-header">
                        Top VLAN
                    </div>

                    <div class="card-body">

                        <div id="topVlansLoading">
                            Loading...
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-6 mb-3">

                <div class="card shadow-sm h-100">

                    <div class="card-header">
                        Top Blocked Domain
                    </div>

                    <div class="card-body">

                        <div id="topBlockedDomainsLoading">
                            Loading...
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection