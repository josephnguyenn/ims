// Notification Manager for Inventory Alerts
class NotificationManager {
    constructor() {
        this.container = null;
        this.checkInterval = 60000; // Check every minute
        this.init();
    }
    
    init() {
        this.createContainer();
        this.startMonitoring();
    }
    
    createContainer() {
        if (document.getElementById('notification-container')) return;
        
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }
    
    startMonitoring() {
        this.checkInventory();
        setInterval(() => this.checkInventory(), this.checkInterval);
    }
    
    async checkInventory() {
        const token = localStorage.getItem("token");
        if (!token) return;
        
        try {
            const response = await fetch(`${BASE_URL}/api/products`, {
                headers: { "Authorization": `Bearer ${token}` }
            });
            const products = await response.json();
            
            // Low stock alerts (quantity <= 10)
            const lowStock = products.filter(p => p.actual_quantity > 0 && p.actual_quantity <= 10);
            if (lowStock.length > 0) {
                this.showNotification(
                    'warning',
                    'Low Stock Alert',
                    `${lowStock.length} product(s) running low on stock`
                );
            }
            
            // Out of stock alerts
            const outOfStock = products.filter(p => p.actual_quantity === 0);
            if (outOfStock.length > 0) {
                this.showNotification(
                    'error',
                    'Out of Stock',
                    `${outOfStock.length} product(s) are out of stock`
                );
            }
            
            // Expiring products (example - add expiry_date field if needed)
            const expiringSoon = products.filter(p => {
                if (!p.expiry_date) return false;
                const daysUntilExpiry = Math.ceil((new Date(p.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
                return daysUntilExpiry > 0 && daysUntilExpiry <= 7;
            });
            if (expiringSoon.length > 0) {
                this.showNotification(
                    'warning',
                    'Products Expiring Soon',
                    `${expiringSoon.length} product(s) expire within 7 days`
                );
            }
        } catch (error) {
            console.error('Inventory check error:', error);
        }
    }
    
    showNotification(type, title, message, duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const iconMap = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        notification.innerHTML = `
            <div class="notification-icon">${iconMap[type] || 'ℹ'}</div>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        this.container.appendChild(notification);
        
        // Auto dismiss
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, duration);
        
        // Limit to 3 notifications at once
        const notifications = this.container.querySelectorAll('.notification');
        if (notifications.length > 3) {
            notifications[0].remove();
        }
    }
    
    // Public API for showing custom notifications
    success(title, message) {
        this.showNotification('success', title, message);
    }
    
    error(title, message) {
        this.showNotification('error', title, message);
    }
    
    warning(title, message) {
        this.showNotification('warning', title, message);
    }
    
    info(title, message) {
        this.showNotification('info', title, message);
    }
}

// Initialize globally
window.notificationManager = new NotificationManager();

// Hook into existing save functions
if (typeof window.addProduct === 'function') {
    const originalAddProduct = window.addProduct;
    window.addProduct = function(...args) {
        originalAddProduct(...args);
        setTimeout(() => {
            window.notificationManager.success('Product Added', 'Product has been added successfully');
        }, 100);
    };
}

if (typeof window.updateProduct === 'function') {
    const originalUpdateProduct = window.updateProduct;
    window.updateProduct = function(...args) {
        originalUpdateProduct(...args);
        setTimeout(() => {
            window.notificationManager.success('Product Updated', 'Product has been updated successfully');
        }, 100);
    };
}

// Listen for order completion events
document.addEventListener('orderCompleted', (e) => {
    window.notificationManager.success('Order Completed', `Order #${e.detail.orderId} has been placed`);
});
