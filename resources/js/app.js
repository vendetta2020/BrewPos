import './bootstrap';

// Global helper to safely initialize Lucide icons
export function initIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Hook into DOM lifecycle and Livewire lifecycle
document.addEventListener('DOMContentLoaded', initIcons);
document.addEventListener('livewire:navigated', initIcons);
document.addEventListener('livewire:initialized', () => {
    if (window.Livewire) {
        Livewire.hook('morph.updated', initIcons);
        Livewire.hook('commit', initIcons);
    }
});
