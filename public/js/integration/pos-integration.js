// Main integration script for SIMPels API with existing ePOS UI
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔴 SIMPels API Integration loaded - PRODUCTION MODE');
    console.log('✅ RFID Payment System: AKTIF');
    console.log('🔗 API Endpoint:', API_CONFIG.baseURL);
    console.log('🔑 API Key configured:', window.SIMPELS_API_KEY ? 'YES' : 'NO');
    console.log('📊 Debug mode:', API_CONFIG.debug ? 'ENABLED' : 'DISABLED');
    console.log('🛠️ Debug helpers available: window.SIMPelsDebug');
    
    // Check if we're on the POS terminal page
    const isPosPage = window.location.pathname === '/pos' || 
                     window.location.pathname.includes('/pos') ||
                     document.querySelector('[wire\\:click*="processPayment"]') !== null ||
                     document.querySelector('.pos-terminal') !== null;
    
    if (!isPosPage) {
        console.log('📄 Not on POS page, skipping POS-specific integration');
        // Still initialize basic API integration for global use
        initializeAPIIntegration();
        return;
    }
    
    console.log('🏪 POS Terminal page detected - initializing full integration');
    
    // Initialize API integration
    initializeAPIIntegration();
    
    // Setup event listeners (only on POS page)
    setupEventListeners();
    
    // Test API connection
    testAPIConnection();
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
    // Double-check we're on the right page before setting up POS-specific listeners
    const hasPosElements = document.querySelector('[wire\\:click*="processPayment"]') || 
                          document.querySelector('[wire\\:click*="addToCart"]') ||
                          document.querySelector('.cart-item') ||
                          document.title.toLowerCase().includes('pos');
    
    if (!hasPosElements) {
        console.log('⚠️ POS elements not found, skipping POS event listeners setup');
        // Only setup global keyboard shortcuts
        setupKeyboardShortcuts();
        return;
    }
    
    console.log('🔧 Setting up POS event listeners...');
    
    // Override existing checkout process for RFID payments
    setupCheckoutOverride();
    
    // Setup payment method change handler
    setupPaymentMethodHandler();
    
    // Setup cart change monitoring
    setupCartMonitoring();
    
    // Setup keyboard shortcuts
    setupKeyboardShortcuts();
    
    console.log('✅ POS event listeners setup complete');
}

/**
 * Override checkout process to support RFID payments
 */
function setupCheckoutOverride() {
    // Try multiple selectors to find the process payment button
    let checkoutBtn = null;
    
    // Try different selectors
    const selectors = [
        'button[wire\\:click="processPayment"]',
        'button[wire\\:click*="processPayment"]',
        'button:contains("Process Payment")',
        'button:has(.fa-credit-card)',
        'button[class*="bg-gradient-to-r"][class*="green"]'
    ];
    
    for (const selector of selectors) {
        try {
            if (selector.includes('contains') || selector.includes('has')) {
                // Use jQuery-style selector logic manually
                const buttons = document.querySelectorAll('button');
                for (const btn of buttons) {
                    if (btn.textContent.includes('Process Payment') || 
                        btn.querySelector('.fa-credit-card') ||
                        (btn.className.includes('bg-gradient-to-r') && btn.className.includes('green'))) {
                        checkoutBtn = btn;
                        break;
                    }
                }
            } else {
                checkoutBtn = document.querySelector(selector);
            }
            
            if (checkoutBtn) {
                console.log(`✅ Process Payment button found using selector: ${selector}`);
                break;
            }
        } catch (e) {
            console.warn(`Failed to use selector: ${selector}`, e);
        }
    }
    
    if (!checkoutBtn) {
        const retryCount = (setupCheckoutOverride.retryCount || 0) + 1;
        
        // Check if we're even on a POS-related page
        const isPosRelated = document.querySelector('[wire\\:click*="addToCart"]') ||
                           document.querySelector('.payment-method') ||
                           document.querySelector('.cart') ||
                           document.title.toLowerCase().includes('pos') ||
                           window.location.pathname.includes('pos');
        
        if (!isPosRelated) {
            console.log('📄 Not on POS page - stopping Process Payment button search');
            return;
        }
        
        if (retryCount < 5) {  // Reduced retry count
            console.warn(`Process Payment button not found (${retryCount}/5), retrying in 1 second...`);
            setupCheckoutOverride.retryCount = retryCount;
            setTimeout(setupCheckoutOverride, 1000);
        } else {
            console.log('⚠️ Process Payment button not found after 5 retries. This is normal on non-POS pages.');
        }
        return;
    }
    
    // Reset retry counter on success
    setupCheckoutOverride.retryCount = 0;
    
    console.log('✅ Process Payment button found, RFID integration ready');
    
    // Don't override the button click - instead monitor payment method changes
    // The existing Livewire system should handle RFID payments properly
    
    // Just add visual indication when RFID mode is active
    setupRfidModeIndicator(checkoutBtn);
}

/**
 * Add visual indicator for RFID mode
 */
