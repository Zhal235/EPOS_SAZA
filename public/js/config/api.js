// API Configuration for SIMPels Integration
const API_CONFIG = {
    baseURL: window.SIMPELS_API_URL || 'http://localhost:8000/api/epos',
    timeout: 30000,
    retries: 3,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': window.SIMPELS_API_KEY ? `Bearer ${window.SIMPELS_API_KEY}` : null
    },
    debug: false // Production mode - RFID payment aktif, no fallback to dummy data
};

// Available API endpoints
const API_ENDPOINTS = {
    SANTRI_BY_RFID: '/santri/rfid',
    SANTRI_ALL: '/santri/all',
    SANTRI_BALANCE: '/santri/{id}/saldo',
    SANTRI_DEDUCT: '/santri/{id}/deduct',
    SANTRI_REFUND: '/santri/{id}/refund',
    GURU_ALL: '/guru/all',
    LIMIT_CHECK: '/limit/check-rfid',
    TRANSACTION_SYNC: '/transaction/sync',
    LIMIT_SUMMARY: '/limit/summary'
};

// Export for use in other modules
window.API_CONFIG = API_CONFIG;
window.API_ENDPOINTS = API_ENDPOINTS;