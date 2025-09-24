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
    // Find existing checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (!checkoutBtn) {
        console.warn('Checkout button not found');
        return;
    }
    
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
                    Livewire.find(getComponentId()).call('processCheckout');
                }
            }
        }
    };
}

/**
 * Handle RFID checkout process
 */
async function handleRFIDCheckout() {
    try {
        // Check if customer is selected
        if (!customerScanner.currentCustomer) {
            showNotification('Silakan scan RFID santri terlebih dahulu', 'error');
            return;
        }
        
        // Get cart items from existing system
        const cart = getCurrentCart();
        if (!cart || cart.length === 0) {
            showNotification('Keranjang belanja kosong', 'error');
            return;
        }
        
        // Calculate total
        const total = calculateCartTotal(cart);
        
        // Show confirmation
        const confirmed = await showCheckoutConfirmation(customerScanner.currentCustomer, cart, total);
        if (!confirmed) {
            return;
        }
        
        // Process RFID payment (PRODUCTION MODE)
        showLoading('Memproses pembayaran RFID melalui SIMPels...');
        
        const result = await transactionProcessor.processPayment(
            customerScanner.currentCustomer,
            cart,
            total,
            'rfid'
        );
        
        if (result.success) {
            showNotification(`✅ Pembayaran RFID berhasil! Saldo tersisa: ${formatCurrency(result.newBalance)}`, 'success');
            
            // Log successful transaction
            transactionLogger.log({
                level: 'info',
                category: 'payment',
                message: `RFID Payment Success - ${customerScanner.currentCustomer.nama_santri}`,
                data: {
                    customer: customerScanner.currentCustomer.nama_santri,
                    total: total,
                    newBalance: result.newBalance,
                    transactionRef: result.transactionRef
                }
            });
            
            // Clear cart and reset UI
            clearCart();
            customerScanner.clearCustomer();
            
            // Print receipt if needed
            printReceipt(result.transactionRef, cart, total, customerScanner.currentCustomer);
            
            // Refresh page or update UI as needed
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('❌ Pembayaran RFID gagal: ' + result.message, 'error');
            
            // Log failed transaction
            transactionLogger.log({
                level: 'error',
                category: 'payment',
                message: `RFID Payment Failed - ${customerScanner.currentCustomer?.nama_santri || 'Unknown'}`,
                data: {
                    error: result.message,
                    customer: customerScanner.currentCustomer?.nama_santri,
                    total: total
                }
            });
        }
        
    } catch (error) {
        console.error('RFID Checkout Error:', error);
        showNotification('❌ Error pembayaran RFID: ' + error.message, 'error');
        
        // Log critical error
        transactionLogger.log({
            level: 'error',
            category: 'payment',
            message: 'RFID Payment Critical Error',
            data: {
                error: error.message,
                stack: error.stack
            }
        });
    } finally {
        hideLoading();
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