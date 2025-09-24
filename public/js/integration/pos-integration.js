// Main integration script for SIMPels API with existing ePOS UI
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔴 SIMPels API Integration loaded - PRODUCTION MODE');
    console.log('✅ RFID Payment System: AKTIF');
    console.log('🔗 API Endpoint:', API_CONFIG.baseURL);
    
    // Initialize API integration
    initializeAPIIntegration();
    
    // Setup event listeners
    setupEventListeners();
    
    // Only test API connection on POS terminal page
    if (window.location.pathname === '/pos') {
        testAPIConnection();
    }
});

/**
 * Initialize API integration
 */
function initializeAPIIntegration() {
    // Set current user for transaction processor
    window.currentUser = {
        name: document.querySelector('meta[name="user-name"]')?.getAttribute('content') || 'Unknown User',
        id: document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '1'
    };
    
    // Set POS terminal ID
    const terminalId = 'EPOS_SAZA_' + Date.now();
    localStorage.setItem('pos_terminal_id', terminalId);
    
    console.log('API Integration initialized with terminal:', terminalId);
}

/**
 * Setup event listeners for existing UI elements
 */
function setupEventListeners() {
    // Override existing checkout process for RFID payments
    setupCheckoutOverride();
    
    // Setup payment method change handler
    setupPaymentMethodHandler();
    
    // Setup cart change monitoring
    setupCartMonitoring();
    
    // Setup keyboard shortcuts
    setupKeyboardShortcuts();
}

/**
 * Override checkout process to support RFID payments
 */
function setupCheckoutOverride() {
    // Find existing checkout button using Livewire wire:click selector
    const checkoutBtn = document.querySelector('button[wire\\:click="processPayment"]');
    if (!checkoutBtn) {
        console.warn('Process Payment button not found, retrying...');
        // Retry after DOM is fully loaded
        setTimeout(setupCheckoutOverride, 2000);
        return;
    }
    
    console.log('Process Payment button found, setting up RFID integration...');
    
    // Store original click handler
    const originalHandler = checkoutBtn.onclick;
    
    // Override with new handler
    checkoutBtn.onclick = async function(e) {
        e.preventDefault();
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        
        if (paymentMethod === 'rfid') {
            await handleRFIDCheckout();
        } else {
            // Use original handler for non-RFID payments
            if (originalHandler) {
                originalHandler.call(this, e);
            } else {
                // Fallback to Livewire component
                if (window.Livewire) {
                    Livewire.find(getComponentId()).call('processPayment');
                }
            }
        }
    };
}

/**
 * Handle RFID checkout process - Integrated with Livewire
 */
async function handleRFIDCheckout() {
    try {
        // Check if customer is selected via customerScanner
        if (!window.customerScanner || !window.customerScanner.currentCustomer) {
            showNotification('Silakan scan RFID santri terlebih dahulu', 'error');
            return;
        }
        
        // Get Livewire component instance
        const componentId = getComponentId();
        if (!componentId) {
            showNotification('Error: Component tidak ditemukan', 'error');
            return;
        }
        
        const livewireComponent = window.Livewire.find(componentId);
        
        // Get cart from Livewire component
        const cart = livewireComponent.get('cart');
        if (!cart || cart.length === 0) {
            showNotification('Keranjang belanja kosong', 'error');
            return;
        }
        
        // Process RFID payment via Livewire (PRODUCTION MODE)
        showLoading('Memproses pembayaran RFID melalui SIMPels...');
        
        // Set payment method to RFID
        livewireComponent.set('paymentMethod', 'rfid');
        
        // Set customer data in Livewire
        livewireComponent.set('selectedSantri', window.customerScanner.currentCustomer);
        
        // Process payment via Livewire
        const result = await livewireComponent.call('processPayment');
        
        hideLoading();
        
        if (result) {
            showNotification(`✅ Pembayaran RFID berhasil!`, 'success');
        } else {
            showNotification('❌ Pembayaran RFID gagal', 'error');
        }
        
    } catch (error) {
        console.error('RFID Checkout Error:', error);
        hideLoading();
        showNotification('❌ Error pembayaran RFID: ' + error.message, 'error');
    }
}

/**
 * Setup payment method change handler
 */
function setupPaymentMethodHandler() {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'rfid') {
                // Enable RFID mode
                enableRFIDMode();
            } else {
                // Disable RFID mode
                disableRFIDMode();
            }
        });
    });
}

/**
 * Enable RFID payment mode
 */
function enableRFIDMode() {
    // Show RFID input
    const rfidInput = document.getElementById('rfid-input');
    if (rfidInput) {
        rfidInput.style.display = 'block';
        rfidInput.focus();
    }
    
    // Show customer panel
    const customerPanel = document.getElementById('customer-panel');
    if (customerPanel && customerScanner.currentCustomer) {
        customerPanel.style.display = 'block';
    }
    
    console.log('RFID mode enabled');
}

/**
 * Disable RFID payment mode
 */
function disableRFIDMode() {
    // Clear current customer
    customerScanner.clearCustomer();
    
    console.log('RFID mode disabled');
}

