import { formatNumber } from './utils';

export function renderSummary(summary) {

    document.getElementById('totalQueries').textContent =
        formatNumber(summary.total_queries);

    document.getElementById('blockedQueries').textContent =
        formatNumber(summary.blocked_queries);

    document.getElementById('allowedQueries').textContent =
        formatNumber(summary.allowed_queries);

    document.getElementById('cachedQueries').textContent =
        formatNumber(summary.cached_queries);

}