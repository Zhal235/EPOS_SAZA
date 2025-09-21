import './bootstrap';

// Debug Livewire
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    console.log('Livewire available:', typeof window.Livewire !== 'undefined');
    
    if (typeof window.Livewire !== 'undefined') {
        console.log('Livewire is loaded successfully');
    } else {
        console.error('Livewire is NOT loaded!');
    }
});
