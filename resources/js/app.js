import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

Livewire.start();

const processStateCanvas = document.querySelector('[data-process-state-chart]');

if (processStateCanvas instanceof HTMLCanvasElement) {
    import('./process-state-chart').then(({ renderProcessStateChart }) => {
        renderProcessStateChart(processStateCanvas);
    }).catch(() => {
        processStateCanvas.closest('[data-process-state-chart-container]')?.remove();
    });
}
