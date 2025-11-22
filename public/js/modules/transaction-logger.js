// Transaction Logger and Monitoring System
class TransactionLogger {
    constructor() {
        this.sessionId = this.generateSessionId();
        this.logBuffer = [];
        this.maxBufferSize = 1000;
        this.autoSyncInterval = 30000; // 30 seconds
        
        this.initializeLogger();
        this.startPeriodicSync();
    }
    
    /**
     * Initialize logger
     */
    initializeLogger() {
        // Log session start
        this.info('Logger initialized', {
            sessionId: this.sessionId,
            timestamp: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
            userAgent: navigator.userAgent,
            url: window.location.href
        });
        
        // Setup error handlers
        this.setupGlobalErrorHandlers();
        
        console.log(`[TransactionLogger] Session ${this.sessionId} started`);
    }
    
    /**
     * Setup global error handlers
     */
    setupGlobalErrorHandlers() {
        // Catch unhandled errors
        window.addEventListener('error', (event) => {
            this.error('Unhandled JavaScript error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack
            });
        });
        
        // Catch unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.error('Unhandled promise rejection', {
                reason: event.reason,
                stack: event.reason?.stack
            });
        });
        
        // Catch network errors
        window.addEventListener('offline', () => {
            this.warn('Network connection lost');
        });
        
        window.addEventListener('online', () => {
            this.info('Network connection restored');
        });
    }
    
    /**
     * Generate unique session ID
     */
    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Main logging function
     */
    log(level, message, data = null, category = 'general') {
        const logEntry = {
            id: this.generateLogId(),
            sessionId: this.sessionId,
            timestamp: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
            level: level.toLowerCase(),
            category: category,
            message: message,
            data: data,
            url: window.location.href,
            userAgent: navigator.userAgent.substr(0, 100) // Truncate for storage
        };
        
        // Add to buffer
        this.logBuffer.push(logEntry);
        
        // Console output
        const consoleMethod = this.getConsoleMethod(level);
        if (data) {
            console[consoleMethod](`[${category.toUpperCase()}] ${message}`, data);
        } else {
            console[consoleMethod](`[${category.toUpperCase()}] ${message}`);
        }
        
        // Store immediately for critical errors
        if (level === 'error' || level === 'critical') {
            this.flushLogs();
        }
        
        // Check buffer size
        if (this.logBuffer.length >= this.maxBufferSize) {
            this.flushLogs();
        }
    }
    
    /**
     * Generate unique log ID
     */
    generateLogId() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    }
    
    /**
     * Get appropriate console method
     */
    getConsoleMethod(level) {
        switch (level.toLowerCase()) {
            case 'error':
            case 'critical':
                return 'error';
            case 'warn':
            case 'warning':
                return 'warn';
            case 'info':
                return 'info';
            case 'debug':
                return 'debug';
            default:
                return 'log';
        }
    }
    
    /**
     * Logging level methods
     */
    debug(message, data = null, category = 'debug') {
        this.log('debug', message, data, category);
    }
    
    info(message, data = null, category = 'info') {
        this.log('info', message, data, category);
    }
    
    warn(message, data = null, category = 'warning') {
        this.log('warn', message, data, category);
    }
    
    error(message, data = null, category = 'error') {
        this.log('error', message, data, category);
    }
    
    critical(message, data = null, category = 'critical') {
        this.log('critical', message, data, category);
    }
    
    /**
     * Specific category logging methods
     */
    logTransaction(event, data) {
        this.info(`Transaction ${event}`, data, 'transaction');
    }
    
    logAPI(method, endpoint, data, response = null) {
        this.info(`API ${method} ${endpoint}`, {
            request: data,
            response: response
        }, 'api');
    }
    
    logRFID(event, rfidTag, data = null) {
        this.info(`RFID ${event}`, {
            rfidTag: rfidTag,
            ...data
        }, 'rfid');
    }
    
    logPayment(event, amount, method, data = null) {
        this.info(`Payment ${event}`, {
            amount: amount,
            method: method,
            ...data
        }, 'payment');
    }
    
    logCustomer(event, customerId, data = null) {
        this.info(`Customer ${event}`, {
            customerId: customerId,
            ...data
        }, 'customer');
    }
    
    logRefund(event, refundRef, data = null) {
        this.info(`Refund ${event}`, {
            refundRef: refundRef,
            ...data
        }, 'refund');
    }
    
    logSystem(event, data = null) {
        this.info(`System ${event}`, data, 'system');
    }
    
    /**
     * Flush logs to persistent storage
     */
    flushLogs() {
        if (this.logBuffer.length === 0) {
            return;
        }
        
        try {
            // Get existing logs
            const existingLogs = JSON.parse(localStorage.getItem('epos_logs') || '[]');
            
            // Add new logs
            const allLogs = [...existingLogs, ...this.logBuffer];
            
            // Keep only last 5000 logs
            const logsToKeep = allLogs.slice(-5000);
            
            // Save to localStorage
            localStorage.setItem('epos_logs', JSON.stringify(logsToKeep));
            
            // Clear buffer
            const flushedCount = this.logBuffer.length;
            this.logBuffer = [];
            
            console.debug(`[TransactionLogger] Flushed ${flushedCount} logs to storage`);
            
        } catch (error) {
            console.error('[TransactionLogger] Failed to flush logs:', error);
            
            // If localStorage is full, clear old logs and try again
            if (error.name === 'QuotaExceededError') {
                this.clearOldLogs();
                this.flushLogs(); // Retry
            }
        }
    }
    
    /**
     * Clear old logs to free up space
     */
    clearOldLogs() {
        try {
            const existingLogs = JSON.parse(localStorage.getItem('epos_logs') || '[]');
            const recentLogs = existingLogs.slice(-1000); // Keep only last 1000
            localStorage.setItem('epos_logs', JSON.stringify(recentLogs));
            
            console.warn('[TransactionLogger] Cleared old logs due to storage quota');
        } catch (error) {
            console.error('[TransactionLogger] Failed to clear old logs:', error);
            localStorage.removeItem('epos_logs'); // Nuclear option
        }
    }
    
    /**
     * Start periodic log sync
     */
    startPeriodicSync() {
        setInterval(() => {
            this.flushLogs();
        }, this.autoSyncInterval);
        
        // Flush on page unload
        window.addEventListener('beforeunload', () => {
            this.flushLogs();
        });
        
        // Flush on visibility change (user switches tabs)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.flushLogs();
            }
        });
    }
    
    /**
     * Get logs by criteria
     */
    getLogs(criteria = {}) {
        const allLogs = JSON.parse(localStorage.getItem('epos_logs') || '[]');
        let filteredLogs = allLogs;
        
        // Filter by level
        if (criteria.level) {
            filteredLogs = filteredLogs.filter(log => log.level === criteria.level);
        }
        
        // Filter by category
        if (criteria.category) {
            filteredLogs = filteredLogs.filter(log => log.category === criteria.category);
        }
        
        // Filter by date range
        if (criteria.from) {
            const fromDate = new Date(criteria.from);
            filteredLogs = filteredLogs.filter(log => new Date(log.timestamp) >= fromDate);
        }
        
        if (criteria.to) {
            const toDate = new Date(criteria.to);
            filteredLogs = filteredLogs.filter(log => new Date(log.timestamp) <= toDate);
        }
        
        // Limit results
        const limit = criteria.limit || 100;
        return filteredLogs.slice(-limit);
    }
    
    /**
     * Get log statistics
     */
    getLogStats() {
        const allLogs = JSON.parse(localStorage.getItem('epos_logs') || '[]');
        const bufferLogs = this.logBuffer;
        
        const stats = {
            total: allLogs.length + bufferLogs.length,
            stored: allLogs.length,
            buffered: bufferLogs.length,
            sessionId: this.sessionId,
            byLevel: {},
            byCategory: {},
            oldestLog: null,
            newestLog: null
        };
        
        // Combine all logs for analysis
        const combinedLogs = [...allLogs, ...bufferLogs];
        
        if (combinedLogs.length > 0) {
            stats.oldestLog = combinedLogs[0].timestamp;
            stats.newestLog = combinedLogs[combinedLogs.length - 1].timestamp;
            
            // Count by level
            combinedLogs.forEach(log => {
                stats.byLevel[log.level] = (stats.byLevel[log.level] || 0) + 1;
                stats.byCategory[log.category] = (stats.byCategory[log.category] || 0) + 1;
            });
        }
        
        return stats;
    }
    
    /**
     * Export logs for analysis
     */
    exportLogs(format = 'json') {
        const logs = JSON.parse(localStorage.getItem('epos_logs') || '[]');
        
        if (format === 'csv') {
            return this.exportLogsAsCSV(logs);
        }
        
        return JSON.stringify(logs, null, 2);
    }
    
    /**
     * Export logs as CSV
     */
    exportLogsAsCSV(logs) {
        if (logs.length === 0) {
            return 'No logs to export';
        }
        
        const headers = ['timestamp', 'level', 'category', 'message', 'sessionId'];
        const csvRows = [headers.join(',')];
        
        logs.forEach(log => {
            const row = [
                log.timestamp,
                log.level,
                log.category,
                `"${log.message.replace(/"/g, '""')}"`, // Escape quotes
                log.sessionId
            ];
            csvRows.push(row.join(','));
        });
        
        return csvRows.join('\n');
    }
    
    /**
     * Clear all logs
     */
    clearLogs() {
        localStorage.removeItem('epos_logs');
        this.logBuffer = [];
        this.info('Logs cleared');
    }
    
    /**
     * Show log viewer dialog
     */
    showLogViewer() {
        const modal = this.createLogViewerModal();
        document.body.appendChild(modal);
        
        // Load logs into viewer
        this.loadLogsIntoViewer(modal);
    }
    
    /**
     * Create log viewer modal
     */
    createLogViewerModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg max-w-6xl w-full h-3/4 flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-medium">Transaction Logs</h3>
                        <div class="flex space-x-2">
                            <button id="export-logs" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Export
                            </button>
                            <button id="clear-logs" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                Clear
                            </button>
                            <button id="close-logs" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4 border-b">
                        <div class="flex space-x-2">
                            <select id="log-level-filter" class="px-2 py-1 border rounded text-sm">
                                <option value="">All Levels</option>
                                <option value="debug">Debug</option>
                                <option value="info">Info</option>
                                <option value="warn">Warning</option>
                                <option value="error">Error</option>
                                <option value="critical">Critical</option>
                            </select>
                            <select id="log-category-filter" class="px-2 py-1 border rounded text-sm">
                                <option value="">All Categories</option>
                                <option value="transaction">Transaction</option>
                                <option value="api">API</option>
                                <option value="rfid">RFID</option>
                                <option value="payment">Payment</option>
                                <option value="customer">Customer</option>
                                <option value="refund">Refund</option>
                                <option value="system">System</option>
                            </select>
                            <button id="refresh-logs" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
                                Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div id="log-content" class="flex-1 p-4 overflow-y-auto bg-gray-50 font-mono text-sm">
                        Loading logs...
                    </div>
                    
                    <div id="log-stats" class="p-4 border-t bg-gray-100 text-sm">
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div>Total: <span id="total-logs">0</span></div>
                            <div>Errors: <span id="error-logs">0</span></div>
                            <div>Session: <span id="session-id">${this.sessionId}</span></div>
                            <div>Buffer: <span id="buffer-logs">${this.logBuffer.length}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Setup event listeners
        this.setupLogViewerEvents(modal);
        
        return modal;
    }
    
    /**
     * Setup log viewer event listeners
     */
    setupLogViewerEvents(modal) {
        const closeBtn = modal.querySelector('#close-logs');
        const exportBtn = modal.querySelector('#export-logs');
        const clearBtn = modal.querySelector('#clear-logs');
        const refreshBtn = modal.querySelector('#refresh-logs');
        const levelFilter = modal.querySelector('#log-level-filter');
        const categoryFilter = modal.querySelector('#log-category-filter');
        
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        exportBtn.addEventListener('click', () => {
            const logs = this.exportLogs('json');
            this.downloadFile('epos-logs.json', logs);
        });
        
        clearBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all logs?')) {
                this.clearLogs();
                this.loadLogsIntoViewer(modal);
            }
        });
        
        refreshBtn.addEventListener('click', () => {
            this.loadLogsIntoViewer(modal);
        });
        
        levelFilter.addEventListener('change', () => {
            this.loadLogsIntoViewer(modal);
        });
        
        categoryFilter.addEventListener('change', () => {
            this.loadLogsIntoViewer(modal);
        });
    }
    
    /**
     * Load logs into viewer
     */
    loadLogsIntoViewer(modal) {
        const levelFilter = modal.querySelector('#log-level-filter').value;
        const categoryFilter = modal.querySelector('#log-category-filter').value;
        const content = modal.querySelector('#log-content');
        
        const criteria = {
            level: levelFilter || undefined,
            category: categoryFilter || undefined,
            limit: 500
        };
        
        const logs = this.getLogs(criteria);
        const stats = this.getLogStats();
        
        // Update stats
        modal.querySelector('#total-logs').textContent = stats.total;
        modal.querySelector('#error-logs').textContent = stats.byLevel.error || 0;
        modal.querySelector('#buffer-logs').textContent = stats.buffered;
        
        // Format logs for display
        const logHtml = logs.map(log => {
            const levelClass = this.getLevelClass(log.level);
            const dataStr = log.data ? JSON.stringify(log.data, null, 2) : '';
            
            return `
                <div class="mb-2 p-2 border rounded ${levelClass}">
                    <div class="flex justify-between items-start">
                        <span class="font-bold">[${log.level.toUpperCase()}] ${log.category}</span>
                        <span class="text-xs text-gray-500">${new Date(log.timestamp).toLocaleString()}</span>
                    </div>
                    <div class="mt-1">${log.message}</div>
                    ${dataStr ? `<pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${dataStr}</pre>` : ''}
                </div>
            `;
        }).join('');
        
        content.innerHTML = logHtml || '<div class="text-center text-gray-500">No logs found</div>';
    }
    
    /**
     * Get CSS class for log level
     */
    getLevelClass(level) {
        switch (level) {
            case 'error':
            case 'critical':
                return 'bg-red-50 border-red-200';
            case 'warn':
                return 'bg-yellow-50 border-yellow-200';
            case 'info':
                return 'bg-blue-50 border-blue-200';
            case 'debug':
                return 'bg-gray-50 border-gray-200';
            default:
                return 'bg-white border-gray-200';
        }
    }
    
    /**
     * Download file utility
     */
    downloadFile(filename, content) {
        const blob = new Blob([content], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Create global instance
window.transactionLogger = new TransactionLogger();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TransactionLogger;
}