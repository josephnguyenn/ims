// PWA Install Manager
class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.isOnline = navigator.onLine;
        this.init();
    }
    
    init() {
        this.checkInstallation();
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.setupOnlineOffline();
        this.createInstallButton();
        this.createOfflineIndicator();
    }
    
    // Check if app is already installed
    checkInstallation() {
        if (window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('[PWA] App is installed');
        }
    }
    
    // Register service worker
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/service-worker.js', {
                    scope: '/'
                });
                
                console.log('[PWA] Service Worker registered:', registration);
                
                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('[PWA] New Service Worker found');
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
                
                // Check for updates every hour
                setInterval(() => {
                    registration.update();
                }, 3600000);
                
            } catch (error) {
                console.error('[PWA] Service Worker registration failed:', error);
            }
        }
    }
    
    // Setup install prompt
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            console.log('[PWA] Install prompt available');
            
            // Show install button
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.style.display = 'block';
            }
            
            // Show install banner after 30 seconds
            setTimeout(() => {
                this.showInstallBanner();
            }, 30000);
        });
        
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.isInstalled = true;
            this.deferredPrompt = null;
            
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.style.display = 'none';
            }
            
            if (window.notificationManager) {
                window.notificationManager.success(
                    'App Installed',
                    'Tappo Market IMS has been installed successfully!'
                );
            }
        });
    }
    
    // Setup online/offline detection
    setupOnlineOffline() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.updateOfflineIndicator();
            
            if (window.notificationManager) {
                window.notificationManager.success(
                    'Back Online',
                    'Internet connection restored'
                );
            }
            
            // Sync offline data
            this.syncOfflineData();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.updateOfflineIndicator();
            
            if (window.notificationManager) {
                window.notificationManager.warning(
                    'Offline Mode',
                    'You are working offline. Data will sync when connection is restored.'
                );
            }
        });
    }
    
    // Create install button
    createInstallButton() {
        if (this.isInstalled) return;
        
        const button = document.createElement('button');
        button.id = 'pwa-install-btn';
        button.className = 'pwa-install-button';
        button.innerHTML = '📱 Install App';
        button.style.display = 'none';
        button.addEventListener('click', () => this.install());
        
        document.body.appendChild(button);
    }
    
    // Create offline indicator
    createOfflineIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'offline-indicator';
        indicator.className = 'offline-indicator';
        indicator.innerHTML = '⚠️ Offline Mode';
        indicator.style.display = this.isOnline ? 'none' : 'flex';
        
        document.body.appendChild(indicator);
    }
    
    // Update offline indicator
    updateOfflineIndicator() {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.style.display = this.isOnline ? 'none' : 'flex';
        }
    }
    
    // Show install banner
    showInstallBanner() {
        if (this.isInstalled || !this.deferredPrompt) return;
        
        const banner = document.createElement('div');
        banner.className = 'pwa-install-banner';
        banner.innerHTML = `
            <div class="pwa-install-content">
                <div class="pwa-install-icon">📱</div>
                <div class="pwa-install-text">
                    <strong>Install Tappo Market</strong>
                    <p>Install our app for faster access and offline support</p>
                </div>
                <div class="pwa-install-actions">
                    <button class="pwa-btn-install" onclick="window.pwaInstaller.install()">Install</button>
                    <button class="pwa-btn-dismiss" onclick="this.parentElement.parentElement.parentElement.remove()">Not Now</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);
        
        // Auto dismiss after 15 seconds
        setTimeout(() => {
            if (banner.parentElement) {
                banner.remove();
            }
        }, 15000);
    }
    
    // Install the PWA
    async install() {
        if (!this.deferredPrompt) {
            console.log('[PWA] Install prompt not available');
            return;
        }
        
        this.deferredPrompt.prompt();
        
        const { outcome } = await this.deferredPrompt.userChoice;
        console.log('[PWA] User choice:', outcome);
        
        if (outcome === 'accepted') {
            console.log('[PWA] User accepted the install prompt');
        } else {
            console.log('[PWA] User dismissed the install prompt');
        }
        
        this.deferredPrompt = null;
    }
    
    // Show update notification
    showUpdateNotification() {
        if (window.notificationManager) {
            window.notificationManager.info(
                'Update Available',
                'A new version is available. Refresh to update.'
            );
        }
        
        // Show update banner
        const banner = document.createElement('div');
        banner.className = 'pwa-update-banner';
        banner.innerHTML = `
            <div class="pwa-update-content">
                <span>🔄 New version available</span>
                <button onclick="window.location.reload()">Update Now</button>
            </div>
        `;
        document.body.appendChild(banner);
    }
    
    // Sync offline data
    async syncOfflineData() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('sync-orders');
                console.log('[PWA] Background sync registered');
            } catch (error) {
                console.error('[PWA] Background sync failed:', error);
            }
        }
    }
    
    // Request notification permission
    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            console.log('[PWA] Notification permission:', permission);
            return permission === 'granted';
        }
        return Notification.permission === 'granted';
    }
}

// Initialize PWA when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pwaInstaller = new PWAInstaller();
    
    // Request notification permission after 5 seconds
    setTimeout(() => {
        window.pwaInstaller.requestNotificationPermission();
    }, 5000);
});

// Add PWA styles
const style = document.createElement('style');
style.textContent = `
    .pwa-install-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 24px;
        background: #1a4ba8;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transition: transform 0.2s;
    }
    
    .pwa-install-button:hover {
        transform: scale(1.05);
        background: #0d3a7a;
    }
    
    .pwa-install-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
        padding: 20px;
        z-index: 10000;
        animation: slideUp 0.3s ease-out;
    }
    
    [data-theme="dark"] .pwa-install-banner {
        background: #2d2d2d;
        color: white;
    }
    
    .pwa-install-content {
        display: flex;
        align-items: center;
        gap: 15px;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .pwa-install-icon {
        font-size: 40px;
    }
    
    .pwa-install-text {
        flex: 1;
    }
    
    .pwa-install-text strong {
        display: block;
        margin-bottom: 5px;
    }
    
    .pwa-install-text p {
        margin: 0;
        font-size: 14px;
        color: #666;
    }
    
    [data-theme="dark"] .pwa-install-text p {
        color: #b0b0b0;
    }
    
    .pwa-install-actions {
        display: flex;
        gap: 10px;
    }
    
    .pwa-btn-install {
        padding: 10px 20px;
        background: #1a4ba8;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .pwa-btn-dismiss {
        padding: 10px 20px;
        background: transparent;
        color: #666;
        border: 1px solid #ccc;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .offline-indicator {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #ffc107;
        color: #000;
        padding: 10px;
        text-align: center;
        font-weight: bold;
        z-index: 10001;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .pwa-update-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #1a4ba8;
        color: white;
        padding: 15px;
        text-align: center;
        z-index: 10002;
        animation: slideDown 0.3s ease-out;
    }
    
    .pwa-update-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
    }
    
    .pwa-update-content button {
        padding: 8px 16px;
        background: white;
        color: #1a4ba8;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    @media (max-width: 768px) {
        .pwa-install-button {
            bottom: 10px;
            right: 10px;
            padding: 10px 20px;
            font-size: 14px;
        }
        
        .pwa-install-content {
            flex-direction: column;
            text-align: center;
        }
        
        .pwa-install-actions {
            width: 100%;
            flex-direction: column;
        }
        
        .pwa-btn-install,
        .pwa-btn-dismiss {
            width: 100%;
        }
    }
`;
document.head.appendChild(style);
