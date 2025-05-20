// public/ims-dashboard/js/categories.js
// CRUD logic for product categories in IMS Dashboard

const API = `${BASE_URL}/api/categories`;

document.addEventListener("DOMContentLoaded", () => {
  loadCategories();

  // Add Category form submit
  document.getElementById("category-form").addEventListener("submit", e => {
    e.preventDefault();
    addCategory();
  });

  // Edit Category form submit
  document.getElementById("edit-category-form").addEventListener("submit", e => {
    e.preventDefault();
    updateCategory();
  });
});

// Fetch and display categories
function loadCategories() {
  fetch(API, { headers: authHeader() })
    .then(res => res.json())
    .then(categories => {
      const tbody = document.getElementById("category-table");
      tbody.innerHTML = '';
      categories.forEach(cat => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${cat.id}</td>
          <td>${cat.name}</td>
          <td>${cat.visible_in_pos ? 'Có' : 'Không'}</td>
          <td>
            <button onclick="openEditCategory(${cat.id})">Sửa</button>
            <button onclick="deleteCategory(${cat.id})">Xóa</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error('Error loading categories:', err));
}

// Create new category
function addCategory() {
  const name = document.getElementById('cat_name').value.trim();
  const visible = document.getElementById('cat_visible').checked;

  fetch(API, {
    method: 'POST',
    headers: {
      ...authHeader(),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name, visible_in_pos: visible })
  })
    .then(res => {
      if (!res.ok) throw res;
      return res.json();
    })
    .then(() => {
      closeModal('addCategoryForm');
      loadCategories();
      document.getElementById('category-form').reset();
    })
    .catch(async err => {
      const msg = err.json ? (await err.json()).message : err.statusText;
      alert('Lỗi thêm danh mục: ' + msg);
    });
}

// Open edit modal and populate
function openEditCategory(id) {
  fetch(`${API}/${id}`, { headers: authHeader() })
    .then(res => res.json())
    .then(cat => {
      document.getElementById('edit_cat_id').value = cat.id;
      document.getElementById('edit_cat_name').value = cat.name;
      document.getElementById('edit_cat_visible').checked = cat.visible_in_pos;
      document.getElementById('editCategoryForm').style.display = 'flex';
    })
    .catch(err => console.error('Error fetching category:', err));
}

// Update existing category
function updateCategory() {
  const id = document.getElementById('edit_cat_id').value;
  const name = document.getElementById('edit_cat_name').value.trim();
  const visible = document.getElementById('edit_cat_visible').checked;

  fetch(`${API}/${id}`, {
    method: 'PUT',
    headers: {
      ...authHeader(),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name, visible_in_pos: visible })
  })
    .then(res => {
      if (!res.ok) throw res;
      return res.json();
    })
    .then(() => {
      closeModal('editCategoryForm');
      loadCategories();
    })
    .catch(async err => {
      const msg = err.json ? (await err.json()).message : err.statusText;
      alert('Lỗi cập nhật danh mục: ' + msg);
    });
}

// Delete a category
function deleteCategory(id) {
  if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
  fetch(`${API}/${id}`, {
    method: 'DELETE',
    headers: authHeader()
  })
    .then(res => {
      if (!res.ok) throw res;
      loadCategories();
    })
    .catch(err => console.error('Error deleting category:', err));
}

// Close modal by id
function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Authorization header helper
function authHeader() {
  return {
    'Authorization': 'Bearer ' + sessionStorage.getItem('token'),
    'Accept': 'application/json'
  };
}