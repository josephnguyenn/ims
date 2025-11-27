// Dark Mode Theme Toggle
class ThemeToggle {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }
    
    init() {
        this.applyTheme(this.currentTheme);
        this.createToggleButton();
        this.attachListeners();
    }
    
    createToggleButton() {
        if (document.getElementById('theme-toggle')) return;
        
        const button = document.createElement('button');
        button.id = 'theme-toggle';
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle dark mode');
        button.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        
        // Position in top right of navbar or body
        const navbar = document.querySelector('.navbar') || document.body;
        navbar.appendChild(button);
    }
    
    attachListeners() {
        const button = document.getElementById('theme-toggle');
        if (button) {
            button.addEventListener('click', () => this.toggle());
        }
        
        // Keyboard shortcut: Ctrl + Shift + D
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                this.toggle();
            }
        });
    }
    
    toggle() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        localStorage.setItem('theme', this.currentTheme);
        
        const button = document.getElementById('theme-toggle');
        if (button) {
            button.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        }
        
        // Show notification
        if (window.notificationManager) {
            window.notificationManager.info(
                'Theme Changed',
                `Switched to ${this.currentTheme} mode`
            );
        }
    }
    
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update meta theme-color for mobile browsers
        let metaTheme = document.querySelector('meta[name="theme-color"]');
        if (!metaTheme) {
            metaTheme = document.createElement('meta');
            metaTheme.name = 'theme-color';
            document.head.appendChild(metaTheme);
        }
        metaTheme.content = theme === 'dark' ? '#1a1a1a' : '#1a4ba8';
    }
}

// Initialize theme toggle
document.addEventListener('DOMContentLoaded', () => {
    window.themeToggle = new ThemeToggle();
});

// Auto dark mode based on system preference
if (window.matchMedia && !localStorage.getItem('theme')) {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDark) {
        localStorage.setItem('theme', 'dark');
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}
