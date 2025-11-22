// API Configuration for SIMPels Integration
const API_CONFIG = {
    baseURL: window.SIMPELS_API_URL || 'http://localhost:8001/api/v1/wallets',
    timeout: window.SIMPELS_API_TIMEOUT ? (window.SIMPELS_API_TIMEOUT * 1000) : 30000,
    retries: 3,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': window.SIMPELS_API_KEY ? `Bearer ${window.SIMPELS_API_KEY}` : null
    },
    debug: true // Enable debug mode to see API logs in console
};

// Log configuration on load for debugging
console.log('🔧 SIMPels API Configuration Loaded:', {
    baseURL: API_CONFIG.baseURL,
    timeout: API_CONFIG.timeout,
    hasApiKey: !!API_CONFIG.headers.Authorization,
    debug: API_CONFIG.debug
});

// Available API endpoints (SIMPELS 2.0)
const API_ENDPOINTS = {
    PING: '/ping',
    SANTRI_BY_RFID: '/rfid/uid', // GET /rfid/uid/{uid}
    EPOS_TRANSACTION: '/epos/transaction', // POST
    EPOS_WITHDRAWAL: '/epos/withdrawal', // POST
    EPOS_WITHDRAWAL_STATUS: '/epos/withdrawal', // GET /{number}/status
    // Legacy endpoints (deprecated)
    SANTRI_ALL: '/santri/all',
    SANTRI_BALANCE: '/santri/{id}/saldo',
    SANTRI_DEDUCT: '/santri/{id}/deduct',
    SANTRI_REFUND: '/santri/{id}/refund',
    GURU_ALL: '/guru/all',
    LIMIT_CHECK: '/limit/check-rfid',
    TRANSACTION_SYNC: '/transaction/sync',
    LIMIT_SUMMARY: '/ping' // Use ping instead
};

// Export for use in other modules
window.API_CONFIG = API_CONFIG;
window.API_ENDPOINTS = API_ENDPOINTS;