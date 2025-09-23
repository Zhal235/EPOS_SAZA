// API Configuration for SIMPels Integration
const API_CONFIG = {
    baseURL: 'http://localhost:8000/api/epos',
    timeout: 30000,
    retries: 3,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    debug: true // Set to false in production
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