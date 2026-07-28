document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    // =============================================
    // Cart Management (localStorage)
    // =============================================
    function getCart() {
        return JSON.parse(localStorage.getItem('earthbred_cart') || '[]');
    }

    function saveCart(cart) {
        localStorage.setItem('earthbred_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        const cart = getCart();
        const badge = document.getElementById('cartBadge');
        if (badge) {
            if (cart.length > 0) {
                badge.textContent = cart.length;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // =============================================
    // Menu item active state toggle and product filtering
    // =============================================
    const menuItems = document.querySelectorAll('.menu-item');
    const productCards = document.querySelectorAll('.product-card');

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Skip inventory — it navigates via onclick in HTML
            if (item.id === 'inventory-menu-item') return;

            menuItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            document.querySelector('.product-grid').style.display = 'grid';
            const mainOrderBtn = document.querySelector('.order-btn');
            if (mainOrderBtn) mainOrderBtn.style.display = 'flex';

            const filterValue = item.getAttribute('data-filter');
            if (!filterValue) return;
            
            productCards.forEach(card => {
                if (filterValue === 'all' || filterValue === 'shift-notes') {
                    card.style.display = 'flex';
                } else {
                    if (card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });

    // =============================================
    // Clock out logic
    // =============================================
    const clockOutBtn = document.querySelector('.clock-out');
    if (clockOutBtn) {
        clockOutBtn.addEventListener('click', () => {
            if(confirm("Are you sure you want to clock out?")) {
                localStorage.removeItem('earthbred_cart');
                window.location.href = BASE + '/login';
            }
        });
    }

    // =============================================
    // Add cart badge to the Order button
    // =============================================
    const mainOrderBtn = document.querySelector('.order-btn');
    if (mainOrderBtn) {
        // Inject badge element
        mainOrderBtn.style.position = 'relative';
        const badge = document.createElement('span');
        badge.id = 'cartBadge';
        badge.style.cssText = `
            display: none;
            position: absolute;
            top: -6px; right: -6px;
            background: #c0392b;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            width: 20px; height: 20px;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
        `;
        mainOrderBtn.appendChild(badge);
        updateCartBadge();

        mainOrderBtn.addEventListener('click', () => {
            const cart = getCart();
            if (cart.length === 0) {
                showToast('Your cart is empty. Add items first!', '#e74c3c');
                return;
            }
            window.location.href = BASE + '/checkout';
        });
    }

    // =============================================
    // Modal Logic
    // =============================================
    const modal = document.getElementById('productModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const modalProductName = document.getElementById('modalProductName');
    const modalTotalPrice = document.getElementById('modalTotalPrice');
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const addToOrderBtn = document.getElementById('addToOrderBtn');
    const customerNameInput = document.getElementById('customerNameInput');
    
    let currentBasePrice = 0;
    let currentProductImage = '';
    
    function updateTotalPrice() {
        let addonsTotal = 0;
        addonCheckboxes.forEach(cb => {
            if (cb.checked) {
                addonsTotal += parseInt(cb.getAttribute('data-price'));
            }
        });
        
        let qty = parseInt(qtyInput.value);
        let finalPrice = (currentBasePrice + addonsTotal) * qty;
        modalTotalPrice.innerText = `₱ ${finalPrice}`;
    }

    // Open Modal when clicking a product card
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const name = card.querySelector('.product-name').innerText;
            const priceText = card.querySelector('.product-price').innerText;
            const imgEl = card.querySelector('.product-image');
            currentBasePrice = parseFloat(card.getAttribute('data-price'));
            currentProductImage = imgEl ? imgEl.getAttribute('src') : '';
            
            modalProductName.innerText = name;
            qtyInput.value = 1;
            customerNameInput.value = '';
            
            // Reset addons
            addonCheckboxes.forEach(cb => cb.checked = false);
            
            updateTotalPrice();
            modal.style.display = 'flex';
        });
    });

    // Prevent .add-btn from double triggering
    const addBtns = document.querySelectorAll('.add-btn');
    addBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Let it bubble to the card to open modal
        });
    });

    // Close Modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
    
    // Close when clicking outside modal content
    if (modal) {
        modal.addEventListener('click', (e) => {
            if(e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Quantity controls
    if (qtyMinus && qtyPlus) {
        qtyMinus.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value);
            if (qty > 1) {
                qtyInput.value = qty - 1;
                updateTotalPrice();
            }
        });

        qtyPlus.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value);
            qtyInput.value = qty + 1;
            updateTotalPrice();
        });
    }

    // Addon changes update price dynamically
    addonCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateTotalPrice);
    });

    // =============================================
    // Add to Cart (instead of redirecting)
    // =============================================
    if (addToOrderBtn) {
        addToOrderBtn.addEventListener('click', () => {
            let addonsTotal = 0;
            let selectedAddons = [];
            addonCheckboxes.forEach(cb => {
                if (cb.checked) {
                    addonsTotal += parseInt(cb.getAttribute('data-price'));
                    selectedAddons.push(cb.value);
                }
            });

            let qty = parseInt(qtyInput.value);
            let itemTotal = (currentBasePrice + addonsTotal) * qty;

            const cartItem = {
                product_name: modalProductName.innerText,
                customer_name: customerNameInput.value.trim() || '',
                price: currentBasePrice,
                quantity: qty,
                addons: selectedAddons,
                addons_total: addonsTotal,
                item_total: itemTotal,
                image: currentProductImage
            };

            const cart = getCart();
            cart.push(cartItem);
            saveCart(cart);

            // Close modal and show confirmation
            modal.style.display = 'none';
            showToast(`${cartItem.product_name} added to cart!`, '#28a745');
        });
    }

    // =============================================
    // Toast Notification
    // =============================================
    function showToast(message, bgColor) {
        // Remove existing toast
        const existing = document.getElementById('posToast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'posToast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background-color: ${bgColor || '#482f25'};
            color: #fff;
            padding: 14px 28px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    // =============================================
    // URL Filter Parameter Handler
    // =============================================
    const urlParams = new URLSearchParams(window.location.search);
    const initialFilter = urlParams.get('filter');
    if (initialFilter) {
        const targetItem = Array.from(menuItems).find(item => item.getAttribute('data-filter') === initialFilter);
        if (targetItem) {
            targetItem.click();
        }
    }

});