function setupRfidModeIndicator(checkoutBtn) {
    // Monitor payment method changes
    const observer = new MutationObserver(() => {
        // Check if RFID payment method is selected by looking for RFID modal or indicators
        const rfidModal = document.querySelector('[wire\\:click="closeRfidModal"]');
        const rfidSelected = document.querySelector('.bg-indigo-600.text-white .fa-wifi'); // RFID button selected state
        
        if (rfidModal || rfidSelected) {
            // Add RFID indicator to checkout button
            if (!checkoutBtn.querySelector('.rfid-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'rfid-indicator';
                indicator.innerHTML = ' 📱';
                indicator.title = 'RFID Payment Mode Active';
                checkoutBtn.appendChild(indicator);
            }
        } else {
            // Remove RFID indicator
            const indicator = checkoutBtn.querySelector('.rfid-indicator');
            if (indicator) {
                indicator.remove();
            }
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class']
    });
}

/**
 * Handle RFID checkout process - Integrated with Livewire
 * Note: This function is now mainly for debugging/manual triggering
 * The main RFID flow is handled by Livewire processPayment method
 */
async function handleRFIDCheckout() {
    try {
        console.log('🔴 Manual RFID checkout triggered');
        
        // Get Livewire component instance
        const componentId = getComponentId();
        if (!componentId) {
            console.error('❌ Livewire component not found');
            if (window.notificationSystem) {
                window.notificationSystem.error('❌ System Error', 'Livewire component tidak ditemukan');
            }
            return false;
        }
        
        const livewireComponent = window.Livewire.find(componentId);
        
        // Get cart from Livewire component
        const cart = livewireComponent.get('cart');
        if (!cart || cart.length === 0) {
            console.warn('⚠️ Cart is empty');
            if (window.notificationSystem) {
                window.notificationSystem.warning('⚠️ Empty Cart', 'Keranjang belanja kosong! Tambahkan produk terlebih dahulu.');
            }
            return false;
        }
        
        // Check if RFID payment method is selected
        const paymentMethod = livewireComponent.get('paymentMethod');
        if (paymentMethod !== 'rfid') {
            console.log('📝 Setting payment method to RFID...');
            livewireComponent.set('paymentMethod', 'rfid');
            
            // Give Livewire time to update
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        console.log('🚀 Processing RFID payment via Livewire...');
        
        // Let Livewire handle the RFID payment process
        // This will open the RFID modal automatically
        const result = await livewireComponent.call('processPayment');
        
        console.log('✅ RFID payment process completed:', result);
        return result;
        
    } catch (error) {
        console.error('❌ RFID Checkout Error:', error);
        
        if (window.notificationSystem) {
            window.notificationSystem.error(
                '❌ RFID Error', 
                'Terjadi kesalahan: ' + error.message
            );
        }
        
        return false;
    }
}

/**
 * Setup payment method change handler
 */
function setupPaymentMethodHandler() {
    // The POS system uses Livewire buttons instead of radio inputs
    // Monitor for payment method button clicks via DOM observation
    
    const observer = new MutationObserver(() => {
        // Look for active payment method buttons
        const activePaymentBtn = document.querySelector('.border-indigo-500.bg-indigo-50');
        
        if (activePaymentBtn) {
            // Check if it's the RFID button by looking for wifi icon or text
            const isRfidActive = activePaymentBtn.querySelector('.fa-wifi') || 
                               activePaymentBtn.textContent.includes('RFID');
                               
            if (isRfidActive) {
                enableRFIDMode();
            } else {
                disableRFIDMode();
            }
        }
    });
    
    // Observe payment method area
    const paymentMethodArea = document.querySelector('.grid.grid-cols-2.gap-3');
    if (paymentMethodArea) {
        observer.observe(paymentMethodArea, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    // Also set up click listeners on payment method buttons
    setTimeout(() => {
        const paymentButtons = document.querySelectorAll('button[wire\\:click*="selectPaymentMethod"]');
        
        paymentButtons.forEach(button => {
            button.addEventListener('click', () => {
                setTimeout(() => {
                    // Check if this button becomes active after click
                    if (button.classList.contains('border-indigo-500')) {
                        const isRfid = button.querySelector('.fa-wifi') || 
                                      button.textContent.includes('RFID');
                        
                        if (isRfid) {
                            enableRFIDMode();
                        } else {
                            disableRFIDMode();
                        }
                    }
                }, 100); // Small delay to allow Livewire to update classes
            });
        });
        
        console.log(`✅ Setup payment method handlers for ${paymentButtons.length} buttons`);
    }, 1000);
}

/**
 * Enable RFID payment mode
 */
function enableRFIDMode() {
    console.log('🔴 RFID payment mode ENABLED');
    
    // The RFID system is already integrated into Livewire
    // Just provide visual feedback and enable shortcuts
    
    // Add RFID status indicator to the page
    showRfidStatusIndicator(true);
    
    // Enable RFID keyboard shortcut
    document.addEventListener('keydown', handleRfidKeyboardShortcut);
    
    // Auto-focus RFID input if modal is open
    setTimeout(() => {
        const rfidInput = document.getElementById('rfid-input');
        if (rfidInput && rfidInput.offsetParent !== null) {
            rfidInput.focus();
        }
    }, 100);
}

/**
 * Disable RFID payment mode
 */
function disableRFIDMode() {
    console.log('⚪ RFID payment mode DISABLED');
    
    // Remove RFID status indicator
    showRfidStatusIndicator(false);
    
    // Disable RFID keyboard shortcut
    document.removeEventListener('keydown', handleRfidKeyboardShortcut);
    
    // Clear any RFID-related data if customerScanner exists
    if (window.customerScanner && typeof window.customerScanner.clearCustomer === 'function') {
        window.customerScanner.clearCustomer();
    }
}

/**
 * Show/hide RFID status indicator
 */
function showRfidStatusIndicator(show) {
    let indicator = document.getElementById('rfid-status-indicator');
    
    if (show && !indicator) {
        // Create indicator
        indicator = document.createElement('div');
        indicator.id = 'rfid-status-indicator';
        indicator.className = 'fixed top-4 left-4 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium shadow-lg z-50';
        indicator.innerHTML = '📱 RFID Mode Active';
        document.body.appendChild(indicator);
        
        // Add animation
        setTimeout(() => {
            indicator.style.transform = 'translateX(0)';
        }, 10);
        
    } else if (!show && indicator) {
        // Remove indicator
        indicator.style.transform = 'translateX(-100%)';
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            }
        }, 300);
    }
}

/**
 * Handle RFID keyboard shortcuts
 */
function handleRfidKeyboardShortcut(e) {
    // Ctrl+R for manual RFID input
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        
        // Try to trigger RFID modal if available
        const processPaymentBtn = document.querySelector('button[wire\\:click*="processPayment"]');
        if (processPaymentBtn && !processPaymentBtn.disabled) {
            processPaymentBtn.click();
        } else if (window.customerScanner && typeof window.customerScanner.showManualRfidInput === 'function') {
            window.customerScanner.showManualRfidInput();
        }
    }
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
    // Only setup if not already done
    if (window.posKeyboardShortcutsSetup) {
        return;
    }
    
    document.addEventListener('keydown', function(e) {
        // Ctrl+R for manual RFID input (only on POS pages)
        if (e.ctrlKey && e.key === 'r') {
            const isPosPage = document.querySelector('[wire\\:click*="processPayment"]') ||
                             document.querySelector('[wire\\:click*="addToCart"]');
            
            if (isPosPage) {
                e.preventDefault();
                if (window.customerScanner && window.customerScanner.showManualRfidInput) {
                    window.customerScanner.showManualRfidInput();
                } else {
                    console.log('📱 RFID scanner not available');
                }
            }
        }
        
        // F1 for API connection test (global)
        if (e.key === 'F1') {
            e.preventDefault();
            testAPIConnection();
        }
        
        // F2 for offline queue status (global)
        if (e.key === 'F2') {
            e.preventDefault();
            showOfflineQueueStatus();
        }
    });
    
    window.posKeyboardShortcutsSetup = true;
    console.log('⌨️ Global keyboard shortcuts setup complete');
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
 * Test API connection (DISABLED - no longer performs automatic testing)
 * Connection status is now handled only on-demand during actual transactions
 */
async function testAPIConnection() {
    console.log('ℹ️ API Connection Test DISABLED - connections tested only during actual transactions');
    return { success: true, message: 'Connection testing disabled' };
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
    console.log('⏳ Loading:', message);
    
    // Use the notification system if available
    if (window.notificationSystem) {
        // Show a persistent loading notification
        window.loadingNotification = window.notificationSystem.info(
            '⏳ Processing',
            message,
            { duration: 0, showProgress: false }
        );
    }
}

function hideLoading() {
    console.log('✅ Hide loading');
    
    // Remove loading notification if it exists
    if (window.loadingNotification && window.notificationSystem) {
        window.notificationSystem.remove(window.loadingNotification);
        window.loadingNotification = null;
    }
}

function showNotification(message, type = 'info') {
    console.log(`📢 ${type.toUpperCase()}: ${message}`);
    
    // Use the proper notification system
    if (window.notificationSystem) {
        const title = {
            'error': '❌ Error',
            'success': '✅ Success',
            'warning': '⚠️ Warning',
            'info': 'ℹ️ Info'
        }[type] || 'ℹ️ Notification';
        
        window.notificationSystem[type] || window.notificationSystem.info(title, message, {
            duration: type === 'error' ? 6000 : 4000,
            sound: true
        });
    } else {
        // Fallback to console and alert
        console.log(`Notification system not available, using fallback`);
        if (type === 'error') {
            alert('❌ Error: ' + message);
        } else if (type === 'success') {
            console.log('✅ Success: ' + message);
        }
    }
}

// Export functions for global access
window.handleRFIDCheckout = handleRFIDCheckout;
window.testAPIConnection = testAPIConnection;
window.showOfflineQueueStatus = showOfflineQueueStatus;