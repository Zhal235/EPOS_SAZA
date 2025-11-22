// Base API Class for SIMPels Integration
class SIMPelsAPI {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.timeout = API_CONFIG.timeout;
        this.retries = API_CONFIG.retries;
        this.headers = { ...API_CONFIG.headers };
        this.debug = API_CONFIG.debug;
        
        // Remove null/undefined headers
        Object.keys(this.headers).forEach(key => {
            if (this.headers[key] == null) {
                delete this.headers[key];
            }
        });
        
        // Enable production logging for request/response debugging
        this.debug = true;
    }
    
    /**
     * Make API request with error handling and retries
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        // For ping endpoint, don't send Authorization header
        const headers = endpoint === '/ping' 
            ? { 'Accept': 'application/json', 'Content-Type': 'application/json' }
            : this.headers;
        
        const config = {
            timeout: this.timeout,
            headers: headers,
            ...options
        };
        
        // Enhanced request logging with timestamp and request ID
        const requestId = this.generateRequestId();
        const requestTime = Date.now();
        
        if (this.debug) {
            console.group(`🌐 [SIMPels API Request ${requestId}]`);
            console.log(`⏰ Time: ${new Date().toISOString()}`);
            console.log(`🎯 Method: ${config.method || 'GET'}`);
            console.log(`🔗 URL: ${url}`);
            console.log(`📋 Headers:`, this.headers);
            if (config.body) {
                try {
                    console.log(`📦 Body:`, JSON.parse(config.body));
                } catch (e) {
                    console.log(`📦 Body:`, config.body);
                }
            }
            console.groupEnd();
        }
        
        let lastError;
        
        // Retry mechanism
        for (let attempt = 1; attempt <= this.retries; attempt++) {
            try {
                const response = await this.fetchWithTimeout(url, config);
                const data = await response.json();
                
                // Enhanced response logging
                const responseTime = Date.now() - requestTime;
                
                if (this.debug) {
                    console.group(`📥 [SIMPels API Response ${requestId}]`);
                    console.log(`⏱️ Response Time: ${responseTime}ms`);
                    console.log(`📊 Status: ${response.status} ${response.statusText}`);
                    console.log(`📋 Headers:`, Object.fromEntries(response.headers.entries()));
                    console.log(`📦 Data:`, data);
                    console.groupEnd();
                    
                    // Log to performance monitoring
                    this.logPerformance(endpoint, responseTime, response.status);
                }
                
                // Check if response indicates success
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${data.message || 'Unknown error'}`);
                }
                
                return data;
                
            } catch (error) {
                lastError = error;
                
                if (this.debug) {
                    console.warn(`[SIMPels API] Attempt ${attempt} failed:`, error.message);
                }
                
                // Use error handler for intelligent error notification (only on final failure)
                if (window.errorHandler && attempt === this.retries) {
                    const context = {
                        operation: 'api_request',
                        endpoint: endpoint,
                        attempt: attempt,
                        maxRetries: this.retries
                    };
                    window.errorHandler.handleAPIError(error, context);
                }
                
                // Don't retry on certain errors
                if (error.message.includes('404') || error.message.includes('422')) {
                    break;
                }
                
                // Wait before retry (exponential backoff)
                if (attempt < this.retries) {
                    await this.sleep(1000 * attempt);
                }
            }
        }
        
        throw new Error(`API request failed after ${this.retries} attempts: ${lastError.message}`);
    }
    
    /**
     * Fetch with timeout support
     */
    async fetchWithTimeout(url, config) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);
        
        try {
            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });
            return response;
        } finally {
            clearTimeout(timeoutId);
        }
    }
    
    /**
     * Sleep utility for retry delays
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Generate unique request ID for logging
     */
    generateRequestId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
    }
    
    /**
     * Log performance metrics
     */
    logPerformance(endpoint, responseTime, statusCode) {
        const performanceEntry = {
            endpoint: endpoint,
            responseTime: responseTime,
            statusCode: statusCode,
            timestamp: new Date().toISOString(),
            success: statusCode >= 200 && statusCode < 300
        };
        
        // Store in localStorage for performance monitoring
        const perfLog = JSON.parse(localStorage.getItem('simpels_api_performance') || '[]');
        perfLog.unshift(performanceEntry);
        
        // Keep only last 100 entries
        if (perfLog.length > 100) {
            perfLog.splice(100);
        }
        
        localStorage.setItem('simpels_api_performance', JSON.stringify(perfLog));
        
        // Warn about slow requests
        if (responseTime > 5000) {
            console.warn(`⚠️ Slow API response: ${endpoint} took ${responseTime}ms`);
        }
    }
    
    /**
     * Get performance statistics
     */
    getPerformanceStats() {
        const perfLog = JSON.parse(localStorage.getItem('simpels_api_performance') || '[]');
        
        if (perfLog.length === 0) {
            return { message: 'No performance data available' };
        }
        
        const stats = {
            totalRequests: perfLog.length,
            successRate: (perfLog.filter(p => p.success).length / perfLog.length * 100).toFixed(2) + '%',
            averageResponseTime: Math.round(perfLog.reduce((sum, p) => sum + p.responseTime, 0) / perfLog.length) + 'ms',
            slowestRequest: Math.max(...perfLog.map(p => p.responseTime)) + 'ms',
            fastestRequest: Math.min(...perfLog.map(p => p.responseTime)) + 'ms',
            recentErrors: perfLog.filter(p => !p.success).slice(0, 5)
        };
        
        return stats;
    }
    
    /**
     * Test API connection
     */
    async testConnection() {
        try {
            const response = await this.request(API_ENDPOINTS.LIMIT_SUMMARY);
            return {
                success: true,
                message: 'Connection successful',
                data: response
            };
        } catch (error) {
            return {
                success: false,
                message: error.message,
                data: null
            };
        }
    }
    
    /**
     * Get santri data by RFID
     */
    async getSantriByRFID(rfidTag) {
        if (!rfidTag) {
            throw new Error('RFID tag is required');
        }
        
        return await this.request(`${API_ENDPOINTS.SANTRI_BY_RFID}/${rfidTag}`);
    }
    
    /**
     * Check santri balance
     */
    async getSantriBalance(santriId) {
        if (!santriId) {
            throw new Error('Santri ID is required');
        }
        
        return await this.request(`${API_ENDPOINTS.SANTRI_BALANCE.replace('{id}', santriId)}`);
    }
    
    /**
     * Validate transaction limits
     */
    async checkTransactionLimit(rfidTag, amount) {
        if (!rfidTag || !amount) {
            throw new Error('RFID tag and amount are required');
        }
        
        return await this.request(API_ENDPOINTS.LIMIT_CHECK, {
            method: 'POST',
            body: JSON.stringify({
                rfid_tag: rfidTag,
                amount: amount
            })
        });
    }
    
    /**
     * Deduct balance from santri account
     */
    async deductBalance(santriId, amount, description, transactionRef) {
        if (!santriId || !amount || !transactionRef) {
            throw new Error('Santri ID, amount, and transaction reference are required');
        }
        
        return await this.request(`${API_ENDPOINTS.SANTRI_DEDUCT.replace('{id}', santriId)}`, {
            method: 'POST',
            body: JSON.stringify({
                nominal: amount,
                keterangan: description,
                transaction_ref: transactionRef
            })
        });
    }
    
    /**
     * Process refund
     */
    async processRefund(santriId, amount, originalTransactionRef, reason) {
        if (!santriId || !amount || !originalTransactionRef) {
            throw new Error('Santri ID, amount, and original transaction reference are required');
        }
        
        return await this.request(`${API_ENDPOINTS.SANTRI_REFUND.replace('{id}', santriId)}`, {
            method: 'POST',
            body: JSON.stringify({
                nominal: amount,
                original_transaction_ref: originalTransactionRef,
                refund_reason: reason || 'Refund via ePOS'
            })
        });
    }
    
    /**
     * Sync transaction details to SIMPels
     */
    async syncTransaction(transactionData) {
        if (!transactionData.epos_transaction_id || !transactionData.santri_id) {
            throw new Error('Transaction ID and Santri ID are required');
        }
        
        return await this.request(API_ENDPOINTS.TRANSACTION_SYNC, {
            method: 'POST',
            body: JSON.stringify(transactionData)
        });
    }
}

