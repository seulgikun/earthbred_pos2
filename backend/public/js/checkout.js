document.addEventListener('DOMContentLoaded', () => {
    
    // =============================================
    // Read Cart from localStorage
    // =============================================
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();

    let cart = JSON.parse(localStorage.getItem('earthbred_cart') || '[]');
    let currentDiscountPercent = 0;

    const orderItemsContainer = document.getElementById('orderItemsContainer');
    const leftTotalDueEl = document.getElementById('leftTotalDue');
    const subtotalAmountEl = document.getElementById('subtotalAmount');
    const discountAmountEl = document.getElementById('discountAmount');
    const rightTotalDueEl = document.getElementById('rightTotalDue');

    // =============================================
    // Render Cart Items
    // =============================================
    function renderCart() {
        orderItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            orderItemsContainer.innerHTML = `
                <div class="empty-cart-msg">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p>Your cart is empty.<br>Go back to add items.</p>
                </div>
            `;
            updateTotals();
            return;
        }

        cart.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'summary-item';
            row.innerHTML = `
                <div class="col-item item-details">
                    <img src="${item.image || ''}" alt="${item.product_name}" class="item-img">
                    <span class="item-name">${item.product_name}</span>
                    <button class="remove-item-btn" data-index="${index}" title="Remove item">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <div class="col-name item-customer-name">${item.customer_name || '—'}</div>
                <div class="col-price item-price">₱ ${item.price}</div>
                <div class="col-qty item-qty">
                    <button class="qty-control-btn minus-btn" data-index="${index}"><i class="fa-solid fa-minus"></i></button>
                    <span class="qty-value">${item.quantity}</span>
                    <button class="qty-control-btn plus-btn" data-index="${index}"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div class="col-total item-total">₱ ${item.item_total}</div>
            `;
            orderItemsContainer.appendChild(row);
        });

        // Attach quantity & remove event listeners
        document.querySelectorAll('.minus-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                if (cart[idx].quantity > 1) {
                    cart[idx].quantity--;
                    recalcItemTotal(idx);
                    saveAndRender();
                }
            });
        });

        document.querySelectorAll('.plus-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                cart[idx].quantity++;
                recalcItemTotal(idx);
                saveAndRender();
            });
        });

        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                cart.splice(idx, 1);
                saveAndRender();
            });
        });

        updateTotals();
    }

    function recalcItemTotal(idx) {
        const item = cart[idx];
        item.item_total = (item.price + (item.addons_total || 0)) * item.quantity;
    }

    function saveAndRender() {
        localStorage.setItem('earthbred_cart', JSON.stringify(cart));
        renderCart();
    }

    // =============================================
    // Update Totals
    // =============================================
    function updateTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.item_total;
        });

        leftTotalDueEl.innerText = `₱ ${subtotal}`;
        subtotalAmountEl.innerText = `₱ ${subtotal}`;

        let discountVal = 0;
        if (currentDiscountPercent > 0) {
            discountVal = Math.floor(subtotal * (currentDiscountPercent / 100));
            discountAmountEl.innerText = `- ₱ ${discountVal}`;
            discountAmountEl.style.color = '#28a745';
        } else {
            discountAmountEl.innerText = `₱ 0`;
            discountAmountEl.style.color = '#1a1a1a';
        }

        let finalTotal = subtotal - discountVal;
        rightTotalDueEl.innerText = `₱ ${finalTotal}`;
    }

    // =============================================
    // Back to Menu
    // =============================================
    const backBtn = document.getElementById('backToMenuBtn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = BASE + '/pos';
        });
    }

    // =============================================
    // Payment Methods Selection
    // =============================================
    const paymentBtns = document.querySelectorAll('.payment-btn');
    paymentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // =============================================
    // Discount Selection
    // =============================================
    const discountBtns = document.querySelectorAll('.discount-btn');
    discountBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.classList.contains('active')) {
                btn.classList.remove('active');
                currentDiscountPercent = 0;
            } else {
                discountBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentDiscountPercent = parseInt(btn.getAttribute('data-discount'));
            }
            updateTotals();
        });
    });

    // =============================================
    // Process Transaction — POST to backend
    // =============================================
    const processBtn = document.getElementById('processBtn');
    const successModal = document.getElementById('successModal');
    const successOkBtn = document.getElementById('successOkBtn');
    const successOrderId = document.getElementById('successOrderId');

    if (processBtn) {
        processBtn.addEventListener('click', async () => {
            if (cart.length === 0) {
                alert('No items in cart!');
                return;
            }

            // Gather data
            let subtotal = 0;
            cart.forEach(item => { subtotal += item.item_total; });
            let discountVal = currentDiscountPercent > 0
                ? Math.floor(subtotal * (currentDiscountPercent / 100))
                : 0;
            let finalTotal = subtotal - discountVal;

            const activePayment = document.querySelector('.payment-btn.active');
            const paymentMethod = activePayment
                ? activePayment.getAttribute('data-method')
                : 'cash';

            const orderData = {
                items: cart.map(item => ({
                    customer_name: item.customer_name || null,
                    product_name: item.product_name,
                    price: item.price,
                    quantity: item.quantity,
                    addons: item.addons || [],
                    addons_total: item.addons_total || 0,
                    item_total: item.item_total
                })),
                subtotal: subtotal,
                discount_percent: currentDiscountPercent,
                discount_amount: discountVal,
                total: finalTotal,
                payment_method: paymentMethod
            };

            // Disable button while processing
            processBtn.disabled = true;
            processBtn.textContent = 'PROCESSING...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch(BASE + '/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    // Clear cart
                    localStorage.removeItem('earthbred_cart');
                    cart = [];

                    // Show success modal
                    if (successOrderId) {
                        successOrderId.textContent = `Order #${result.order_id}`;
                    }
                    successModal.style.display = 'flex';
                } else {
                    alert('Error processing order. Please try again.');
                    processBtn.disabled = false;
                    processBtn.textContent = 'PROCESS TRANSACTION';
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to process order. Check your connection.');
                processBtn.disabled = false;
                processBtn.textContent = 'PROCESS TRANSACTION';
            }
        });
    }

    // Success modal OK button
    if (successOkBtn) {
        successOkBtn.addEventListener('click', () => {
            window.location.href = BASE + '/pos';
        });
    }

    // =============================================
    // Initial Render
    // =============================================
    renderCart();
});
