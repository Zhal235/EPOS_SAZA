// EPOS Connection Status Manager - COMPLETELY DISABLED
// This module has been completely removed to prevent constant API pinging
// Connection status is now handled only on-demand during transactions

console.log('Connection Status Manager COMPLETELY DISABLED - no background monitoring');

// Export disabled class that does nothing
window.ConnectionStatusManager = class {
    constructor() {
        // Do nothing - completely disabled
    }
    
    init() { /* completely disabled */ }
    startMonitoring() { /* completely disabled */ }
    checkConnection() { return Promise.resolve({ status: 'disabled' }); }
    forceCheck() { /* completely disabled */ }
    getStatus() { return 'disabled'; }
    isConnected() { return true; } // Always return true to avoid blocking
    stop() { /* completely disabled */ }
    setStatus() { /* completely disabled */ }
    updateLastCheckTime() { /* completely disabled */ }
    handleApiNotification() { /* completely disabled */ }
};

// Create disabled connection status object
window.connectionStatus = new window.ConnectionStatusManager();

console.log('Connection Status Manager loading complete - all monitoring disabled');