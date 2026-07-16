export function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(value ?? 0);
}

export function formatDateTimeLocal(date) {
    const pad = (value) => value.toString().padStart(2, '0');

    return (
        date.getFullYear() +
        '-' +
        pad(date.getMonth() + 1) +
        '-' +
        pad(date.getDate()) +
        'T' +
        pad(date.getHours()) +
        ':' +
        pad(date.getMinutes())
    );
}

export function formatCompactNumber(number) {

    return new Intl.NumberFormat(
        'id-ID',
        {
            notation: 'compact',
            maximumFractionDigits: 1,
        }
    ).format(number);

}