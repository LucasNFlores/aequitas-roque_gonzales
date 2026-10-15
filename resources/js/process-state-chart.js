import Chart from '../../node_modules/chart.js/auto/auto.js';

export function renderProcessStateChart(canvas) {
    const labels = JSON.parse(canvas.dataset.labels ?? '[]');
    const values = JSON.parse(canvas.dataset.values ?? '[]');
    const chartContainer = canvas.closest('[data-process-state-chart-container]');

    if (chartContainer instanceof HTMLElement) {
        chartContainer.style.height = `${Math.max(180, labels.length * 44)}px`;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: '#4f46e5',
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
                    ticks: { precision: 0, color: '#4b5563' },
                    grid: { color: '#e5e7eb' },
                },
                y: {
                    ticks: { color: '#374151' },
                    grid: { display: false },
                },
            },
        },
    });
}
