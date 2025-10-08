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
        this.setupKeyboardHandlers();
        console.log('EPOS Notification System initialized');
    }
    
    setupKeyboardHandlers() {
        // Close notifications with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.notifications.length > 0) {
                this.removeAll();
            }
        });
    }
    
    createContainer() {
        // Remove existing containers if any
        const existing = document.querySelector('.notification-container');
        if (existing) {
            existing.remove();
        }
        
        const existingOverlay = document.querySelector('.notification-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        // Create overlay for modal effect (only used for special notifications)
        this.overlay = document.createElement('div');
        this.overlay.className = 'notification-overlay';
        
        // Close notifications when clicking on overlay (outside popup)
        this.overlay.addEventListener('click', (e) => {
            // Only close if clicking directly on overlay, not on notification content
            if (e.target === this.overlay) {
                this.removeModalNotifications();
            }
        });
        
        document.body.appendChild(this.overlay);
        
        // Create notification container
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
    
    // Simple modal-style notifications (clear previous first)
    showSuccessModal(title, message) {
        this.removeAll(); // Clear all existing notifications
        this.container.classList.add('modal-centered');
        this.overlay.classList.add('show');
        
        return this.success(title, message, {
            duration: 5000, // Auto-dismiss after 5 seconds
            sound: true,
            showProgress: true, // Show countdown progress bar
            modal: true,
            actions: [
                {
                    text: 'OK',
                    class: 'primary',
                    callback: () => this.hideModal()
                }
            ]
        });
    }
    
    showErrorModal(title, message) {
        this.removeAll(); // Clear all existing notifications
        this.container.classList.add('modal-centered');
        this.overlay.classList.add('show');
        
        return this.error(title, message, {
            duration: 8000, // Auto-dismiss after 8 seconds (longer for errors)
            sound: true,
            showProgress: true, // Show countdown progress bar
            modal: true,
            actions: [
                {
                    text: 'OK',
                    class: 'primary',
                    callback: () => this.hideModal()
                }
            ]
        });
    }
    
    // Hide modal overlay and reset container position
    hideModal() {
        this.container.classList.remove('modal-centered');
        this.overlay.classList.remove('show');
        this.removeAll();
    }
    
    // Remove only modal notifications (those with overlay)
    removeModalNotifications() {
        const modalNotifications = this.notifications.filter(n => n.modal);
        modalNotifications.forEach(n => this.remove(n));
        
        if (modalNotifications.length > 0) {
            this.hideModal();
        }
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
        // Validate inputs to prevent undefined errors
        if (!type) type = 'info';
        if (!title) title = 'Notification';
        if (!message) message = '';
        
        // If this is a session message, clear previous session messages first
        if (options.sessionMessage) {
            this.removeSessionMessages();
        }
        
        const notification = this.createNotification(type, title, message, options);
        
        // Remove oldest notification if we have too many (but keep modal notifications)
        if (this.notifications.length >= this.maxNotifications) {
            const regularNotifications = this.notifications.filter(n => !n.modal);
            if (regularNotifications.length > 0) {
                this.remove(regularNotifications[0]);
            }
        }
        
        this.notifications.push(notification);
        this.container.appendChild(notification.element);
        
        // Only show overlay for modal notifications
        if (options.modal && this.overlay) {
            this.overlay.classList.add('show');
        }
        
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
            timeout: null,
            modal: options.modal || false,
            sessionMessage: options.sessionMessage || false
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
                    <div class="notification-title">${title || 'Notification'}</div>
                    <div class="notification-message">${message || ''}</div>
                </div>
                <button class="notification-close" onclick="window.notificationSystem.removeById('${notification.id}')">
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
            
            // Add auto-close info if duration is set
            if (options.duration && options.duration > 0) {
                const seconds = Math.round(options.duration / 1000);
                html += `<span class="notification-auto-close">Auto-close dalam ${seconds} detik</span>`;
            }
            
            // Add action buttons
            html += '<div style="display: flex; gap: 8px;">';
            options.actions.forEach((action, index) => {
                const actionId = `action_${notification.id}_${index}`;
                // Store action callback globally to avoid serialization issues
                window[actionId] = action.callback;
                
                html += `
                    <button class="notification-btn ${action.class || ''}" 
                            onclick="if(window['${actionId}']) window['${actionId}'](); window.notificationSystem.removeById('${notification.id}')">
                        ${action.text || 'Action'}
                    </button>
                `;
            });
            html += '</div></div>';
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
            
            // Hide overlay if no more notifications
            if (this.notifications.length === 0 && this.overlay) {
                this.overlay.classList.remove('show');
            }
        }, 300);
    }
    
    removeById(id) {
        const notification = this.notifications.find(n => n.id == id);
        if (notification) {
            this.remove(notification);
        }
    }
    
    removeAll() {
        this.notifications.forEach(notification => {
            this.remove(notification);
        });
    }
    
    // Remove only session message notifications
    removeSessionMessages() {
        const sessionNotifications = this.notifications.filter(n => n.sessionMessage);
        sessionNotifications.forEach(n => this.remove(n));
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