/**
 * Setup cart monitoring
 */
function setupCartMonitoring() {
    // Monitor for cart changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.id === 'cart-items' || mutation.target.classList.contains('cart-item')) {
                updateCartDisplay();
            }
        });
    });
    
    const cartElement = document.getElementById('cart-items');
    if (cartElement) {
        observer.observe(cartElement, {
            childList: true,
            subtree: true
        });
    }
}

/**
 * Setup keyboard shortcuts
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+R for manual RFID input
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            customerScanner.showManualRfidInput();
        }
        
        // F1 for API connection test
        if (e.key === 'F1') {
            e.preventDefault();
            testAPIConnection();
        }
        
        // F2 for offline queue status
        if (e.key === 'F2') {
            e.preventDefault();
            showOfflineQueueStatus();
        }
    });
}

/**
 * Get current cart items from existing system
 */
function getCurrentCart() {
    // Try to get from Livewire component first
    if (window.Livewire) {
        const component = Livewire.find(getComponentId());
        if (component && component.get('cart')) {
            return component.get('cart');
        }
    }
    
    // Fallback: parse from DOM
    const cartItems = [];
    const cartElements = document.querySelectorAll('.cart-item');
    
    cartElements.forEach(element => {
        const id = element.dataset.id;
        const name = element.querySelector('.item-name')?.textContent;
        const price = parseFloat(element.dataset.price || '0');
        const quantity = parseInt(element.querySelector('.item-quantity')?.textContent || '1');
        
        if (id && name && price && quantity) {
            cartItems.push({
                id: id,
                name: name,
                price: price,
                quantity: quantity
            });
        }
    });
    
    return cartItems;
}

/**
 * Calculate cart total
 */
function calculateCartTotal(cart) {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

/**
 * Clear cart
 */
function clearCart() {
    // Try Livewire component first
    if (window.Livewire) {
        const component = Livewire.find(getComponentId());
        if (component) {
            component.call('clearCart');
            return;
        }
    }
    
    // Fallback: clear DOM elements
    const cartContainer = document.getElementById('cart-items');
    if (cartContainer) {
        cartContainer.innerHTML = '<div class="text-center py-8 text-gray-500">Keranjang kosong</div>';
    }
    
    updateCartDisplay();
}

/**
 * Update cart display
 */
function updateCartDisplay() {
    const cart = getCurrentCart();
    const total = calculateCartTotal(cart);
    
    // Update total display
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = formatCurrency(total);
    }
    
    // Update checkout button state
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = cart.length === 0;
    }
}

/**
 * Show checkout confirmation
 */
async function showCheckoutConfirmation(customer, cart, total) {
    const message = `
        Konfirmasi Pembayaran:
        
        Santri: ${customer.nama_santri}
        Kelas: ${customer.kelas || 'N/A'}
        Total: ${formatCurrency(total)}
        Saldo: ${formatCurrency(customer.saldo)}
        
        Items:
        ${cart.map(item => `- ${item.name} (${item.quantity}x) = ${formatCurrency(item.price * item.quantity)}`).join('\n')}
        
        Lanjutkan pembayaran?
    `;
    
    return confirm(message);
}

/**
 * Print receipt
 */
function printReceipt(transactionRef, cart, total, customer) {
    // Implementation depends on your receipt printer setup
    console.log('Printing receipt:', {
        transactionRef,
        cart,
        total,
        customer
    });
    
    // You can integrate with your existing receipt printing system here
}

/**
 * Test API connection
 */
async function testAPIConnection() {
    try {
        showLoading('Testing SIMPels API connection...');
        const result = await simpelsAPI.testConnection();
        
        if (result.success) {
            showNotification('SIMPels API connection successful', 'success');
        } else {
            showNotification('SIMPels API connection failed: ' + result.message, 'error');
        }
    } catch (error) {
        showNotification('SIMPels API connection error: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

/**
 * Show offline queue status
 */
function showOfflineQueueStatus() {
    const status = transactionProcessor.getOfflineQueueStatus();
    const message = `
        Offline Queue Status:
        Total: ${status.total}
        Pending: ${status.pending}
        Failed: ${status.failed}
    `;
    
    alert(message);
}

/**
 * Get Livewire component ID
 */
function getComponentId() {
    const element = document.querySelector('[wire\\:id]');
    return element ? element.getAttribute('wire:id') : null;
}

/**
 * Utility functions
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function showLoading(message = 'Loading...') {
    // Implement based on your loading system
    console.log('Loading:', message);
}

function hideLoading() {
    // Implement based on your loading system
    console.log('Hide loading');
}

function showNotification(message, type = 'info') {
    // Implement based on your notification system
    console.log(`${type.toUpperCase()}: ${message}`);
    
    // Fallback to alert for now
    if (type === 'error') {
        alert('Error: ' + message);
    } else if (type === 'success') {
        alert('Success: ' + message);
    }
}

// Export functions for global access
window.handleRFIDCheckout = handleRFIDCheckout;
window.testAPIConnection = testAPIConnection;
window.showOfflineQueueStatus = showOfflineQueueStatus;