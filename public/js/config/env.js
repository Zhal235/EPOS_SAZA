// Environment configuration
// Auto-detect environment based on hostname
const isProduction = window.location.hostname !== 'localhost' && 
                     window.location.hostname !== '127.0.0.1' &&
                     !window.location.hostname.includes('local');

// Global debug flag - set to false to disable all logging
// CHANGE THIS TO false FOR PRODUCTION OR TO REDUCE CONSOLE SPAM
window.APP_DEBUG = false; // Changed from !isProduction to always false for clean console

// Helper untuk debug logging
window.debugLog = function(...args) {
    if (window.APP_DEBUG) {
        console.log(...args);
    }
};

window.debugInfo = function(...args) {
    if (window.APP_DEBUG) {
        console.info(...args);
    }
};

// Override console methods in production
if (!window.APP_DEBUG) {
    // Store original console methods
    const originalConsole = {
        log: console.log,
        info: console.info,
        warn: console.warn,
        debug: console.debug
    };
    
    // Override console methods to do nothing in production
    console.log = function() {};
    console.info = function() {};
    console.debug = function() {};
    // Keep console.error and console.warn for critical issues
    
    // Provide a way to restore console for debugging if needed
    window.restoreConsole = function() {
        console.log = originalConsole.log;
        console.info = originalConsole.info;
        console.warn = originalConsole.warn;
        console.debug = originalConsole.debug;
        console.log('Console restored for debugging');
    };
}

// Export config
window.ENV_CONFIG = {
    isProduction: isProduction,
    isDevelopment: !isProduction,
    debug: window.APP_DEBUG
};

if (window.APP_DEBUG) {
    console.log('🔧 Environment:', isProduction ? 'Production' : 'Development');
    console.log('🐛 Debug mode:', window.APP_DEBUG ? 'Enabled' : 'Disabled');
}
