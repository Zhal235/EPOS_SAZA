// SIMPels Connection Alert Handler
// Handles specific alerts for SIMPels API connection failures

class SimpelsConnectionAlertHandler {
    constructor() {
        this.setupEventListeners();
        console.log('SIMPels Connection Alert Handler initialized');
    }

    setupEventListeners() {
        // Listen for Livewire events from backend
        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.on('showSimpelsConnectionError', (data) => {
                this.showConnectionErrorModal(data);
            });
        }
        
        // Listen for custom events
        document.addEventListener('simpels-connection-error', (event) => {
            this.showConnectionErrorModal(event.detail);
        });
    }
    
    /**
     * Show connection error modal with specific messaging for SIMPels failures
     */
    showConnectionErrorModal(data) {
        const title = data.title || '🔌 Koneksi Server SIMPels Terputus';
        const message = data.message || 'Tidak dapat terhubung ke server SIMPels.';
        const details = data.details || [];
        const technicalError = data.error || '';
        
        // Create modal HTML
        const modalHtml = `
            <div id="simpels-error-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                    <!-- Header -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-wifi text-red-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                            <p class="text-sm text-red-600">Server Error</p>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">${message}</p>
                        
                        ${details.length > 0 ? `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-medium text-yellow-800 mb-2">📋 Langkah yang perlu dilakukan:</h4>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                ${details.map(detail => `<li>• ${detail}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                        
                        ${technicalError ? `
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">Technical Details</summary>
                            <div class="mt-2 p-3 bg-gray-100 rounded text-xs text-gray-600 font-mono">
                                ${technicalError}
                            </div>
                        </details>
                        ` : ''}
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button onclick="simpelsConnectionAlert.retryConnection()" 
                                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-redo mr-2"></i>Coba Lagi
                        </button>
                        <button onclick="simpelsConnectionAlert.closeModal()" 
                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            <i class="fas fa-times mr-2"></i>Tutup
                        </button>
                    </div>
                    
                    <!-- Alternative Payment Suggestion -->
                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">
                            💡 <strong>Alternatif:</strong> Gunakan pembayaran <strong>TUNAI</strong> atau <strong>QRIS</strong> 
                            untuk melanjutkan transaksi tanpa bergantung pada server SIMPels.
                        </p>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        this.closeModal();
        
        // Insert modal into page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Auto-focus close button
        setTimeout(() => {
            const modal = document.getElementById('simpels-error-modal');
            if (modal) {
                modal.querySelector('button:last-of-type')?.focus();
            }
        }, 100);
        
        // Auto-close after 30 seconds
        this.autoCloseTimer = setTimeout(() => {
            this.closeModal();
        }, 30000);
        
        // Log error for admin debugging
        this.logConnectionError(data);
    }
    
    /**
     * Retry connection test
     */
    async retryConnection() {
        // Show loading state
        const retryBtn = document.querySelector('#simpels-error-modal button:first-of-type');
        if (retryBtn) {
            retryBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
            retryBtn.disabled = true;
        }
        
        try {
            // Test connection using existing API
            if (window.testAPIConnection && typeof window.testAPIConnection === 'function') {
                await window.testAPIConnection();
            }
            
            // Close modal on success
            this.closeModal();
            
            // Show success notification
            if (window.notificationSystem) {
                window.notificationSystem.success(
                    '✅ Koneksi Berhasil',
                    'Server SIMPels kembali dapat diakses. Silakan coba transaksi RFID kembali.',
                    { duration: 5000 }
                );
            }
            
        } catch (error) {
            console.log('Connection retry failed:', error.message);
            
            // Restore button state
            if (retryBtn) {
                retryBtn.innerHTML = '<i class="fas fa-redo mr-2"></i>Coba Lagi';
                retryBtn.disabled = false;
            }
            
            // Update modal message
            const messageArea = document.querySelector('#simpels-error-modal .text-gray-700');
            if (messageArea) {
                messageArea.textContent = 'Server SIMPels masih tidak dapat diakses. Silakan hubungi administrator.';
            }
        }
    }
    
    /**
     * Close modal
     */
    closeModal() {
        const modal = document.getElementById('simpels-error-modal');
        if (modal) {
            modal.remove();
        }
        
        if (this.autoCloseTimer) {
            clearTimeout(this.autoCloseTimer);
            this.autoCloseTimer = null;
        }
    }
    
    /**
     * Log connection error for debugging
     */
    logConnectionError(data) {
        const errorLog = {
            timestamp: new Date().toISOString(),
            type: 'simpels_connection_error',
            title: data.title,
            message: data.message,
            technical_error: data.error,
            user_agent: navigator.userAgent,
            url: window.location.href
        };
        
        // Store in localStorage for admin debugging
        const logs = JSON.parse(localStorage.getItem('simpels_error_logs') || '[]');
        logs.unshift(errorLog);
        
        // Keep only last 50 logs
        if (logs.length > 50) {
            logs.splice(50);
        }
        
        localStorage.setItem('simpels_error_logs', JSON.stringify(logs));
        
        console.error('SIMPels Connection Error Logged:', errorLog);
    }
    
    /**
     * Get error logs for admin debugging
     */
    getErrorLogs() {
        return JSON.parse(localStorage.getItem('simpels_error_logs') || '[]');
    }
    
    /**
     * Clear error logs
     */
    clearErrorLogs() {
        localStorage.removeItem('simpels_error_logs');
        console.log('SIMPels error logs cleared');
    }
    
    /**
     * Show simple alert for quick notifications
     */
    showQuickAlert(message, type = 'error') {
        if (window.notificationSystem) {
            const title = {
                'error': '❌ SIMPels Error',
                'warning': '⚠️ SIMPels Warning',
                'info': 'ℹ️ SIMPels Info'
            }[type] || '❌ SIMPels Error';
            
            window.notificationSystem[type] || window.notificationSystem.error(
                title, 
                message,
                { 
                    duration: type === 'error' ? 8000 : 5000,
                    sound: true,
                    showProgress: false
                }
            );
        } else {
            // Fallback to browser alert
            alert(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Initialize handler when DOM is ready
let simpelsConnectionAlert;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        simpelsConnectionAlert = new SimpelsConnectionAlertHandler();
        window.simpelsConnectionAlert = simpelsConnectionAlert;
    });
} else {
    simpelsConnectionAlert = new SimpelsConnectionAlertHandler();
    window.simpelsConnectionAlert = simpelsConnectionAlert;
}

// Export for global access
window.SimpelsConnectionAlertHandler = SimpelsConnectionAlertHandler;

console.log('SIMPels Connection Alert Handler loaded');