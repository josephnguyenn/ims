// Keyboard Shortcuts for POS System
class KeyboardShortcuts {
    constructor() {
        this.shortcuts = {
            'F1': () => this.showHelp(),
            'F2': () => this.focusProductSearch(),
            'F3': () => this.focusCustomerSearch(),
            'F4': () => this.addNewProduct(),
            'F5': () => this.refreshData(),
            'F9': () => this.openPOS(),
            'F10': () => this.processPayment(),
            'F11': () => this.toggleFullscreen(),
            'Escape': () => this.closeModals(),
            'Control+s': () => this.quickSave(),
            'Control+p': () => this.printReceipt(),
            'Control+f': () => this.globalSearch()
        };
        this.init();
    }
    
    init() {
        this.attachListeners();
        this.createHelpModal();
    }
    
    attachListeners() {
        document.addEventListener('keydown', (e) => {
            const key = this.getKeyCombo(e);
            
            if (this.shortcuts[key]) {
                // Don't prevent default for F5 (refresh) and F11 (fullscreen)
                if (key !== 'F5' && key !== 'F11') {
                    e.preventDefault();
                }
                this.shortcuts[key]();
            }
        });
    }
    
    getKeyCombo(e) {
        let combo = [];
        if (e.ctrlKey) combo.push('Control');
        if (e.shiftKey) combo.push('Shift');
        if (e.altKey) combo.push('Alt');
        combo.push(e.key);
        return combo.join('+');
    }
    
    showHelp() {
        const modal = document.getElementById('keyboard-shortcuts-modal');
        if (modal) {
            modal.style.display = 'block';
        }
    }
    
    focusProductSearch() {
        const searchInput = document.getElementById('productSearch') || 
                           document.querySelector('input[placeholder*="Search"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    focusCustomerSearch() {
        const customerInput = document.getElementById('customerSearch') || 
                             document.querySelector('input[placeholder*="Customer"]');
        if (customerInput) {
            customerInput.focus();
            customerInput.select();
        }
    }
    
    addNewProduct() {
        const addBtn = document.querySelector('.btn-add-product') || 
                      document.querySelector('[onclick*="addProduct"]');
        if (addBtn) {
            addBtn.click();
        }
    }
    
    refreshData() {
        // Let F5 work normally, but show notification
        if (window.notificationManager) {
            window.notificationManager.info('Refreshing', 'Page is being refreshed...');
        }
    }
    
    openPOS() {
        window.location.href = 'pos.php';
    }
    
    processPayment() {
        const paymentBtn = document.getElementById('completeOrderBtn') || 
                          document.querySelector('[onclick*="completeOrder"]');
        if (paymentBtn && !paymentBtn.disabled) {
            paymentBtn.click();
        }
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            if (window.notificationManager) {
                window.notificationManager.info('Fullscreen', 'Press F11 again to exit');
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }
    
    closeModals() {
        // Close any open modals
        const modals = document.querySelectorAll('.modal[style*="display: block"]');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
        
        // Close product form
        const productForm = document.getElementById('productForm');
        if (productForm && productForm.style.display !== 'none') {
            productForm.style.display = 'none';
        }
    }
    
    quickSave(e) {
        e?.preventDefault();
        
        // Check if we're in a form context
        const saveBtn = document.querySelector('.btn-primary[onclick*="save"]') ||
                       document.querySelector('.btn-success[onclick*="save"]');
        if (saveBtn) {
            saveBtn.click();
        }
    }
    
    printReceipt(e) {
        e?.preventDefault();
        window.print();
    }
    
    globalSearch(e) {
        e?.preventDefault();
        this.focusProductSearch();
    }
    
    createHelpModal() {
        if (document.getElementById('keyboard-shortcuts-modal')) return;
        
        const modal = document.createElement('div');
        modal.id = 'keyboard-shortcuts-modal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 600px;">
                <span class="close" onclick="document.getElementById('keyboard-shortcuts-modal').style.display='none'">&times;</span>
                <h2>⌨️ Keyboard Shortcuts</h2>
                <table style="width: 100%; margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Shortcut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><kbd>F1</kbd></td><td>Show this help</td></tr>
                        <tr><td><kbd>F2</kbd></td><td>Focus product search</td></tr>
                        <tr><td><kbd>F3</kbd></td><td>Focus customer search</td></tr>
                        <tr><td><kbd>F4</kbd></td><td>Add new product</td></tr>
                        <tr><td><kbd>F5</kbd></td><td>Refresh page</td></tr>
                        <tr><td><kbd>F9</kbd></td><td>Open POS system</td></tr>
                        <tr><td><kbd>F10</kbd></td><td>Process payment</td></tr>
                        <tr><td><kbd>F11</kbd></td><td>Toggle fullscreen</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Close modals</td></tr>
                        <tr><td><kbd>Ctrl + S</kbd></td><td>Quick save</td></tr>
                        <tr><td><kbd>Ctrl + P</kbd></td><td>Print receipt</td></tr>
                        <tr><td><kbd>Ctrl + F</kbd></td><td>Global search</td></tr>
                        <tr><td><kbd>Ctrl + Shift + D</kbd></td><td>Toggle dark mode</td></tr>
                    </tbody>
                </table>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
}

// Initialize keyboard shortcuts
document.addEventListener('DOMContentLoaded', () => {
    window.keyboardShortcuts = new KeyboardShortcuts();
    
    // Show help notification on first load
    if (!localStorage.getItem('keyboard-shortcuts-shown')) {
        setTimeout(() => {
            if (window.notificationManager) {
                window.notificationManager.info(
                    'Keyboard Shortcuts Available',
                    'Press F1 to see all shortcuts'
                );
            }
            localStorage.setItem('keyboard-shortcuts-shown', 'true');
        }, 2000);
    }
});

// Add kbd styling
const style = document.createElement('style');
style.textContent = `
    kbd {
        background: #f4f4f4;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-shadow: 0 1px 0 rgba(0,0,0,0.2);
        color: #333;
        display: inline-block;
        font-family: monospace;
        font-size: 0.9em;
        padding: 2px 6px;
        white-space: nowrap;
    }
    [data-theme="dark"] kbd {
        background: #333;
        border-color: #555;
        color: #fff;
    }
`;
document.head.appendChild(style);
