@extends('layouts.app')

@section('title', 'Query Log - DNS Analytics')

@section('content')
    <div
        id="dns-query-page"
        data-url="{{ route('dns-queries.data') }}"
        data-filter-options-url="{{ route('dns-queries.filter-options') }}"
        data-detail-url-template="{{ route('dns-queries.show', ['queryId' => '__QUERY_ID__']) }}"
    >

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-1">DNS Query Log</h5>
                        <p class="text-muted small mb-0">
                            Query log ditampilkan menggunakan server-side processing.
                        </p>
                    </div>
                </div>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label for="date_from" class="form-label">
                                    Dari Waktu
                                </label>
                                <input
                                    type="datetime-local"
                                    id="date_from"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="date_to" class="form-label">
                                    Sampai Waktu
                                </label>
                                <input
                                    type="datetime-local"
                                    id="date_to"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="vlan_name" class="form-label">VLAN</label>

                                <select id="vlan_name" class="form-select">
                                    <option value="">Semua VLAN</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="client_ip" class="form-label">
                                    Client IP
                                </label>
                                <input
                                    type="text"
                                    id="client_ip"
                                    class="form-control"
                                    placeholder="Contoh: 172.16.0.3"
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="client_name" class="form-label">
                                    Client Name
                                </label>
                                <input
                                    type="text"
                                    id="client_name"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="domain" class="form-label">
                                    Domain
                                </label>
                                <input
                                    type="text"
                                    id="domain"
                                    class="form-control"
                                    placeholder="Contoh: google.com"
                                >
                            </div>

                            <div class="col-md-2">
                                <label for="query_type" class="form-label">Query Type</label>

                                <select id="query_type" class="form-select">
                                    <option value="">Semua Query Type</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>

                                <select id="status" class="form-select">
                                    <option value="">Semua Status</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="reason" class="form-label">Reason</label>

                                <select id="reason" class="form-select">
                                    <option value="">Semua Reason</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="disallowed" class="form-label">
                                    Allowed / Blocked
                                </label>
                                <select id="disallowed" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="0">Allowed</option>
                                    <option value="1">Blocked</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="cached" class="form-label">
                                    Cache
                                </label>
                                <select id="cached" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="1">Cached</option>
                                    <option value="0">Non-cached</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="upstream" class="form-label">Upstream</label>

                                <select id="upstream" class="form-select">
                                    <option value="">Semua Upstream</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="filter_id" class="form-label">
                                    Filter ID
                                </label>
                                <input
                                    type="number"
                                    id="filter_id"
                                    class="form-control"
                                    min="0"
                                >
                            </div>

                            <div class="col-md-5">
                                <label for="matched_rule" class="form-label">
                                    Matched Rule
                                </label>
                                <input
                                    type="text"
                                    id="matched_rule"
                                    class="form-control"
                                >
                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button
                                type="button"
                                id="btn-filter"
                                class="btn btn-primary"
                            >
                                Terapkan Filter
                            </button>

                            <button
                                type="button"
                                id="btn-reset"
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>


                <div class="table-responsive">
                    <table
                        id="dns-query-table"
                        class="table table-striped table-hover align-middle w-100"
                    >
                        <thead>
                            <tr>
                                <th>Waktu WIB</th>
                                <th>Client IP</th>
                                <th>Client Name</th>
                                <th>VLAN</th>
                                <th>Domain</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Cached</th>
                                <th>Response Time</th>
                                <th>Upstream</th>
                                <th>Blocked</th>
                                <th>Detail</th> 
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div
            class="modal fade"
            id="queryDetailModal"
            tabindex="-1"
            aria-labelledby="queryDetailModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="queryDetailModalLabel"
                        >
                            Detail DNS Query
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <th style="width: 180px;">Waktu WIB</th>
                                        <td id="detail-event-time"></td>
                                    </tr>

                                    <tr>
                                        <th>Client</th>
                                        <td id="detail-client"></td>
                                    </tr>

                                    <tr>
                                        <th>VLAN</th>
                                        <td id="detail-vlan"></td>
                                    </tr>

                                    <tr>
                                        <th>Domain</th>
                                        <td id="detail-domain"></td>
                                    </tr>

                                    <tr>
                                        <th>Query</th>
                                        <td id="detail-query"></td>
                                    </tr>

                                    <tr>
                                        <th>Status</th>
                                        <td id="detail-status"></td>
                                    </tr>

                                    <tr>
                                        <th>Reason</th>
                                        <td id="detail-reason"></td>
                                    </tr>

                                    <tr>
                                        <th>Filter ID</th>
                                        <td id="detail-filter-id"></td>
                                    </tr>

                                    <tr>
                                        <th>Matched Rule</th>
                                        <td id="detail-matched-rule"></td>
                                    </tr>

                                    <tr>
                                        <th>Upstream</th>
                                        <td id="detail-upstream"></td>
                                    </tr>

                                    <tr>
                                        <th>Response Time</th>
                                        <td id="detail-elapsed"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6>Answers JSON</h6>
                        <pre
                            id="detail-answers-json"
                            class="bg-light border rounded p-3 mb-4"
                        ></pre>

                        <h6>Rules JSON</h6>
                        <pre
                            id="detail-rules-json"
                            class="bg-light border rounded p-3 mb-4"
                        ></pre>

                        <h6>Client WHOIS JSON</h6>
                        <pre
                            id="detail-whois-json"
                            class="bg-light border rounded p-3 mb-4"
                        ></pre>

                        <h6>Raw JSON</h6>
                        <pre
                            id="detail-raw-json"
                            class="bg-light border rounded p-3 mb-0"
                        ></pre>

                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection