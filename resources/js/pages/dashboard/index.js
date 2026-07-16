import { formatDateTimeLocal } from './utils';
import { renderSummary } from './summary';
import { renderRankingTable } from './rankings';
import { renderTimelineChart, renderAllowedBlockedTimelineChart } from './charts';

const page = document.getElementById('dashboard-page');


if (!page) {

    return;

} else {

    const Dashboard = {

        page,

        summaryUrl: page.dataset.summaryUrl,

        async init() {

            console.log('Init dashboard');

            this.initializeDefaultDateRange();

            this.bindEvents();

            await this.loadDashboard();

        },

        bindEvents() {

            document
                .getElementById('btnApplyFilter')
                .addEventListener('click', async () => {

                    await this.loadDashboard();

                });

            document
                .getElementById('btnResetFilter')
                .addEventListener('click', async () => {

                    this.initializeDefaultDateRange();

                    await this.loadDashboard();

                });

        },

        getFilters() {

            return {

                date_from: document.getElementById('dateFrom').value,

                date_to: document.getElementById('dateTo').value,

            };

        },

        async loadDashboard() {

            const params = new URLSearchParams(
                this.getFilters()
            );

            const response = await fetch(
                `${this.summaryUrl}?${params.toString()}`,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Failed to load dashboard.');
            }

            const result = await response.json();

            renderSummary(result.data.summary);

            renderRankingTable({
                containerId: 'topDomainsLoading',
                columns: [
                    {
                        key: 'domain',
                        title: 'Domain',
                    },
                    {
                        key: 'total',
                        title: 'Total',
                        align: 'end',
                    },
                ],
                data: result.data.rankings.top_domains,
            });
           
            renderRankingTable({
                containerId: 'topClientsLoading',
                columns: [
                    {
                        key: 'client_name',
                        title: 'Client',
                    },
                    {
                        key: 'vlan_name',
                        title: 'VLAN',
                    },
                    {
                        key: 'total',
                        title: 'Total',
                        align: 'end',
                    },
                ],
                data: result.data.rankings.top_clients,
            });
         
            renderRankingTable({
                containerId: 'topVlansLoading',

                columns: [
                    {
                        key: 'vlan_name',
                        title: 'VLAN',
                    },
                    {
                        key: 'total',
                        title: 'Total',
                        align: 'end',
                    },
                ],

                data: result.data.rankings.top_vlans,
            });


            renderRankingTable({
                containerId: 'topBlockedDomainsLoading',

                columns: [
                    {
                        key: 'domain',
                        title: 'Domain',
                    },
                    {
                        key: 'total',
                        title: 'Blocked',
                        align: 'end',
                    },
                ],

                data: result.data.rankings.top_blocked_domains,
            });

            renderTimelineChart(
                result.data.charts.query_timeline
            );

            renderAllowedBlockedTimelineChart(
                result.data.charts.allowed_blocked_timeline
            );

        },

        initializeDefaultDateRange() {

            const now = new Date();

            const startOfYear = new Date(
                now.getFullYear(),
                0,
                1,
                0,
                0,
                0,
                0
            );

            document.getElementById('dateFrom').value =
                formatDateTimeLocal(startOfYear);

            document.getElementById('dateTo').value =
                formatDateTimeLocal(now);

        },

    };

    Dashboard.init();
    

}