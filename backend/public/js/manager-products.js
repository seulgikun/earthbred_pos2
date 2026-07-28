const BASE = (function() {
    const pathname = window.location.pathname;
    const idx = pathname.toLowerCase().indexOf('/backend/public');
    return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
})();

const modal = document.getElementById('productModal');
const form = document.getElementById('productForm');
const modalTitle = document.getElementById('modalTitle');

function openAddModal() {
    form.reset();
    document.getElementById('productId').value = '';
    modalTitle.innerText = 'Add Product';
    modal.style.display = 'flex';
}

function openEditModal(product) {
    form.reset();
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productDiscountedPrice').value = product.discounted_price || '';
    
    modalTitle.innerText = 'Edit Product';
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('productId').value;
    
    let url = BASE + '/api/products';
    if (id) {
        url += '/' + id;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error saving product');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the product.');
    });
});

const discountsModal = document.getElementById('discountsModal');
const discountsList = document.getElementById('discountsList');
const addDiscountForm = document.getElementById('addDiscountForm');

function openDiscountsModal() {
    discountsModal.style.display = 'flex';
    fetchDiscounts();
}

function closeDiscountsModal() {
    discountsModal.style.display = 'none';
}

function fetchDiscounts() {
    fetch(BASE + '/api/discounts')
        .then(res => res.json())
        .then(data => {
            discountsList.innerHTML = '';
            data.forEach(d => {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee; align-items:center;';
                div.innerHTML = `
                    <div><strong>${d.name}</strong> (${d.percentage}%)</div>
                    <button onclick="deleteDiscount(${d.id})" style="background:#c5221f; color:#fff; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                `;
                discountsList.appendChild(div);
            });
        });
}

if (addDiscountForm) {
    addDiscountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('discountName').value;
        const percentage = document.getElementById('discountPercent').value;

        fetch(BASE + '/api/discounts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name, percentage })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('discountName').value = '';
                document.getElementById('discountPercent').value = '';
                fetchDiscounts();
            } else {
                alert('Error adding discount');
            }
        });
    });
}

function deleteDiscount(id) {
    if (confirm('Delete this discount?')) {
        fetch(BASE + '/api/discounts/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchDiscounts();
            }
        });
    }
}
