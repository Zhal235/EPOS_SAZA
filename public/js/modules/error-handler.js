// Error Handler Module with Debounce and Prevention of Duplicate Notifications
class ErrorHandler {
    constructor() {
        this.errorCache = new Map();
        this.notificationDebounceTime = 3000; // 3 seconds between same error notifications
        this.maxSameErrorCount = 3; // Maximum same error notifications per session
        this.connectionErrorFlag = false;
        this.connectionErrorCooldown = 30000; // 30 seconds cooldown for connection errors
        this.lastConnectionErrorTime = 0;
        
        this.init();
    }
    
    init() {
        console.log('Error Handler initialized - preventing duplicate error notifications');
    }
    
    /**
     * Handle API errors with intelligent deduplication
     */
    handleAPIError(error, context = {}) {
        const errorKey = this.generateErrorKey(error, context);
        const now = Date.now();
        
        // Check if this is a connection error
        if (this.isConnectionError(error)) {
            return this.handleConnectionError(error, context);
        }
        
        // Check if we should show this error
        if (this.shouldShowError(errorKey, now)) {
            this.trackError(errorKey, now);
            this.showError(error, context);
            return true;
        } else {
            // Log the suppressed error for debugging
            console.warn(`[ErrorHandler] Suppressed duplicate error:`, error.message, context);
            return false;
        }
    }
    
    /**
     * Handle connection-specific errors with special logic
     */
    handleConnectionError(error, context = {}) {
        const now = Date.now();
        
        // If we're in connection error cooldown, don't show notification
        if (this.connectionErrorFlag && (now - this.lastConnectionErrorTime) < this.connectionErrorCooldown) {
            console.warn(`[ErrorHandler] Connection error suppressed (cooldown active):`, error.message);
            return false;
        }
        
        // Show connection error notification
        this.connectionErrorFlag = true;
        this.lastConnectionErrorTime = now;
        
        this.showConnectionError(error, context);
        
        // Clear connection error flag after cooldown
        setTimeout(() => {
            this.connectionErrorFlag = false;
            console.log('[ErrorHandler] Connection error cooldown ended');
        }, this.connectionErrorCooldown);
        
        return true;
    }
    
    /**
     * Generate unique key for error deduplication
     */
    generateErrorKey(error, context) {
        const errorMessage = error.message || error.toString();
        const contextString = JSON.stringify(context);
        return `${errorMessage}_${contextString}`.toLowerCase().replace(/[^a-z0-9_]/g, '_');
    }
    
    /**
     * Check if error should be shown based on debouncing rules
     */
    shouldShowError(errorKey, now) {
        const errorInfo = this.errorCache.get(errorKey);
        
        if (!errorInfo) {
            return true; // First time seeing this error
        }
        
        // Check if enough time has passed since last notification
        const timeSinceLastNotification = now - errorInfo.lastNotificationTime;
        if (timeSinceLastNotification < this.notificationDebounceTime) {
            return false; // Too soon since last notification
        }
        
        // Check if we've exceeded max notifications for this error
        if (errorInfo.notificationCount >= this.maxSameErrorCount) {
            return false; // Too many notifications for this error
        }
        
        return true;
    }
    
    /**
     * Track error occurrence
     */
    trackError(errorKey, now) {
        const errorInfo = this.errorCache.get(errorKey) || {
            firstOccurrence: now,
            lastNotificationTime: 0,
            notificationCount: 0,
            occurrenceCount: 0
        };
        
        errorInfo.lastNotificationTime = now;
        errorInfo.notificationCount++;
        errorInfo.occurrenceCount++;
        
        this.errorCache.set(errorKey, errorInfo);
        
        // Log error statistics
        console.log(`[ErrorHandler] Error tracked:`, {
            key: errorKey,
            notificationCount: errorInfo.notificationCount,
            occurrenceCount: errorInfo.occurrenceCount
        });
    }
    
    /**
     * Check if error is connection-related
     */
    isConnectionError(error) {
        const connectionKeywords = [
            'network',
            'connection',
            'timeout',
            'fetch',
            'cors',
            'refused',
            'unreachable',
            'offline',
            'abort'
        ];
        
        const errorString = error.message.toLowerCase();
        return connectionKeywords.some(keyword => errorString.includes(keyword));
    }
    
    /**
     * Show regular error notification
     */
    showError(error, context = {}) {
        const title = this.getErrorTitle(error, context);
        const message = this.getErrorMessage(error, context);
        const options = this.getErrorOptions(error, context);
        
        if (window.notificationSystem) {
            window.notificationSystem.error(title, message, options);
        } else {
            // Fallback to console and alert
            console.error(`${title}: ${message}`);
            alert(`${title}\n${message}`);
        }
        
        this.logError(error, context);
    }
    
