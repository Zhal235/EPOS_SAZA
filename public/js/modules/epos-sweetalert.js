// SweetAlert Modal Handler for EPOS System
// Handles modal alerts dispatched from Livewire components

class EposSweetAlertHandler {
    constructor() {
        this.setupEventListeners();
        console.log('EPOS SweetAlert Handler initialized');
    }

    setupEventListeners() {
        // Listen for Livewire swal:modal events
        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.on('swal:modal', (data) => {
                this.showModal(data);
            });
        }

        // Listen for custom swal events
        document.addEventListener('swal:show', (event) => {
            this.showModal(event.detail);
        });
    }

    /**
     * Show SweetAlert modal
     */
    async showModal(config) {
        // Check if SweetAlert is available
        if (typeof Swal === 'undefined') {
            console.warn('SweetAlert2 not loaded, using fallback alert');
            this.showFallbackAlert(config);
            return;
        }

        // Default configuration
        const defaultConfig = {
            title: 'Notification',
            text: '',
            type: 'info',
            confirmButtonText: 'OK',
            showCancelButton: false,
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            allowEscapeKey: true,
            buttonsStyling: true,
            customClass: {
                container: 'epos-swal-container',
                popup: 'epos-swal-popup',
                title: 'epos-swal-title',
                content: 'epos-swal-content',
                confirmButton: 'epos-swal-confirm',
                cancelButton: 'epos-swal-cancel'
            }
        };

        // Merge user config with defaults
        const modalConfig = { ...defaultConfig, ...config };
        
        // Map 'type' to 'icon' for SweetAlert2
        if (modalConfig.type) {
            modalConfig.icon = modalConfig.type;
            delete modalConfig.type;
        }

        // Handle specific types with custom styling
        switch (modalConfig.icon) {
            case 'error':
                modalConfig.iconColor = '#e74c3c';
                modalConfig.confirmButtonColor = '#e74c3c';
                break;
            case 'success':
                modalConfig.iconColor = '#2ecc71';
                modalConfig.confirmButtonColor = '#2ecc71';
                break;
            case 'warning':
                modalConfig.iconColor = '#f39c12';
                modalConfig.confirmButtonColor = '#f39c12';
                break;
            case 'info':
                modalConfig.iconColor = '#3498db';
                modalConfig.confirmButtonColor = '#3498db';
                break;
        }

        try {
            const result = await Swal.fire(modalConfig);
            
            // Handle button clicks
            if (result.isConfirmed) {
                this.handleConfirm(config, result);
            } else if (result.isDismissed) {
                this.handleCancel(config, result);
            }
            
            return result;
        } catch (error) {
            console.error('SweetAlert error:', error);
            this.showFallbackAlert(config);
        }
    }

    /**
     * Handle confirm button click
     */
    handleConfirm(config, result) {
        console.log('SweetAlert confirmed:', config.title);
        
        // Trigger custom events if specified
        if (config.onConfirm) {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm(result);
            } else if (typeof config.onConfirm === 'string') {
                // Dispatch custom event
                document.dispatchEvent(new CustomEvent(config.onConfirm, {
                    detail: { config, result }
                }));
            }
        }
        
        // Handle specific actions based on type
        if (config.icon === 'error' && config.title?.includes('Server SIMPels')) {
            // For server connection errors, suggest switching to cash payment
            this.suggestCashPayment();
        }
    }

    /**
     * Handle cancel button click
     */
    handleCancel(config, result) {
        console.log('SweetAlert cancelled:', config.title);
        
        if (config.onCancel) {
            if (typeof config.onCancel === 'function') {
                config.onCancel(result);
            } else if (typeof config.onCancel === 'string') {
                document.dispatchEvent(new CustomEvent(config.onCancel, {
                    detail: { config, result }
                }));
            }
        }
        
        // Handle retry for connection errors
        if (config.icon === 'error' && config.title?.includes('Server') && config.cancelButtonText === 'Coba Lagi') {
            this.retryConnection();
        }
    }

    /**
     * Suggest cash payment when SIMPels is offline
     */
    suggestCashPayment() {
        // Try to switch to cash payment method if we're on POS terminal
        const cashButton = document.querySelector('button[wire\\:click*="selectPaymentMethod(\'cash\')"]');
        if (cashButton) {
            setTimeout(() => {
                cashButton.click();
                console.log('Switched to cash payment method');
            }, 1000);
        }
    }

    /**
     * Retry connection test
     */
    async retryConnection() {
        console.log('Retrying connection...');
        
        // Show loading alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Testing Connection...',
                text: 'Please wait while we test the SIMPels server connection.',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        try {
            // Test connection using global API function if available
            if (window.testAPIConnection && typeof window.testAPIConnection === 'function') {
                await window.testAPIConnection();
                
                // Connection successful
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Connection Restored!',
                        text: 'SIMPels server is now accessible. You can try RFID payment again.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            } else {
                throw new Error('Connection test function not available');
            }
        } catch (error) {
            console.log('Connection retry failed:', error.message);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Still Offline',
                    text: 'SIMPels server is still not accessible. Please contact the administrator or use cash payment.',
                    icon: 'error',
                    confirmButtonText: 'Use Cash',
                    showCancelButton: true,
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.suggestCashPayment();
                    }
                });
            }
        }
    }

    /**
     * Fallback alert when SweetAlert is not available
     */
    showFallbackAlert(config) {
        const message = `${config.title}${config.text ? '\\n\\n' + config.text : ''}`;
        
        if (config.showCancelButton) {
            const confirmed = confirm(message + '\\n\\nClick OK to confirm, Cancel to dismiss.');
            if (confirmed) {
                this.handleConfirm(config, { isConfirmed: true });
            } else {
                this.handleCancel(config, { isDismissed: true });
            }
        } else {
            alert(message);
            this.handleConfirm(config, { isConfirmed: true });
        }
    }

    /**
     * Quick toast notification for non-critical messages
     */
    showToast(message, type = 'info', duration = 3000) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        } else {
            // Fallback to console for toast messages
            console.log(`Toast (${type.toUpperCase()}): ${message}`);
        }
    }
}

// CSS for custom styling
const sweetAlertStyles = `
    .epos-swal-container {
        z-index: 99999;
    }
    
    .epos-swal-popup {
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    
    .epos-swal-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2d3748;
    }
    
    .epos-swal-content {
        font-size: 1rem;
        line-height: 1.5;
        color: #4a5568;
    }
    
    .epos-swal-confirm {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        text-transform: none;
    }
    
    .epos-swal-cancel {
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        text-transform: none;
        background-color: #e2e8f0;
        color: #4a5568;
    }
    
    .epos-swal-cancel:hover {
        background-color: #cbd5e0;
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = sweetAlertStyles;
document.head.appendChild(styleSheet);

// Initialize handler when DOM is ready
let eposSweetAlertHandler;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        eposSweetAlertHandler = new EposSweetAlertHandler();
        window.eposSweetAlert = eposSweetAlertHandler;
    });
} else {
    eposSweetAlertHandler = new EposSweetAlertHandler();
    window.eposSweetAlert = eposSweetAlertHandler;
}

// Export for global use
window.EposSweetAlertHandler = EposSweetAlertHandler;

console.log('EPOS SweetAlert Handler loaded');