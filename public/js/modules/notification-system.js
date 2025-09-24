// EPOS Notification System
class EPOSNotificationSystem {
    constructor() {
        this.notifications = [];
        this.container = null;
        this.soundEnabled = true;
        this.maxNotifications = 5;
        this.defaultDuration = 5000; // 5 seconds
        
        this.init();
    }
    
    init() {
        this.createContainer();
        this.loadSounds();
        console.log('EPOS Notification System initialized');
    }
    
    createContainer() {
        // Remove existing container if any
        const existing = document.querySelector('.notification-container');
        if (existing) {
            existing.remove();
        }
        
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }
    
    loadSounds() {
        // Preload notification sounds
        this.sounds = {
            success: new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp3aFBFApGn+DyvmwhBkKY3u/JdSMBGn3v8iCocRkAPFmf8dh5LQguiO3pjD8XGkOa/Pd') // Simple success beep
        };
        
        // Set volume
        Object.values(this.sounds).forEach(sound => {
            sound.volume = 0.3;
        });
    }
    
    // Public API methods
    success(title, message, options = {}) {
        return this.show('success', title, message, options);
    }
    
    error(title, message, options = {}) {
        return this.show('error', title, message, options);
    }
    
    warning(title, message, options = {}) {
        return this.show('warning', title, message, options);
    }
    
    info(title, message, options = {}) {
        return this.show('info', title, message, options);
    }
    
    // RFID specific notifications
    rfidSuccess(customerName, amount, newBalance, transactionRef) {
        const options = {
            details: {
                'Customer': customerName,
                'Amount': this.formatCurrency(amount),
                'New Balance': this.formatCurrency(newBalance),
                'Transaction': transactionRef
            },
            actions: [
                {
                    text: 'Print Receipt',
                    class: 'primary',
                    callback: () => this.printReceipt(transactionRef)
                },
                {
                    text: 'New Transaction',
                    callback: () => window.location.reload()
                }
            ],
            duration: 8000,
            sound: true
        };
        
        return this.success(
            '✅ Payment Successful!',
            `RFID payment completed successfully for ${customerName}`,
            options
        );
    }
    
    rfidError(errorMessage, customerName = null, amount = null) {
        const options = {
            details: customerName ? {
                'Customer': customerName,
                'Amount': amount ? this.formatCurrency(amount) : 'N/A',
                'Error': errorMessage,
                'Time': new Date().toLocaleTimeString()
            } : {
                'Error': errorMessage,
                'Time': new Date().toLocaleTimeString()
            },
            actions: [
                {
                    text: 'Try Again',
                    class: 'primary',
                    callback: () => this.retryRfidScan()
                },
                {
                    text: 'Use Cash',
                    callback: () => this.switchToCash()
                }
            ],
            duration: 10000,
            sound: true
        };
        
        return this.error(
            '❌ Payment Failed!',
            errorMessage,
            options
        );
    }
    
    rfidScanning(customerName) {
        return this.info(
            '📱 Processing RFID...',
            `Scanning card for ${customerName}`,
            {
                duration: 0, // Don't auto-dismiss
                showProgress: false
            }
        );
    }
    
    // Main show method
    show(type, title, message, options = {}) {
        const notification = this.createNotification(type, title, message, options);
        
        // Remove oldest notification if we have too many
        if (this.notifications.length >= this.maxNotifications) {
            this.remove(this.notifications[0]);
        }
        
        this.notifications.push(notification);
        this.container.appendChild(notification.element);
        
        // Trigger animation
        setTimeout(() => {
            notification.element.classList.add('show');
        }, 10);
        
        // Play sound if enabled
        if (options.sound && this.soundEnabled) {
            this.playSound(type);
        }
        
        // Auto-dismiss if duration is set
        if (options.duration !== 0) {
            const duration = options.duration || this.defaultDuration;
            notification.timeout = setTimeout(() => {
                this.remove(notification);
            }, duration);
            
            // Show progress bar
            if (options.showProgress !== false) {
                this.showProgress(notification, duration);
            }
        }
        
        return notification;
    }
    
