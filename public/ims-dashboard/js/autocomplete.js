// Smart Product Search with Autocomplete
class AutocompleteSearch {
    constructor(inputSelector, resultsSelector) {
        this.input = document.querySelector(inputSelector);
        this.resultsContainer = null;
        this.cache = new Map();
        this.debounceTimer = null;
        this.selectedIndex = -1;
        this.init();
    }
    
    init() {
        if (!this.input) return;
        
        this.createResultsContainer();
        this.attachListeners();
    }
    
    createResultsContainer() {
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'autocomplete-results';
        this.input.parentNode.style.position = 'relative';
        this.input.parentNode.appendChild(this.resultsContainer);
    }
    
    attachListeners() {
        this.input.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideResults();
                return;
            }
            
            this.debounceTimer = setTimeout(() => {
                this.search(query);
            }, 300);
        });
        
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.navigate(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.navigate(-1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.selectCurrent();
            } else if (e.key === 'Escape') {
                this.hideResults();
            }
        });
        
        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.resultsContainer.contains(e.target)) {
                this.hideResults();
            }
        });
    }
    
    async search(query) {
        // Check cache first
        if (this.cache.has(query)) {
            this.displayResults(this.cache.get(query), query);
            return;
        }
        
        const token = localStorage.getItem("token");
        if (!token) return;
        
        try {
            const response = await fetch(`${BASE_URL}/api/products?search=${encodeURIComponent(query)}`, {
                headers: { "Authorization": `Bearer ${token}` }
            });
            
            const products = await response.json();
            
            // Cache the results
            this.cache.set(query, products);
            
            // Limit cache size
            if (this.cache.size > 50) {
                const firstKey = this.cache.keys().next().value;
                this.cache.delete(firstKey);
            }
            
            this.displayResults(products, query);
        } catch (error) {
            console.error('Search error:', error);
        }
    }
    
    displayResults(products, query) {
        if (!products || products.length === 0) {
            this.resultsContainer.innerHTML = '<div class="autocomplete-no-results">No products found</div>';
            this.resultsContainer.style.display = 'block';
            return;
        }
        
        this.resultsContainer.innerHTML = products.slice(0, 8).map((product, index) => `
            <div class="autocomplete-item" data-index="${index}" data-id="${product.id}">
                <div class="autocomplete-product">
                    <img src="${product.image || '/placeholder.png'}" alt="${product.name}" class="autocomplete-product-image">
                    <div class="autocomplete-product-info">
                        <div class="autocomplete-product-name">${this.highlightMatch(product.name, query)}</div>
                        <div class="autocomplete-product-meta">
                            ${product.barcode ? `<span>Barcode: ${product.barcode}</span>` : ''}
                            <span class="autocomplete-product-price">${parseFloat(product.selling_price).toLocaleString()} CZK</span>
                        </div>
                        <div class="autocomplete-product-stock ${product.actual_quantity <= 0 ? 'out-of-stock' : product.actual_quantity <= 10 ? 'low-stock' : ''}">
                            Stock: ${product.actual_quantity} units
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.resultsContainer.style.display = 'block';
        this.selectedIndex = -1;
        
        // Attach click handlers
        this.resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', () => {
                const productId = item.dataset.id;
                const product = products.find(p => p.id == productId);
                this.selectProduct(product);
            });
        });
    }
    
    highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    navigate(direction) {
        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;
        
        // Remove previous highlight
        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
            items[this.selectedIndex].classList.remove('active');
        }
        
        // Update index
        this.selectedIndex += direction;
        if (this.selectedIndex < 0) {
            this.selectedIndex = items.length - 1;
        } else if (this.selectedIndex >= items.length) {
            this.selectedIndex = 0;
        }
        
        // Highlight new item
        items[this.selectedIndex].classList.add('active');
        items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
    }
    
    selectCurrent() {
        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');
        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
            items[this.selectedIndex].click();
        }
    }
    
    selectProduct(product) {
        // Dispatch custom event for parent to handle
        const event = new CustomEvent('productSelected', { 
            detail: product,
            bubbles: true 
        });
        this.input.dispatchEvent(event);
        
        // Clear input and hide results
        this.input.value = '';
        this.hideResults();
    }
    
    hideResults() {
        this.resultsContainer.style.display = 'none';
        this.selectedIndex = -1;
    }
}

// Initialize autocomplete for various pages
document.addEventListener('DOMContentLoaded', () => {
    // Dashboard product search
    const dashboardSearch = document.getElementById('productSearch');
    if (dashboardSearch) {
        window.productAutocomplete = new AutocompleteSearch('#productSearch');
    }
    
    // POS product search
    const posSearch = document.querySelector('.pos-search input');
    if (posSearch) {
        window.posAutocomplete = new AutocompleteSearch('.pos-search input');
        
        // Handle product selection in POS
        posSearch.addEventListener('productSelected', (e) => {
            const product = e.detail;
            if (typeof addToCart === 'function') {
                addToCart(product);
            }
        });
    }
});

// Add active state styling
const style = document.createElement('style');
style.textContent = `
    .autocomplete-item.active {
        background: #e3f2fd !important;
    }
    [data-theme="dark"] .autocomplete-item.active {
        background: #1a4ba8 !important;
    }
`;
document.head.appendChild(style);
