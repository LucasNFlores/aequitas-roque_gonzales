import Chart from '../../node_modules/chart.js/auto/auto.js';

const charts = new WeakMap();

document.addEventListener('aequitas:theme-changed', () => {
    document.querySelectorAll('[data-horizontal-bar-chart]').forEach((canvas) => {
        if (canvas instanceof HTMLCanvasElement && charts.has(canvas)) {
            renderProcessStateChart(canvas);
        }
    });
});

export function renderProcessStateChart(canvas) {
    const labels = JSON.parse(canvas.dataset.labels ?? '[]');
    const values = JSON.parse(canvas.dataset.values ?? '[]');
    const chartContainer = canvas.closest('[data-process-state-chart-container]');
    const tokens = getComputedStyle(document.documentElement);
    const navy = tokens.getPropertyValue('--aequitas-chart-series').trim() || '#243850';
    const ink = tokens.getPropertyValue('--aequitas-ink').trim() || '#172538';
    const muted = tokens.getPropertyValue('--aequitas-muted').trim() || '#5b6470';
    const line = tokens.getPropertyValue('--aequitas-line').trim() || '#dde2e8';

    if (chartContainer instanceof HTMLElement) {
        chartContainer.style.height = `${Math.max(180, labels.length * 44)}px`;
    }

    charts.get(canvas)?.destroy();

    const chart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: navy,
                borderRadius: 4,
                maxBarThickness: 32,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => ` ${new Intl.NumberFormat('es-AR').format(context.parsed.x)}`,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: muted },
                    grid: { color: line },
                },
                y: {
                    ticks: { color: ink },
                    grid: { display: false },
                },
            },
        },
    });

    charts.set(canvas, chart);
}
