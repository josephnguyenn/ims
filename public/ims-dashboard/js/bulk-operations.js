// Bulk Operations Manager for Products
class BulkOperationsManager {
    constructor(tableId = 'productsDataTable') {
        this.tableId = tableId;
        this.selectedIds = new Set();
        this.init();
    }
    
    init() {
        this.addCheckboxColumn();
        this.createBulkActionsBar();
        this.attachEventListeners();
    }
    
    // Add checkbox column to DataTable
    addCheckboxColumn() {
        // This will be called when DataTable initializes
        // See products.php for column definition
    }
    
    // Create bulk actions toolbar
    createBulkActionsBar() {
        const table = document.getElementById(this.tableId);
        if (!table) return;
        
        const toolbar = document.createElement('div');
        toolbar.id = 'bulk-actions-bar';
        toolbar.className = 'bulk-actions-bar';
        toolbar.style.display = 'none';
        toolbar.innerHTML = `
            <div class="bulk-actions-content">
                <span class="bulk-selected-count">0 selected</span>
                <div class="bulk-actions-buttons">
                    <button class="bulk-btn bulk-btn-update" onclick="window.bulkOps.showBulkUpdateModal()">
                        <span>✏️</span> Update Selected
                    </button>
                    <button class="bulk-btn bulk-btn-delete" onclick="window.bulkOps.bulkDelete()">
                        <span>🗑️</span> Delete Selected
                    </button>
                    <button class="bulk-btn bulk-btn-clear" onclick="window.bulkOps.clearSelection()">
                        <span>✕</span> Clear
                    </button>
                </div>
            </div>
        `;
        
        table.parentNode.insertBefore(toolbar, table);
    }
    
    // Attach event listeners
    attachEventListeners() {
        // Select all checkbox
        document.addEventListener('change', (e) => {
            if (e.target.id === 'select-all-products') {
                this.toggleSelectAll(e.target.checked);
            } else if (e.target.classList.contains('product-checkbox')) {
                this.toggleSelection(e.target.value, e.target.checked);
            }
        });
    }
    
