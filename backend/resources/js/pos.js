document.addEventListener('DOMContentLoaded', () => {
    // Menu item active state toggle and product filtering
    const menuItems = document.querySelectorAll('.menu-item');
    const productCards = document.querySelectorAll('.product-card');

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Toggle active class
            menuItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            // Filtering logic
            const filterValue = item.getAttribute('data-filter');
            
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

    // Clock out logic
    const clockOutBtn = document.querySelector('.clock-out');
    if (clockOutBtn) {
        clockOutBtn.addEventListener('click', () => {
            if(confirm("Are you sure you want to clock out?")) {
                window.location.href = '/login'; // redirect back to login
            }
        });
    }

    // Modal Logic
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
            // Extract the number from "₱ 95"
            currentBasePrice = parseInt(priceText.replace(/[^0-9]/g, ''));
            
            modalProductName.innerText = name;
            qtyInput.value = 1;
            customerNameInput.value = ''; // Reset customer name
            
            // Reset addons
            addonCheckboxes.forEach(cb => cb.checked = false);
            
            updateTotalPrice();
            modal.style.display = 'flex';
        });
    });

    // Prevent .add-btn from double triggering or doing something else if it's inside the card
    const addBtns = document.querySelectorAll('.add-btn');
    addBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Let it bubble to the card to open modal, or we can just stop it
            // Actually, we don't need to do anything, clicking it will trigger the card click.
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

    // Add to order action
    if (addToOrderBtn) {
        addToOrderBtn.addEventListener('click', () => {
            // Redirect to checkout page
            window.location.href = '/checkout';
        });
    }

    // Top Right Order Button Redirect
    const mainOrderBtn = document.querySelector('.order-btn');
    if (mainOrderBtn) {
        mainOrderBtn.addEventListener('click', () => {
            window.location.href = '/checkout';
        });
    }
});
