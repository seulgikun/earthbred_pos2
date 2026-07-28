<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Product Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/manager-products.css') ?>?v=<?= time() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body>
    <div class="mgr-app">
        <!-- Sidebar -->
        <aside class="mgr-sidebar">
            <div class="mgr-logo-section">
                <h1 class="mgr-logo-main">earthbred</h1>
                <p class="mgr-logo-sub">Coffee Studio</p>
            </div>
            <div class="mgr-user-profile">
                <i class="fa-solid fa-circle-user mgr-profile-icon"></i>
                <div class="mgr-user-info">
                    <p class="mgr-user-name">Juan Reyes</p>
                    <p class="mgr-user-id">MGR-001</p>
                </div>
            </div>
            <nav class="mgr-nav">
                <h3 class="mgr-nav-heading">NAVIGATION</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager'">
                        <i class="fa-solid fa-chart-line mgr-nav-icon"></i> Dashboard
                    </li>
                </ul>
                <h3 class="mgr-nav-heading">OPERATIONS</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item active">
                        <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                    </li>
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/shift-notes'">
                        <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                    </li>
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                        <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                    </li>
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/inventory'">
                        <i class="fa-solid fa-boxes-stacked mgr-nav-icon"></i> Inventory
                    </li>
                </ul>
                <h3 class="mgr-nav-heading">TOOLS</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/ai'">
                        <i class="fa-solid fa-robot mgr-nav-icon"></i> AI Gemini Assistant
                    </li>
                    <li class="mgr-nav-item owner-only-link" onclick="window.location.href='<?= url('') ?>/manager/accounts'">
                        <i class="fa-solid fa-users mgr-nav-icon"></i> Account Management
                    </li>
                </ul>
            </nav>
            <div class="mgr-sidebar-footer">
                <div class="mgr-clock-out" onclick="window.location.href='<?= url('') ?>/login'">
                    <i class="fa-solid fa-power-off"></i> Clock Out
                </div>
            </div>
        </aside>
        <script>
            if (localStorage.getItem('userRole') !== 'owner') {
                document.querySelectorAll('.owner-only-link').forEach(el => el.style.display = 'none');
            }
            if (localStorage.getItem('userName')) {
                document.querySelector('.mgr-user-name').textContent = localStorage.getItem('userName');
            }
        </script>

        <!-- Main Content Area -->
        <main class="mgr-main">
            <header class="mgr-header" style="justify-content: space-between; display: flex;">
                <h2 class="mgr-page-title">Product Management</h2>
            </header>

            <div style="padding: 1.25rem 1.25rem 0; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
                <button class="add-product-btn owner-only-link" onclick="openVoidPinModal()" style="background-color:#d35400;"><i class="fa-solid fa-key"></i> Manage Void PIN</button>
                <button class="add-product-btn" onclick="openDiscountsModal()" style="background-color:#8d786c;"><i class="fa-solid fa-percent"></i> Manage Discounts</button>
                <button class="add-product-btn" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Item</button>
            </div>

            <div class="mgr-content">
                <div class="products-grid">
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-wrap">
                            <img src="<?= asset('images/' . $product->picture) ?>" alt="<?= htmlspecialchars($product->name) ?>">
                        </div>
                        <div class="product-info">
                            <h4 class="product-name"><?= htmlspecialchars($product->name) ?></h4>
                            <p class="product-category"><?= htmlspecialchars($product->category) ?></p>
                            <p class="product-price">
                                ₱ <?= $product->price ?>
                                <?php if($product->discounted_price): ?>
                                    <span class="discounted"> (Discount: ₱ <?= $product->discounted_price ?>)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="product-actions">
                            <button class="edit-btn" onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Product Modal (Add/Edit) -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add Product</h3>
                <button class="close-modal-btn" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="id">
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="productName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select id="productCategory" name="category" required>
                            <option value="coffee">Coffee</option>
                            <option value="non-coffee">Non-Coffee</option>
                            <option value="lemonade">Lemonade</option>
                            <option value="foods">Foods</option>
                        </select>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <label>Price (₱)</label>
                            <input type="number" step="0.01" id="productPrice" name="price" required>
                        </div>
                        <div class="col">
                            <label>Discounted Price (₱) [Optional]</label>
                            <input type="number" step="0.01" id="productDiscountedPrice" name="discounted_price">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Picture</label>
                        <input type="file" id="productPicture" name="picture" accept="image/*">
                        <small>Leave blank if you don't want to change the picture during edit.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="save-btn" id="saveProductBtn">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Discounts Modal -->
    <div class="modal-overlay" id="discountsModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Manage Global Discounts</h3>
                <button class="close-modal-btn" onclick="closeDiscountsModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="addDiscountForm" style="display:flex; gap:10px; margin-bottom: 20px;">
                    <input type="text" id="discountName" placeholder="Name (e.g. 10% Off)" required style="flex:1; padding:8px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif;">
                    <input type="number" id="discountPercent" placeholder="%" min="1" max="100" required style="width:70px; padding:8px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif;">
                    <button type="submit" class="save-btn" style="padding: 8px 16px; margin:0; width:auto;">Add</button>
                </form>
                <div id="discountsList" style="max-height: 300px; overflow-y: auto;">
                    <!-- populated by js -->
                </div>
            </div>
        </div>
    </div>


    <!-- Void PIN Modal -->
    <div class="modal-overlay" id="voidPinModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Manage Void PIN</h3>
                <button class="close-modal-btn" onclick="closeVoidPinModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="voidPinForm">
                    <div class="form-group">
                        <label for="newVoidPin">New Void PIN</label>
                        <input type="password" id="newVoidPin" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-family: 'Poppins', sans-serif;">
                    </div>
                    <button type="submit" class="save-btn" id="saveVoidPinBtn" style="margin-top: 15px; width: 100%;">Save PIN</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openVoidPinModal() {
            document.getElementById('voidPinModal').style.display = 'flex';
        }
        function closeVoidPinModal() {
            document.getElementById('voidPinModal').style.display = 'none';
        }

        document.getElementById('voidPinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pin = document.getElementById('newVoidPin').value;
            const btn = document.getElementById('saveVoidPinBtn');
            btn.textContent = 'Saving...';
            btn.disabled = true;

            try {
                const res = await fetch('<?= url('') ?>/api/void-pin/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ pin })
                });
                const data = await res.json();
                alert(data.message || (data.success ? 'PIN updated!' : 'Failed to update PIN.'));
                if (data.success) {
                    closeVoidPinModal();
                    document.getElementById('newVoidPin').value = '';
                }
            } catch (err) {
                console.error(err);
                alert('Error updating PIN');
            }
            btn.textContent = 'Save PIN';
            btn.disabled = false;
        });
    </script>
    <script src="<?= asset('js/manager-products.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
