document.addEventListener('DOMContentLoaded', function() {
    const productList = document.getElementById('product-list');
    const cartTableBody = document.querySelector('#cart-table tbody');
    const totalCZK = document.getElementById('total-czk');
    const totalEUR = document.getElementById('total-eur');
    const printStatus = document.getElementById('print-status');

    let cart = {};
    let autoPrint = false;
    let EUR_RATE = 25; // Will be loaded dynamically

    // Load exchange rate dynamically
    fetch('api/get_exchange_rate.php')
        .then(res => res.json())
        .then(data => {
            EUR_RATE = parseFloat(data.rate);
            updateCart();
        });

    // Category Switching
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.dataset.category;
            fetch(`api/fetch_products.php?category=${encodeURIComponent(category)}`)
                .then(response => response.text())
                .then(html => {
                    productList.innerHTML = html;
                    attachProductEvents();
                });
        });
    });

    function attachProductEvents() {
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const price = parseFloat(this.dataset.price);

                if (cart[id]) {
                    cart[id].qty += 1;
                } else {
                    cart[id] = { name, price, qty: 1 };
                }
                updateCart();
            });
        });
    }

    attachProductEvents();

    function updateCart() {
        cartTableBody.innerHTML = '';
        let total = 0;

        for (let id in cart) {
            const item = cart[id];
            const row = `<tr>
                <td>${item.name}</td>
                <td>${item.qty}</td>
                <td>${item.price} CZK</td>
                <td>${(item.qty * item.price).toFixed(2)} CZK</td>
                <td><button class="remove-item" data-id="${id}">✖</button></td>
            </tr>`;
            cartTableBody.innerHTML += row;
            total += item.qty * item.price;
        }

        totalCZK.innerText = `${total.toFixed(2)} CZK`;
        totalEUR.innerText = `${(total / EUR_RATE).toFixed(2)} EUR`;

        // Handle Remove Buttons
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                delete cart[id];
                updateCart();
            });
        });
    }

    document.getElementById('page-up').addEventListener('click', function() {
        adjustLastQty(1);
    });

    document.getElementById('page-down').addEventListener('click', function() {
        adjustLastQty(-1);
    });

    // Barcode Handling
    document.getElementById('barcode-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = this.value.trim();
            fetch(`api/fetch_products.php?barcode=${encodeURIComponent(barcode)}`)
                .then(response => response.json())
                .then(product => {
                    if (product) {
                        const id = product.id;
                        if (cart[id]) {
                            cart[id].qty += 1;
                        } else {
                            cart[id] = { name: product.name, price: parseFloat(product.price), qty: 1 };
                        }
                        updateCart();
                        this.value = '';
                    }
                });
        }
    });

    // Numpad Interaction
    document.querySelectorAll('.num-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const number = this.innerText;
            const barcodeInput = document.getElementById('barcode-input');
            barcodeInput.value += number;
        });
    });

    // Auto Print Toggle
    document.getElementById('toggle-print').addEventListener('click', function() {
        autoPrint = !autoPrint;
        printStatus.innerText = autoPrint ? 'ON' : 'OFF';
    });

    // Print Invoice
    document.getElementById('print-invoice').addEventListener('click', printInvoice);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F11') {
            e.preventDefault();
            printInvoice();
        }
    if (e.key === 'PageUp') {
        e.preventDefault();  // Prevent page scroll
        adjustLastQty(1);
    }
    if (e.key === 'PageDown') {
        e.preventDefault();  // Prevent page scroll
        adjustLastQty(-1);
    }
    });

    function printInvoice() {
        if (Object.keys(cart).length === 0) {
            alert('Cart is empty!');
            return;
        }
        console.log("Printing Invoice...", cart);
        if (autoPrint) window.print();
        cart = {};
        updateCart();
    }

    function adjustLastQty(change) {
        const keys = Object.keys(cart);
        if (keys.length === 0) return;
        const lastKey = keys[keys.length - 1];
        cart[lastKey].qty = Math.max(1, cart[lastKey].qty + change);
        updateCart();
    }
});
