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

// ✅ Load Customers into Table
function loadCustomerData() {
    fetch("http://localhost/ims/public/api/customers", {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(customers => {
        let customerTable = document.getElementById("customer-table");
        customerTable.innerHTML = "";

        if (customers.length === 0) {
            customerTable.innerHTML = "<tr><td colspan='9'>No customers found.</td></tr>";
            return;
        }

        customers.forEach(customer => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${customer.id}</td>
                <td>${customer.name}</td>
                <td>${customer.email}</td>
                <td>${customer.phone}</td>
                <td>${customer.address}</td>
                <td>${customer.vat_code || "N/A"}</td>
                <td>${customer.total_orders}</td>
                <td>${customer.total_debt}</td>
                <td>
                    <button onclick="openEditCustomerModal(${customer.id}, '${customer.name}', '${customer.email}', '${customer.phone}', '${customer.address}', '${customer.vat_code}')">Edit</button>
                    <button onclick="deleteCustomer(${customer.id})">Delete</button>
                </td>
            `;
            customerTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading customer data:", error));
}

// ✅ Function to Add Customer
function addCustomer() {
    let name = document.getElementById("customer_name").value;
    let email = document.getElementById("customer_email").value;
    let phone = document.getElementById("customer_phone").value;
    let address = document.getElementById("customer_address").value;
    let vat_code = document.getElementById("customer_vat_code").value;

    fetch("http://localhost/ims/public/api/customers", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name, email, phone, address, vat_code })
    })
    .then(response => response.json())
    .then(() => {
        alert("Customer added!");
        document.getElementById("addCustomerForm").style.display = "none";
        loadCustomerData();
    });
}

// ✅ Function to Open Edit Customer Modal
function openEditCustomerModal(id, name, email, phone, address, vat_code) {
    document.getElementById("edit_customer_id").value = id;
    document.getElementById("edit_customer_name").value = name;
    document.getElementById("edit_customer_email").value = email;
    document.getElementById("edit_customer_phone").value = phone;
    document.getElementById("edit_customer_address").value = address;
    document.getElementById("edit_customer_vat_code").value = vat_code;

    document.getElementById("editCustomerForm").style.display = "block";
}

// ✅ Function to Update Customer
function updateCustomer() {
    let id = document.getElementById("edit_customer_id").value;
    let name = document.getElementById("edit_customer_name").value;
    let email = document.getElementById("edit_customer_email").value;
    let phone = document.getElementById("edit_customer_phone").value;
    let address = document.getElementById("edit_customer_address").value;
    let vat_code = document.getElementById("edit_customer_vat_code").value;

    fetch(`http://localhost/ims/public/api/customers/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name, email, phone, address, vat_code })
    })
    .then(() => {
        alert("Customer updated!");
        document.getElementById("editCustomerForm").style.display = "none";
        loadCustomerData();
    });
}
