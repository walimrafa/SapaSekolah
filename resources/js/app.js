import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.Chart = Chart;

// Auto initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Toast notification handler
window.showToast = function (message, type = 'info') {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message, type }
    }));
};

Alpine.start();
