document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('dns-query-page');

    if (!page) {
        return;
    }

    const dataUrl = page.dataset.url;
    const filterOptionsUrl = page.dataset.filterOptionsUrl;
    const detailUrlTemplate = page.dataset.detailUrlTemplate;

    function populateSelect(selectId, items) {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        items.forEach(function (item) {
            const option = document.createElement('option');

            option.value = item;
            option.textContent = item;

            select.appendChild(option);
        });
    }

    fetch(filterOptionsUrl)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Gagal mengambil filter options.');
            }

            return response.json();
        })
        .then(function (data) {
            populateSelect('vlan_name', data.vlans);
            populateSelect('query_type', data.query_types);
            populateSelect('status', data.statuses);
            populateSelect('reason', data.reasons);
            populateSelect('upstream', data.upstreams);
        })
        .catch(function (error) {
            console.error(error);
        });

    const table = new DataTable('#dns-query-table', {
        processing: true,
        serverSide: true,
        responsive: true,

        ajax: {
            url: dataUrl,
            type: 'GET',

            data: function (d) {
                d.date_from = document.getElementById('date_from').value;
                d.date_to = document.getElementById('date_to').value;
                d.vlan_name = document.getElementById('vlan_name').value;
                d.client_ip = document.getElementById('client_ip').value;
                d.client_name = document.getElementById('client_name').value;
                d.domain = document.getElementById('domain').value;
                d.query_type = document.getElementById('query_type').value;
                d.status = document.getElementById('status').value;
                d.reason = document.getElementById('reason').value;
                d.disallowed = document.getElementById('disallowed').value;
                d.cached = document.getElementById('cached').value;
                d.upstream = document.getElementById('upstream').value;
                d.filter_id = document.getElementById('filter_id').value;
                d.matched_rule = document.getElementById('matched_rule').value;
            },
        },

        order: [
            [0, 'desc'],
        ],

        pageLength: 100,

        lengthMenu: [
            [10, 25, 50, 100, 250, 500],
            [10, 25, 50, 100, 250, 500],
        ],

        columns: [
            {
                data: 'event_time_wib',
                name: 'event_time',
            },
            {
                data: 'client_ip',
                name: 'client_ip',
            },
            {
                data: 'client_name',
                name: 'client_name',
                defaultContent: '-',
            },
            {
                data: 'vlan_name',
                name: 'vlan_name',
                defaultContent: '-',
            },
            {
                data: 'domain',
                name: 'domain',
            },
            {
                data: 'query_type',
                name: 'query_type',
            },
            {
                data: 'status',
                name: 'status',
            },
            {
                data: 'reason',
                name: 'reason',
                defaultContent: '-',
            },
            {
                data: 'cached',
                name: 'cached',
                render: function (data) {
                    return data
                        ? '<span class="badge text-bg-info">Yes</span>'
                        : '<span class="badge text-bg-secondary">No</span>';
                },
            },
            {
                data: 'elapsed_ms',
                name: 'elapsed_ms',
                render: function (data) {
                    if (data === null || data === undefined) {
                        return '-';
                    }

                    return Number(data).toFixed(2) + ' ms';
                },
            },
            {
                data: 'upstream',
                name: 'upstream',
                defaultContent: '-',
                render: function (data) {
                    return data || '-';
                },
            },
            {
                data: 'disallowed',
                name: 'disallowed',
                render: function (data) {
                    return data
                        ? '<span class="badge text-bg-danger">Blocked</span>'
                        : '<span class="badge text-bg-success">Allowed</span>';
                },
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function () {
                    return `
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary btn-query-detail"
                        >
                            Detail
                        </button>
                    `;
                },
            },
        ],

        language: {
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data DNS Query Log',

            paginate: {
                first: 'Pertama',
                previous: 'Sebelumnya',
                next: 'Selanjutnya',
                last: 'Terakhir',
            },
        },
    });

    document
        .getElementById('btn-filter')
        .addEventListener('click', function () {
            table.ajax.reload();
        });

    document
        .getElementById('btn-reset')
        .addEventListener('click', function () {
            const filterIds = [
                'date_from',
                'date_to',
                'vlan_name',
                'client_ip',
                'client_name',
                'domain',
                'query_type',
                'status',
                'reason',
                'disallowed',
                'cached',
                'upstream',
                'filter_id',
                'matched_rule',
            ];

            filterIds.forEach(function (id) {
                document.getElementById(id).value = '';
            });

            setDefaultDateRange();

            table.search('');
            table.ajax.reload();
        });

    
    function formatJson(value) {
        if (!value) {
            return '-';
        }

        try {
            const parsed = typeof value === 'string'
                ? JSON.parse(value)
                : value;

            return JSON.stringify(parsed, null, 2);
        } catch (error) {
            return value;
        }
    }

    document
        .getElementById('dns-query-table')
        .addEventListener('click', async function (event) {
            const button = event.target.closest('.btn-query-detail');

            if (!button) {
                return;
            }

            const rowElement = button.closest('tr');

            let row = table.row(rowElement);

            if (rowElement.classList.contains('child')) {
                row = table.row(rowElement.previousElementSibling);
            }

            const rowData = row.data();

            if (!rowData || !rowData.query_id) {
                return;
            }

            const originalButtonText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = 'Memuat...';

            try {
                const url = detailUrlTemplate.replace(
                    '__QUERY_ID__',
                    encodeURIComponent(rowData.query_id)
                );

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error(
                        `Gagal mengambil detail query. HTTP ${response.status}`
                    );
                }

                const result = await response.json();
                const data = result.data;

                document.getElementById('detail-event-time').textContent =
                    data.event_time_wib || '-';

                document.getElementById('detail-client').textContent =
                    `${data.client_ip || '-'} / ${data.client_name || '-'}`;

                document.getElementById('detail-vlan').textContent =
                    data.vlan_name || '-';

                document.getElementById('detail-domain').textContent =
                    data.domain || '-';

                document.getElementById('detail-query').textContent =
                    `${data.query_class || '-'} / ${data.query_type || '-'}`;

                document.getElementById('detail-status').textContent =
                    data.status || '-';

                document.getElementById('detail-reason').textContent =
                    data.reason || '-';

                document.getElementById('detail-filter-id').textContent =
                    data.filter_id ?? '-';

                document.getElementById('detail-matched-rule').textContent =
                    data.matched_rule || '-';

                document.getElementById('detail-upstream').textContent =
                    data.upstream || '-';

                document.getElementById('detail-elapsed').textContent =
                    data.elapsed_ms !== null && data.elapsed_ms !== undefined
                        ? `${Number(data.elapsed_ms).toFixed(3)} ms`
                        : '-';

                document.getElementById('detail-answers-json').textContent =
                    formatJson(data.answers_json);

                document.getElementById('detail-rules-json').textContent =
                    formatJson(data.rules_json);

                document.getElementById('detail-whois-json').textContent =
                    formatJson(data.client_whois_json);

                document.getElementById('detail-raw-json').textContent =
                    formatJson(data.raw_json);

                const modalElement = document.getElementById(
                    'queryDetailModal'
                );

                const modal = bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );

                modal.show();
            } catch (error) {
                console.error(error);

                alert('Gagal mengambil detail DNS query.');
            } finally {
                button.disabled = false;
                button.innerHTML = originalButtonText;
            }
        });


    function formatDateTimeLocal(date) {
        const pad = function (value) {
            return String(value).padStart(2, '0');
        };

        return [
            date.getFullYear(),
            pad(date.getMonth() + 1),
            pad(date.getDate()),
        ].join('-') + 'T' + [
            pad(date.getHours()),
            pad(date.getMinutes()),
        ].join(':');
    }

    function setDefaultDateRange() {
        const dateTo = new Date();

        const dateFrom = new Date(dateTo.getFullYear(), 0, 1);

        document.getElementById('date_from').value =
            formatDateTimeLocal(dateFrom);

        document.getElementById('date_to').value =
            formatDateTimeLocal(dateTo);
    }

    setDefaultDateRange();
});