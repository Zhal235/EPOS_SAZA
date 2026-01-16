// EPOS Connection Status Manager - DISABLED
// This module has been disabled to prevent constant API pinging
// Connection status is now handled on-demand during transactions only

console.log('Connection Status Manager DISABLED - no background checking');

// Export empty objects to prevent errors if other code references this
window.ConnectionStatusManager = class {
    constructor() {
        console.warn('ConnectionStatusManager is disabled');
    }
    
    init() { /* disabled */ }
    startMonitoring() { /* disabled */ }
    checkConnection() { return Promise.resolve(); }
    forceCheck() { /* disabled */ }
    getStatus() { return 'disabled'; }
    isConnected() { return true; } // Always return true to avoid blocking
    stop() { /* disabled */ }
};

window.connectionStatus = new window.ConnectionStatusManager();

// Prevent initialization
if (false) { // Never execute the old code
    
    init() {
        // Get DOM elements
        this.indicator = document.getElementById('connection-indicator');
        this.statusText = document.getElementById('connection-text');
        this.statusContainer = document.getElementById('api-connection-status');
        this.detailsElement = document.getElementById('connection-details');
        this.lastCheckElement = document.getElementById('last-check-time');
        
        if (!this.indicator || !this.statusText) {
            console.warn('Connection status elements not found');
            return;
        }
        
        // Start monitoring
        this.startMonitoring();
        console.log('Connection Status Manager initialized');
    }
    
    startMonitoring() {
        // Initial check
        this.checkConnection();
        
        // Check every 2 minutes (reduced frequency to save resources)
        this.checkInterval = setInterval(() => {
            this.checkConnection();
        }, 120000);
        
        // Also check when page becomes visible again
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkConnection();
            }
        });
    }
    
    async checkConnection() {
        if (this.isChecking) return;
        
        this.isChecking = true;
        // Reduced logging for better performance
        this.setStatus('checking', 'Checking...', 'Testing connection to SIMPels API...', false);
        
        try {
            // Use the ping endpoint for connection test (no auth required)
            const response = await fetch(`${API_CONFIG.baseURL}/ping`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                timeout: 10000
            });
            
            if (response.ok) {
                const data = await response.json();
                this.setStatus('connected', 'Connected', `Connected to SIMPels API successfully. Response time: ${data.response_time || 'N/A'}ms`);
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
        } catch (error) {
            console.warn('API Connection failed:', error);
            this.setStatus('disconnected', 'Disconnected', `Failed to connect to SIMPels API: ${error.message}`);
        } finally {
            this.isChecking = false;
            this.updateLastCheckTime();
        }
    }
    
    setStatus(status, text, details) {
        if (!this.indicator || !this.statusText) return;
        
        // Remove all status classes
        this.indicator.classList.remove('bg-green-500', 'bg-red-500', 'bg-gray-400', 'animate-pulse');
        this.statusContainer?.classList.remove('bg-green-50', 'bg-red-50', 'bg-gray-50');
        this.statusText.classList.remove('text-green-600', 'text-red-600', 'text-gray-600');
        
        switch (status) {
            case 'connected':
                this.indicator.classList.add('bg-green-500');
                this.statusContainer?.classList.add('bg-green-50');
                this.statusText.classList.add('text-green-600');
                this.statusText.textContent = text;
                break;
                
            case 'disconnected':
                this.indicator.classList.add('bg-red-500');
                this.statusContainer?.classList.add('bg-red-50');
                this.statusText.classList.add('text-red-600');
                this.statusText.textContent = text;
                break;
                
            case 'checking':
                this.indicator.classList.add('bg-gray-400', 'animate-pulse');
                this.statusContainer?.classList.add('bg-gray-50');
                this.statusText.classList.add('text-gray-600');
                this.statusText.textContent = text;
                break;
        }
        
        // Update details
        if (this.detailsElement && details) {
            this.detailsElement.textContent = details;
        }
        
        // Store last status
        this.lastStatus = status;
        
        // Log status change
        console.log(`[ConnectionStatus] ${status.toUpperCase()}: ${text}`);
    }
    
    updateLastCheckTime() {
        if (this.lastCheckElement) {
            this.lastCheckElement.textContent = new Date().toLocaleTimeString();
        }
    }
    
    // Public methods for external use
    forceCheck() {
        this.checkConnection();
    }
    
    getStatus() {
        return this.lastStatus;
    }
    
    isConnected() {
        return this.lastStatus === 'connected';
    }
    
    stop() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }
    
    // Override notification for API connection messages
    handleApiNotification(message) {
        // Don't show popup notifications for API connection status
        // The status indicator will show the current state
        
        if (message.includes('successful')) {
            // Connection restored - already handled by status indicator
            return;
        }
        
        if (message.includes('failed') || message.includes('error')) {
            // Connection failed - already handled by status indicator
            return;
        }
    }
}

// CSS Styles injection
const statusStyles = `
    #api-connection-status {
        transition: all 0.2s ease;
    }
    
    #connection-indicator {
        transition: all 0.3s ease;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
    }
    
    #connection-indicator.bg-green-500 {
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }
    
    #connection-indicator.bg-red-500 {
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }
    
    @keyframes pulse-green {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    @keyframes pulse-red {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .animate-pulse-green {
        animation: pulse-green 2s infinite;
    }
    
    .animate-pulse-red {
        animation: pulse-red 2s infinite;
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = statusStyles;
document.head.appendChild(styleSheet);

// Initialize connection status manager when DOM is ready
let connectionStatusManager;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        connectionStatusManager = new ConnectionStatusManager();
        window.connectionStatus = connectionStatusManager;
    });
} else {
    connectionStatusManager = new ConnectionStatusManager();
    window.connectionStatus = connectionStatusManager;
}

// Export for global use
window.ConnectionStatusManager = ConnectionStatusManager;

console.log('Connection Status Manager loaded');