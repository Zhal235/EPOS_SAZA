// SIMPels Connection Alert Handler - COMPLETELY DISABLED
// This module has been completely removed to prevent automatic connection alerts
// Connection errors are now handled only when transactions actually fail

console.log('SIMPels Connection Alert Handler COMPLETELY DISABLED - no automatic alerts');

// Export empty class to prevent errors if other code references this
class SimpelsConnectionAlertHandler {
    constructor() {
        // Do nothing - completely disabled
    }
    
    setupEventListeners() { /* disabled */ }
    showConnectionErrorModal() { /* disabled */ }
    retryConnection() { /* disabled */ }
    closeModal() { /* disabled */ }
    logConnectionError() { /* disabled */ }
    getErrorLogs() { return []; }
    clearErrorLogs() { /* disabled */ }
    showQuickAlert() { /* disabled */ }
}

// Create disabled instance
let simpelsConnectionAlert = new SimpelsConnectionAlertHandler();
window.simpelsConnectionAlert = simpelsConnectionAlert;
window.SimpelsConnectionAlertHandler = SimpelsConnectionAlertHandler;

console.log('SIMPels Connection Alert Handler loading complete - all alerts disabled');