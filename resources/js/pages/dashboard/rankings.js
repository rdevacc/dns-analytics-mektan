export function renderRankingTable({
    containerId,
    columns,
    data,
    emptyMessage = 'Tidak ada data.',
}) {

    const container = document.getElementById(containerId);

    if (!container) {
        return;
    }

    if (!data || data.length === 0) {

        container.innerHTML = `
            <div class="text-muted">
                ${emptyMessage}
            </div>
        `;

        return;
    }

    let html = `
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
    `;

    columns.forEach(column => {

        html += `
            <th class="${column.align === 'end' ? 'text-end' : ''}">
                ${column.title}
            </th>
        `;

    });

    html += `
                </tr>
            </thead>
            <tbody>
    `;

    data.forEach(row => {

        html += '<tr>';

        columns.forEach(column => {

            const value = row[column.key] ?? '-';

            html += `
                <td class="${column.align === 'end' ? 'text-end' : ''}">
                    ${value}
                </td>
            `;

        });

        html += '</tr>';

    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;

}