// Create global instance
window.simpelsAPI = new SIMPelsAPI();

// Global debugging helpers
window.SIMPelsDebug = {
    // Get API performance statistics
    getPerformanceStats: () => window.simpelsAPI.getPerformanceStats(),
    
    // Clear performance log
    clearPerformanceLog: () => {
        localStorage.removeItem('simpels_api_performance');
        console.log('SIMPels API performance log cleared');
    },
    
    // Test API connection
    testConnection: async () => {
        const start = performance.now();
        try {
            const result = await window.simpelsAPI.testConnection();
            const time = Math.round(performance.now() - start);
            console.log(`✅ API Connection Test: ${result.success ? 'SUCCESS' : 'FAILED'} (${time}ms)`);
            console.log('Response:', result);
            return result;
        } catch (error) {
            const time = Math.round(performance.now() - start);
            console.error(`❌ API Connection Test: FAILED (${time}ms)`);
            console.error('Error:', error);
            return { success: false, error: error.message };
        }
    },
    
    // Show recent API logs
    showRecentLogs: (limit = 10) => {
        const perfLog = JSON.parse(localStorage.getItem('simpels_api_performance') || '[]');
        console.table(perfLog.slice(0, limit));
    },
    
    // Monitor API in real-time
    startMonitoring: () => {
        console.log('🔍 Starting SIMPels API monitoring... Check console for real-time logs');
        window.simpelsAPI.debug = true;
    },
    
    // Stop API monitoring
    stopMonitoring: () => {
        console.log('⏸️ Stopped SIMPels API monitoring');
        window.simpelsAPI.debug = false;
    }
};

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SIMPelsAPI;
}