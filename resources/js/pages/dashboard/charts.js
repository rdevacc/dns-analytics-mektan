import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

import { formatCompactNumber } from './utils';

Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    Tooltip,
    Legend,
    Filler
);

let allowedBlockedChart = null;
let timelineChart = null;


function createGradient(canvas, color) {

    const ctx = canvas.getContext('2d');

    const gradient = ctx.createLinearGradient(
        0,
        0,
        0,
        350
    );

    gradient.addColorStop(0, color);
    gradient.addColorStop(1, 'rgba(255,255,255,0)');

    return gradient;

}

export function renderTimelineChart(data = []) {

    const canvas = document.getElementById(
        'queryTimelineChart'
    );

    if (!canvas) {
        return;
    }

    if (timelineChart) {
        timelineChart.destroy();
    }

    timelineChart = new Chart(canvas, {

        type: 'line',

        data: {

            labels: data.map(item => item.time),

            datasets: [
                {
                    label: 'DNS Queries',

                    data: data.map(item => item.total),

                    borderColor: '#0d6efd',

                    backgroundColor: createGradient(
                        canvas,
                        'rgba(13,110,253,.25)'
                    ),

                    fill: true,

                    tension: 0.4,

                    borderWidth: 3,

                    pointRadius: 0,

                    pointHoverRadius: 6,

                    pointHoverBorderWidth: 2,
                }
            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            interaction: {

                mode: 'index',

                intersect: false,

            },

            animation: {

                duration: 900,

                easing: 'easeOutQuart',

            },

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        usePointStyle: true,

                        pointStyle: 'circle',

                        padding: 20,

                    }

                },

                tooltip: {

                    backgroundColor: '#fff',

                    titleColor: '#212529',

                    bodyColor: '#212529',

                    borderColor: '#dee2e6',

                    borderWidth: 1,

                    displayColors: true,

                    callbacks: {

                        label(context) {

                            return context.dataset.label +
                                ': ' +
                                formatCompactNumber(context.parsed.y);

                        }

                    }

                }

            },

            scales: {

                x: {

                    grid: {

                        display: false,

                    }

                },

                y: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0,

                    },

                    grid: {

                        color: '#eef2f7',

                    }

                }

            }

        }

    });

}

export function renderAllowedBlockedTimelineChart(data = []) {

    const canvas = document.getElementById(
        'allowedBlockedTimelineChart'
    );

    if (!canvas) {
        return;
    }

    if (allowedBlockedChart) {
        allowedBlockedChart.destroy();
    }

    allowedBlockedChart = new Chart(canvas, {

        type: 'line',

        data: {

            labels: data.map(item => item.time),

            datasets: [
                {
                    label: 'Allowed',

                    data: data.map(item => item.allowed),

                    borderColor: '#198754',

                    backgroundColor: createGradient(
                        canvas,
                        'rgba(25,135,84,.20)'
                    ),

                    fill: true,

                    tension: .4,

                    borderWidth: 3,

                    pointRadius: 0,

                    pointHoverRadius: 6,

                    pointHoverBorderWidth: 2,
                },

                {
                    label: 'Blocked',

                    data: data.map(item => item.blocked),

                    borderColor: '#dc3545',

                    backgroundColor: createGradient(
                        canvas,
                        'rgba(220,53,69,.18)'
                    ),

                    fill: true,

                    tension: .4,

                    borderWidth: 3,

                    pointRadius: 0,

                    pointHoverRadius: 6,
                }
            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            interaction: {

                mode: 'index',

                intersect: false,

            },

            animation: {

                duration: 900,

                easing: 'easeOutQuart',

            },

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        usePointStyle: true,

                        pointStyle: 'circle',

                        padding: 20,

                    }

                },

                tooltip: {

                    backgroundColor: '#fff',

                    titleColor: '#212529',

                    bodyColor: '#212529',

                    borderColor: '#dee2e6',

                    borderWidth: 1,

                    displayColors: true,

                    callbacks: {

                        label(context) {

                            return context.dataset.label +
                                ': ' +
                                formatCompactNumber(context.parsed.y);

                        }

                    }

                }

            },

            scales: {

                x: {

                    grid: {

                        display: false,

                    }

                },

                y: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0,

                    },

                    grid: {

                        color: '#eef2f7',

                    }

                }

            }

        }

    });

}