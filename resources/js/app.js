import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

Livewire.start();

let processStateChartRenderer;

const renderProcessStateCharts = async () => {
    processStateChartRenderer ??= import('./process-state-chart');
    const { renderProcessStateChart } = await processStateChartRenderer;

    document.querySelectorAll('[data-horizontal-bar-chart]:not([data-chart-initialized])').forEach((canvas) => {
        if (canvas instanceof HTMLCanvasElement) {
            canvas.dataset.chartInitialized = 'true';
            renderProcessStateChart(canvas);
        }
    });
};

const processStateChartObserver = new MutationObserver(() => {
    renderProcessStateCharts().catch(() => {
        document.querySelectorAll('[data-horizontal-bar-chart]').forEach((canvas) => {
            canvas.closest('[data-process-state-chart-container]')?.remove();
        });
        processStateChartObserver.disconnect();
    });
});

processStateChartObserver.observe(document.body, { childList: true, subtree: true });
renderProcessStateCharts().catch(() => {
    document.querySelectorAll('[data-horizontal-bar-chart]').forEach((canvas) => {
        canvas.closest('[data-process-state-chart-container]')?.remove();
    });
    processStateChartObserver.disconnect();
});

const historyForm = document.querySelector('[data-history-filter-form]');

if (historyForm instanceof HTMLFormElement) {
    const periodInput = historyForm.querySelector('[data-history-period]');
    const rangeInputs = historyForm.querySelector('[data-history-custom-range]');
    const fromInput = historyForm.querySelector('[data-history-from]');
    const toInput = historyForm.querySelector('[data-history-to]');
    const clientError = historyForm.querySelector('[data-history-client-error]');
    const stateCheckboxes = Array.from(historyForm.querySelectorAll('[data-history-state]'));
    const selectAllStatesButton = historyForm.querySelector('[data-select-all-history-states]');
    const clearStatesButton = historyForm.querySelector('[data-clear-history-states]');

    selectAllStatesButton?.addEventListener('click', () => {
        stateCheckboxes.forEach((checkbox) => {
            if (checkbox instanceof HTMLInputElement) {
                checkbox.checked = true;
            }
        });
    });

    clearStatesButton?.addEventListener('click', () => {
        stateCheckboxes.forEach((checkbox) => {
            if (checkbox instanceof HTMLInputElement) {
                checkbox.checked = false;
            }
        });
    });

    const updateHistoryRange = () => {
        const isCustom = periodInput instanceof HTMLSelectElement && periodInput.value === 'custom';

        if (rangeInputs instanceof HTMLElement) {
            rangeInputs.hidden = !isCustom;
        }

    };

    if (periodInput instanceof HTMLSelectElement) {
        periodInput.addEventListener('change', updateHistoryRange);
        updateHistoryRange();
    }

    historyForm.addEventListener('submit', (event) => {
        if (!(periodInput instanceof HTMLSelectElement) || periodInput.value !== 'custom') {
            return;
        }

        if (!(fromInput instanceof HTMLInputElement) || !(toInput instanceof HTMLInputElement)) {
            return;
        }

        const today = toInput.max;
        let message = '';

        if (!fromInput.value || !toInput.value) {
            message = 'Completa las fechas Desde y Hasta para aplicar el rango personalizado.';
        } else if (fromInput.value > toInput.value) {
            message = 'La fecha Desde debe ser anterior o igual a Hasta.';
        } else if (fromInput.value > today || toInput.value > today) {
            message = 'El rango no puede incluir fechas futuras.';
        }

        if (clientError instanceof HTMLElement) {
            clientError.textContent = message;
            clientError.hidden = message === '';
        }

        if (message !== '') {
            event.preventDefault();
            (fromInput.value > toInput.value ? toInput : fromInput).focus();
        }
    });
}