    /**
     * Show connection error notification with special handling
     */
    showConnectionError(error, context = {}) {
        const title = '🌐 Koneksi API Terputus';
        const message = 'Tidak dapat terhubung ke server SIMPels. Sistem akan mencoba kembali secara otomatis.';
        
        const options = {
            duration: 8000,
            details: {
                'Error': error.message,
                'Time': new Date().toLocaleTimeString(),
                'Next Retry': 'Automatic in 30 seconds'
            },
            actions: [
                {
                    text: 'Test Connection',
                    class: 'primary',
                    callback: () => this.testConnection()
                },
                {
                    text: 'Refresh Page',
                    callback: () => window.location.reload()
                }
            ]
        };
        
        if (window.notificationSystem) {
            window.notificationSystem.error(title, message, options);
        } else {
            console.error(`${title}: ${message}`);
            alert(`${title}\n${message}`);
        }
        
        this.logConnectionError(error, context);
    }
    
    /**
     * Get appropriate error title based on error type and context
     */
    getErrorTitle(error, context) {
        if (context.operation === 'payment') {
            return '💳 Pembayaran Gagal';
        } else if (context.operation === 'rfid_scan') {
            return '📱 RFID Scan Gagal';
        } else if (context.operation === 'balance_check') {
            return '💰 Cek Saldo Gagal';
        } else {
            return '⚠️ Error Sistem';
        }
    }
    
    /**
     * Get formatted error message
     */
    getErrorMessage(error, context) {
        let message = error.message || 'Terjadi kesalahan yang tidak diketahui';
        
        // Add context information if available
        if (context.rfid_tag) {
            message += `\nRFID: ${context.rfid_tag}`;
        }
        if (context.amount) {
            message += `\nJumlah: ${this.formatCurrency(context.amount)}`;
        }
        if (context.customer_name) {
            message += `\nCustomer: ${context.customer_name}`;
        }
        
        return message;
    }
    
    /**
     * Get error notification options
     */
    getErrorOptions(error, context) {
        const options = {
            duration: 6000,
            details: {
                'Error': error.message,
                'Time': new Date().toLocaleTimeString()
            }
        };
        
        // Add retry action for certain operations
        if (context.operation && context.retryCallback) {
            options.actions = [
                {
                    text: 'Try Again',
                    class: 'primary',
                    callback: context.retryCallback
                }
            ];
        }
        
        return options;
    }
    
    /**
     * Test API connection manually
     */
    async testConnection() {
        try {
            if (window.simpelsAPI) {
                const result = await window.simpelsAPI.testConnection();
                if (result.success) {
                    window.notificationSystem?.success(
                        '✅ Koneksi Berhasil',
                        'Koneksi ke server SIMPels telah pulih'
                    );
                    this.resetConnectionError();
                } else {
                    throw new Error(result.message);
                }
            }
        } catch (error) {
            window.notificationSystem?.error(
                '❌ Koneksi Masih Gagal',
                'Masih tidak dapat terhubung ke server: ' + error.message
            );
        }
    }
    
    /**
     * Reset connection error state
     */
    resetConnectionError() {
        this.connectionErrorFlag = false;
        this.lastConnectionErrorTime = 0;
        console.log('[ErrorHandler] Connection error state reset');
    }
    
    /**
     * Clear error cache (useful for new session or manual reset)
     */
    clearErrorCache() {
        this.errorCache.clear();
        this.resetConnectionError();
        console.log('[ErrorHandler] Error cache cleared');
    }
    
    /**
     * Get error statistics for debugging
     */
    getErrorStatistics() {
        const stats = {
            totalUniqueErrors: this.errorCache.size,
            connectionErrorActive: this.connectionErrorFlag,
            errors: []
        };
        
        for (const [key, info] of this.errorCache.entries()) {
            stats.errors.push({
                key,
                notificationCount: info.notificationCount,
                occurrenceCount: info.occurrenceCount,
                firstOccurrence: new Date(info.firstOccurrence).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
                lastNotification: new Date(info.lastNotificationTime).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })
            });
        }
        
        return stats;
    }
    
    /**
     * Logging functions
     */
    logError(error, context) {
        console.error('[ErrorHandler] Error occurred:', {
            message: error.message,
            context: context,
            timestamp: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
            stack: error.stack
        });
    }
    
    logConnectionError(error, context) {
        console.error('[ErrorHandler] Connection error:', {
            message: error.message,
            context: context,
            timestamp: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
            connectionErrorFlag: this.connectionErrorFlag,
            lastConnectionErrorTime: this.lastConnectionErrorTime
        });
    }
    
    /**
     * Utility functions
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
}

// Create global instance
window.errorHandler = new ErrorHandler();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ErrorHandler;
}

console.log('Error Handler Module loaded successfully');