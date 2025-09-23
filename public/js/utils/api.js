// Base API Class for SIMPels Integration
class SIMPelsAPI {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.timeout = API_CONFIG.timeout;
        this.retries = API_CONFIG.retries;
        this.headers = API_CONFIG.headers;
        this.debug = API_CONFIG.debug;
    }
    
    /**
     * Make API request with error handling and retries
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            timeout: this.timeout,
            headers: this.headers,
            ...options
        };
        
        // Add request logging
        if (this.debug) {
            console.log(`[SIMPels API] ${config.method || 'GET'} ${url}`, config.body ? JSON.parse(config.body) : null);
        }
        
        let lastError;
        
        // Retry mechanism
        for (let attempt = 1; attempt <= this.retries; attempt++) {
            try {
                const response = await this.fetchWithTimeout(url, config);
                const data = await response.json();
                
                // Log response
                if (this.debug) {
                    console.log(`[SIMPels API] Response:`, data);
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

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SIMPelsAPI;
}