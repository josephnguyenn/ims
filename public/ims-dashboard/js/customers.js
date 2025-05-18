document.addEventListener("DOMContentLoaded", function () {
    loadCustomerData();

    const customerForm = document.getElementById("customer-form");
    if (customerForm) {
        customerForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addCustomer();
        });
    }
});

// ✅ Load Customers
function loadCustomerData() {
    fetch(`${BASE_URL}/api/customers`, {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(customers => {
        const table = document.getElementById("customer-table");
        table.innerHTML = "";

        if (customers.length === 0) {
            table.innerHTML = "<tr><td colspan='9'>No customers found.</td></tr>";
            return;
        }

        customers.forEach(customer => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${customer.id}</td>
                <td>${customer.name}</td>
                <td>${customer.email}</td>
                <td>${customer.phone}</td>
                <td>${customer.address}</td>
                <td>${customer.vat_code || "N/A"}</td>
                <td>${customer.total_orders}</td>
                <td>${customer.total_debt}Kč</td>
                <td>
                <button onclick="openEditCustomerModal(
                    ${customer.id}, 
                    '${customer.name}', 
                    '${customer.email}', 
                    '${customer.phone}', 
                    '${customer.address}', 
                    '${customer.vat_code ?? ''}', 
                    '${customer.city ?? ''}', 
                    '${customer.postal_code ?? ''}', 
                    '${customer.tax_code ?? ''}'
                )">Sửa</button>                    
                <button onclick="deleteCustomer(${customer.id})">Xóa</button>
                </td>
            `;
            table.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading customers:", error));
}

// ✅ Add Customer
function addCustomer() {
    const name = document.getElementById("customer_name").value;
    const email = document.getElementById("customer_email").value;
    const phone = document.getElementById("customer_phone").value;
    const city = document.getElementById("customer_city").value;
    const postal_code = document.getElementById("customer_postal_code").value;
    const address = document.getElementById("customer_address").value;
    const vat_code = document.getElementById("customer_vat_code").value;
    const tax_code = document.getElementById("customer_tax_code").value;

    fetch(`${BASE_URL}/api/customers`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json", // <-- ✅ Fixes redirect to HTML
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
        },
        body: JSON.stringify({ name, email, phone, address, vat_code, city, postal_code, tax_code })
    })
    .then(async response => {
        const text = await response.text();
    
        if (!response.ok) {
            console.error("❌ Server Error:", text); // Log actual HTML error
            throw new Error("Server returned error: " + text);
        }
    
        try {
            return JSON.parse(text); // safer than response.json()
        } catch (err) {
            throw new Error("❌ Failed to parse JSON: " + text);
        }
    })
    .then(() => {
        alert("Customer added!");
        closeModal("addCustomerForm");
        loadCustomerData();
        window.location.reload(); // Refresh the page
    })
    .catch(error => console.error("❌ Failed to add customer:", error));
}    

// ✅ Open Edit Modal
function openEditCustomerModal(id, name, email, phone, address, vat_code, city, postal_code, tax_code) {
    document.getElementById("edit_customer_id").value = id;
    document.getElementById("edit_customer_name").value = name;
    document.getElementById("edit_customer_email").value = email;
    document.getElementById("edit_customer_phone").value = phone;
    document.getElementById("edit_customer_address").value = address;
    document.getElementById("edit_customer_vat_code").value = vat_code;
    document.getElementById("edit_customer_tax_code").value = tax_code;
    document.getElementById("edit_customer_city").value = city;
    document.getElementById("edit_customer_postal_code").value = postal_code;
    openModal("editCustomerForm");
}

// ✅ Update Customer
function updateCustomer() {
    const id = document.getElementById("edit_customer_id").value;
    const name = document.getElementById("edit_customer_name").value;
    const email = document.getElementById("edit_customer_email").value;
    const phone = document.getElementById("edit_customer_phone").value;
    const address = document.getElementById("edit_customer_address").value;
    const tax_code = document.getElementById("edit_customer_tax_code").value;
    const city = document.getElementById("edit_customer_city").value;
    const postal_code = document.getElementById("edit_customer_postal_code").value;
    const vat_code = document.getElementById("edit_customer_vat_code").value;

    fetch(`${BASE_URL}/api/customers/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name, email, phone, address, vat_code, city, postal_code, tax_code })
    })
    .then(() => {
        alert("Customer updated!");
        closeModal("editCustomerForm");
        loadCustomerData();
    });
}

// ✅ Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "flex"; // To match your existing flexbox layout
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "none";
}

function deleteCustomer(id) {
    if (!confirm("Bạn có chắc muốn xóa khách hàng này không?")) return;

    fetch(`${BASE_URL}/api/customers/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Customer deleted successfully") {
            alert("Xóa khách hàng thành công!");
            loadCustomerData();
        } else {
            alert("Không thể xóa khách hàng: " + (data.message || "Lỗi không xác định"));
        }
    })
    .catch(error => console.error("❌ Lỗi khi xóa khách hàng:", error));
}