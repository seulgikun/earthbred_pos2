<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/pos.css') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">

</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-section">
                <h1 class="logo-main">earthbred</h1>
                <p class="logo-sub">Coffee Studio</p>
            </div>
            
            <div class="user-profile">
                <i class="fa-solid fa-circle-user profile-icon"></i>
                <div class="user-info">
                    <p class="user-name" id="sidebarUserName">Cashier</p>
                    <p class="user-id">Staff 001</p>
                </div>
            </div>
            <script>
                if (localStorage.getItem('userName')) {
                    document.getElementById('sidebarUserName').textContent = localStorage.getItem('userName');
                }
            </script>

            <nav class="menu-section">
                <h3 class="menu-heading">MENU</h3>
                <ul class="menu-list">
                    <li class="menu-item active" data-filter="all">
                        <span class="menu-icon">🍽️</span> All Items
                    </li>
                    <li class="menu-item" data-filter="coffee">
                        <span class="menu-icon">☕</span> Coffee
                    </li>
                    <li class="menu-item" data-filter="non-coffee">
                        <span class="menu-icon">🍵</span> Non-Coffee
                    </li>
                    <li class="menu-item" data-filter="lemonade">
                        <span class="menu-icon">🍹</span> Lemonade
                    </li>
                    <li class="menu-item" data-filter="foods">
                        <span class="menu-icon">🍲</span> Foods
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/shift-notes'">
                        <span class="menu-icon">📝</span> Shift Notes
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📋</span> Order Queuing
                    </li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📦</span> Inventory
                    </li>
                </ul>
            </nav>

            <div class="clock-out">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-spacer"></div>
                <button class="order-btn">
                    <i class="fa-solid fa-cart-shopping"></i> Order
                </button>
            </header>

            <!-- Product Grid -->
            <div class="product-grid">
                
                <?php foreach($products as $product): ?>
                <div class="product-card" data-category="<?= htmlspecialchars($product->category) ?>" data-id="<?= $product->id ?>" data-price="<?= $product->discounted_price ? $product->discounted_price : $product->price ?>">
                    <button class="add-btn"><i class="fa-solid fa-plus"></i></button>
                    <img src="<?= asset('images/' . $product->picture) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="product-image">
                    <h4 class="product-name"><?= htmlspecialchars($product->name) ?></h4>
                    <?php if($product->discounted_price): ?>
                        <p class="product-price">
                            <span style="text-decoration: line-through; font-size: 0.8em; color: #888;">₱ <?= number_format($product->price, 0) ?></span>
                            ₱ <?= number_format($product->discounted_price, 0) ?>
                        </p>
                    <?php else: ?>
                        <p class="product-price">₱ <?= number_format($product->price, 0) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

            </div>

        </main>
    </div>

    <!-- Product Modal -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalProductName">Product Name</h3>
                <button class="close-modal-btn" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="customer-section">
                    <h4>Customer Name (Optional)</h4>
                    <input type="text" id="customerNameInput" class="customer-input" placeholder="e.g., John Doe">
                </div>

                <div class="quantity-section">
                    <h4>Quantity</h4>
                    <div class="quantity-controls">
                        <button class="qty-btn" id="qtyMinus"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" class="qty-input" id="qtyInput" value="1" min="1" readonly>
                        <button class="qty-btn" id="qtyPlus"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <div class="addons-section">
                    <h4>Add-ons</h4>
                    <div class="addon-item">
                        <label class="addon-label">
                            <input type="checkbox" class="addon-checkbox" data-price="30" value="Extra Espresso Shot">
                            <span class="custom-checkbox"></span>
                            Extra Espresso Shot (+₱ 30)
                        </label>
                    </div>
                    <div class="addon-item">
                        <label class="addon-label">
                            <input type="checkbox" class="addon-checkbox" data-price="40" value="Oat Milk">
                            <span class="custom-checkbox"></span>
                            Oat Milk (+₱ 40)
                        </label>
                    </div>
                    <div class="addon-item">
                        <label class="addon-label">
                            <input type="checkbox" class="addon-checkbox" data-price="0" value="Less Ice">
                            <span class="custom-checkbox"></span>
                            Less Ice (Free)
                        </label>
                    </div>
                    <div class="addon-item">
                        <label class="addon-label">
                            <input type="checkbox" class="addon-checkbox" data-price="20" value="Extra Sweet">
                            <span class="custom-checkbox"></span>
                            Extra Sweet (+₱ 20)
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="add-to-order-btn" id="addToOrderBtn">
                    Add to Order <span id="modalTotalPrice">₱ 0</span>
                </button>
            </div>
        </div>
    </div>


    
    <script src="<?= asset('js/pos.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