    // Toggle select all
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            this.toggleSelection(checkbox.value, checked);
        });
    }
    
    // Toggle individual selection
    toggleSelection(productId, selected) {
        if (selected) {
            this.selectedIds.add(productId);
        } else {
            this.selectedIds.delete(productId);
        }
        
        this.updateUI();
    }
    
    // Update UI based on selection
    updateUI() {
        const count = this.selectedIds.size;
        const toolbar = document.getElementById('bulk-actions-bar');
        const countSpan = toolbar?.querySelector('.bulk-selected-count');
        
        if (toolbar) {
            toolbar.style.display = count > 0 ? 'flex' : 'none';
        }
        
        if (countSpan) {
            countSpan.textContent = `${count} selected`;
        }
        
        // Update select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-products');
        if (selectAllCheckbox) {
            const totalCheckboxes = document.querySelectorAll('.product-checkbox').length;
            selectAllCheckbox.checked = count === totalCheckboxes && count > 0;
            selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes;
        }
    }
    
    // Clear selection
    clearSelection() {
        this.selectedIds.clear();
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        const selectAllCheckbox = document.getElementById('select-all-products');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        this.updateUI();
    }
    
    // Show bulk update modal
    showBulkUpdateModal() {
        if (this.selectedIds.size === 0) {
            alert('Please select products to update');
            return;
        }
        
        const modal = document.getElementById('bulkUpdateModal') || this.createBulkUpdateModal();
        modal.style.display = 'block';
    }
    
    // Create bulk update modal
    createBulkUpdateModal() {
        const modal = document.createElement('div');
        modal.id = 'bulkUpdateModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('bulkUpdateModal').style.display='none'">&times;</span>
                <h2>Bulk Update Products</h2>
                <p class="bulk-info">${this.selectedIds.size} product(s) selected</p>
                
                <form id="bulkUpdateForm">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="update-price-check"> Update Price
                        </label>
                        <input type="number" id="update-price" step="0.01" placeholder="New price" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="update-cost-check"> Update Cost
                        </label>
                        <input type="number" id="update-cost" step="0.01" placeholder="New cost" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="update-tax-check"> Update Tax
                        </label>
                        <input type="number" id="update-tax" step="0.01" placeholder="New tax %" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="update-category-check"> Update Category
                        </label>
                        <select id="update-category" disabled>
                            <option value="">Select category</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="window.bulkOps.executeBulkUpdate()">
                            Update Products
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('bulkUpdateModal').style.display='none'">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Enable/disable inputs based on checkboxes
        ['price', 'cost', 'tax', 'category'].forEach(field => {
            const checkbox = modal.querySelector(`#update-${field}-check`);
            const input = modal.querySelector(`#update-${field}`);
            checkbox.addEventListener('change', () => {
                input.disabled = !checkbox.checked;
            });
        });
        
        // Load categories
        this.loadCategories();
        
        return modal;
    }
    
    // Load categories for dropdown
    async loadCategories() {
        try {
            const response = await fetch(`${BASE_URL}/api/categories`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            const categories = await response.json();
            
            const select = document.getElementById('update-category');
            if (select && categories) {
                select.innerHTML = '<option value="">Select category</option>' + 
                    categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }
    
    // Execute bulk update
    async executeBulkUpdate() {
        const updates = {};
        
        // Collect updates
        if (document.getElementById('update-price-check').checked) {
            updates.price = document.getElementById('update-price').value;
        }
        if (document.getElementById('update-cost-check').checked) {
            updates.cost = document.getElementById('update-cost').value;
        }
        if (document.getElementById('update-tax-check').checked) {
            updates.tax = document.getElementById('update-tax').value;
        }
        if (document.getElementById('update-category-check').checked) {
            updates.category_id = document.getElementById('update-category').value;
        }
        
        if (Object.keys(updates).length === 0) {
            alert('Please select at least one field to update');
            return;
        }
        
        if (!confirm(`Update ${this.selectedIds.size} products with these values?`)) {
            return;
        }
        
        try {
            const response = await fetch(`${BASE_URL}/api/products/bulk/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({
                    product_ids: Array.from(this.selectedIds),
                    updates: updates
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert(data.message);
                document.getElementById('bulkUpdateModal').style.display = 'none';
                
                // Reload table
                if (typeof window.reloadProductsTable === 'function') {
                    window.reloadProductsTable();
                } else {
                    window.location.reload();
                }
                
                this.clearSelection();
            } else {
                alert('Error: ' + (data.message || 'Bulk update failed'));
            }
        } catch (error) {
            console.error('Bulk update error:', error);
            alert('Failed to update products: ' + error.message);
        }
    }
    
    // Bulk delete
    async bulkDelete() {
        if (this.selectedIds.size === 0) {
            alert('Please select products to delete');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${this.selectedIds.size} products? This action cannot be undone.`)) {
            return;
        }
        
        try {
            const response = await fetch(`${BASE_URL}/api/products/bulk/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({
                    product_ids: Array.from(this.selectedIds)
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert(data.message);
                
                // Reload table
                if (typeof window.reloadProductsTable === 'function') {
                    window.reloadProductsTable();
                } else {
                    window.location.reload();
                }
                
                this.clearSelection();
            } else {
                alert('Error: ' + (data.message || 'Bulk delete failed'));
            }
        } catch (error) {
            console.error('Bulk delete error:', error);
            alert('Failed to delete products: ' + error.message);
        }
    }
}

// Styles for bulk operations
const bulkStyles = document.createElement('style');
bulkStyles.textContent = `
    .bulk-actions-bar {
        display: none;
        justify-content: space-between;
        align-items: center;
        background: #e3f2fd;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    [data-theme="dark"] .bulk-actions-bar {
        background: #1a4ba8;
        color: white;
    }
    
    .bulk-actions-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    
    .bulk-selected-count {
        font-weight: bold;
        font-size: 16px;
        color: #1a4ba8;
    }
    
    [data-theme="dark"] .bulk-selected-count {
        color: white;
    }
    
    .bulk-actions-buttons {
        display: flex;
        gap: 10px;
    }
    
    .bulk-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    
    .bulk-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .bulk-btn-update {
        background: #1a4ba8;
        color: white;
    }
    
    .bulk-btn-update:hover {
        background: #0d3a7a;
    }
    
    .bulk-btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .bulk-btn-delete:hover {
        background: #c82333;
    }
    
    .bulk-btn-clear {
        background: #6c757d;
        color: white;
    }
    
    .bulk-btn-clear:hover {
        background: #545b62;
    }
    
    .bulk-info {
        background: #fff3cd;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
        color: #856404;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-group input[type="number"],
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-top: 5px;
    }
    
    .form-group input:disabled,
    .form-group select:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .form-actions button {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .bulk-actions-content {
            flex-direction: column;
            gap: 15px;
        }
        
        .bulk-actions-buttons {
            width: 100%;
            flex-direction: column;
        }
        
        .bulk-btn {
            width: 100%;
            justify-content: center;
        }
    }
`;
document.head.appendChild(bulkStyles);

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    window.bulkOps = new BulkOperationsManager();
});
