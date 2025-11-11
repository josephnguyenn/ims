// Performance optimization utilities
class PerformanceHelper {
    constructor() {
        this.cache = new Map();
        this.loadingStates = new Map();
    }

    // Show loading indicator
    showLoading(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Đang tải dữ liệu...</p>
                </div>
            `;
        }
    }

    // Hide loading indicator
    hideLoading(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            const spinner = container.querySelector('.loading-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }

    // Fetch data with caching and loading states
    async fetchWithCache(url, options = {}) {
        const cacheKey = url + JSON.stringify(options);
        const cacheDuration = 5 * 60 * 1000; // 5 minutes

        // Check if we already have cached data
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < cacheDuration) {
                return cached.data;
            }
        }

        // Check if we're already loading this data
        if (this.loadingStates.has(cacheKey)) {
            return this.loadingStates.get(cacheKey);
        }

        // Start loading
        const loadingPromise = fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                ...options.headers
            },
            ...options
        })
        .then(response => response.json())
        .then(data => {
            // Cache the result
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });
            
            // Remove from loading states
            this.loadingStates.delete(cacheKey);
            
            return data;
        })
        .catch(error => {
            // Remove from loading states on error
            this.loadingStates.delete(cacheKey);
            throw error;
        });

        // Store the loading promise
        this.loadingStates.set(cacheKey, loadingPromise);
        
        return loadingPromise;
    }

    // Clear cache for a specific pattern
    clearCache(pattern = null) {
        if (pattern) {
            for (let key of this.cache.keys()) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }

    // Lazy load content when scrolling
    setupLazyLoading(containerId, fetchFunction, itemsPerPage = 50) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let currentPage = 1;
        let loading = false;
        let hasMore = true;

        const loadMore = async () => {
            if (loading || !hasMore) return;

            loading = true;
            try {
                const data = await fetchFunction(currentPage, itemsPerPage);
                
                if (data && data.length > 0) {
                    // Append new items to container
                    data.forEach(item => {
                        const element = this.createItemElement(item);
                        container.appendChild(element);
                    });
                    currentPage++;
                    
                    if (data.length < itemsPerPage) {
                        hasMore = false;
                    }
                } else {
                    hasMore = false;
                }
            } catch (error) {
                console.error('Error loading more items:', error);
            }
            loading = false;
        };

        // Initial load
        loadMore();

        // Load more on scroll
        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
                loadMore();
            }
        });
    }

    // Debounce function for search inputs
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Global instance
window.performanceHelper = new PerformanceHelper();

// Add CSS for loading spinner
const style = document.createElement('style');
style.textContent = `
    .loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-spinner p {
        color: #666;
        font-size: 14px;
    }
`;
document.head.appendChild(style);