    createNotification(type, title, message, options = {}) {
        const notification = {
            id: Date.now() + Math.random(),
            type,
            element: null,
            timeout: null
        };
        
        const element = document.createElement('div');
        element.className = `notification ${type}`;
        
        if (options.sound) {
            element.classList.add('with-sound');
        }
        
        let html = `
            <div class="notification-header">
                <div class="notification-icon">
                    ${this.getIcon(type)}
                </div>
                <div class="notification-content">
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close" onclick="window.notificationSystem.remove(${JSON.stringify(notification).replace(/"/g, '&quot;')})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        `;
        
        // Add details if provided
        if (options.details) {
            html += '<div class="notification-details">';
            for (const [label, value] of Object.entries(options.details)) {
                html += `
                    <div class="detail-row">
                        <span class="detail-label">${label}:</span>
                        <span class="detail-value">${value}</span>
                    </div>
                `;
            }
            html += '</div>';
        }
        
        // Add progress bar
        html += `
            <div class="notification-progress">
                <div class="notification-progress-bar"></div>
            </div>
        `;
        
        // Add action buttons if provided
        if (options.actions && options.actions.length > 0) {
            html += '<div class="notification-actions">';
            options.actions.forEach(action => {
                html += `
                    <button class="notification-btn ${action.class || ''}" 
                            onclick="(${action.callback.toString()})(); window.notificationSystem.remove(${JSON.stringify(notification).replace(/"/g, '&quot;')})">
                        ${action.text}
                    </button>
                `;
            });
            html += '</div>';
        }
        
        element.innerHTML = html;
        notification.element = element;
        
        return notification;
    }
    
    getIcon(type) {
        const icons = {
            success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            error: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
            warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
            info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };
        return icons[type] || icons.info;
    }
    
    showProgress(notification, duration) {
        const progressBar = notification.element.querySelector('.notification-progress-bar');
        if (progressBar) {
            progressBar.style.transform = 'scaleX(0)';
            progressBar.style.transition = `transform ${duration}ms linear`;
            
            setTimeout(() => {
                progressBar.style.transform = 'scaleX(1)';
            }, 10);
        }
    }
    
    remove(notification) {
        if (notification.timeout) {
            clearTimeout(notification.timeout);
        }
        
        notification.element.classList.add('hide');
        
        setTimeout(() => {
            if (notification.element.parentNode) {
                notification.element.parentNode.removeChild(notification.element);
            }
            
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        }, 300);
    }
    
    removeAll() {
        this.notifications.forEach(notification => {
            this.remove(notification);
        });
    }
    
    playSound(type) {
        if (this.sounds[type]) {
            this.sounds[type].currentTime = 0;
            this.sounds[type].play().catch(e => {
                console.warn('Could not play notification sound:', e);
            });
        }
    }
    
    // Utility methods
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    printReceipt(transactionRef) {
        console.log('Printing receipt for transaction:', transactionRef);
        // Implement receipt printing logic
    }
    
    retryRfidScan() {
        console.log('Retrying RFID scan...');
        // Implement retry logic
        if (window.customerScanner) {
            window.customerScanner.showManualRfidInput();
        }
    }
    
    switchToCash() {
        console.log('Switching to cash payment...');
        // Implement switch to cash payment
        if (window.Livewire) {
            const componentId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
            if (componentId) {
                window.Livewire.find(componentId).set('paymentMethod', 'cash');
            }
        }
    }
    
    // Settings
    enableSound() {
        this.soundEnabled = true;
    }
    
    disableSound() {
        this.soundEnabled = false;
    }
    
    setMaxNotifications(max) {
        this.maxNotifications = max;
    }
    
    setDefaultDuration(duration) {
        this.defaultDuration = duration;
    }
}

// Initialize global notification system
window.notificationSystem = new EPOSNotificationSystem();

// Backward compatibility aliases
window.showNotification = function(message, type = 'info', options = {}) {
    return window.notificationSystem.show(type, type.charAt(0).toUpperCase() + type.slice(1), message, options);
};

window.showToast = window.showNotification; // Alias

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EPOSNotificationSystem;
}

console.log('EPOS Notification System loaded successfully');