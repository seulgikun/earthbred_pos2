<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>Earthbred - Order Queuing</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/pos.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/queue.css') ?>">
    <style>
        body {
            display: block !important;
            height: 100vh !important;
            overflow: hidden !important;
            background-color: #222 !important;
        }
        .app-container {
            display: flex !important;
            width: 100% !important;
            height: 100vh !important;
        }
    </style>
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
                    <p class="user-name">Aries Marolina</p>
                    <p class="user-id">Staff 001</p>
                </div>
            </div>

            <nav class="menu-section">
                <h3 class="menu-heading">MENU</h3>
                <ul class="menu-list">
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos'">
                        <span class="menu-icon">🍽️</span> All Items
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=coffee'">
                        <span class="menu-icon">☕</span> Coffee
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=non-coffee'">
                        <span class="menu-icon">🍵</span> Non-Coffee
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=lemonade'">
                        <span class="menu-icon">🍹</span> Lemonade
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=foods'">
                        <span class="menu-icon">🍲</span> Foods
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/shift-notes'">
                        <span class="menu-icon">📝</span> Shift Notes
                    </li>
                    <li class="menu-item active" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📋</span> Order Queuing
                    </li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📦</span> Inventory
                    </li>
                </ul>
            </nav>

            <div class="clock-out" onclick="if(confirm('Are you sure you want to clock out?')) { localStorage.removeItem('earthbred_cart'); window.location.href = '<?= url('') ?>/login'; }">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-header queue-header">
                <h2>Order Queue (Today)</h2>
            </header>

            <div style="padding: 1.5rem 2rem 0; display: flex; justify-content: flex-end;">
                <div class="daily-total">
                    <span>TOTAL SALES:</span>
                    <strong>₱ <?= number_format($totalSales, 2) ?></strong>
                </div>
            </div>

            <div class="queue-container">
                <?php if (count($orders) === 0): ?>
                    <div class="empty-queue">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <p>No orders placed today.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-grid">
                        <?php foreach($orders as $order): ?>
                            <div class="order-card status-<?= $order->status ?>" id="order-card-<?= $order->id ?>">
                                <div class="order-header">
                                    <div>
                                        <h3>Order #<?= $order->id ?></h3>
                                        <span class="order-time"><?= $order->created_at->format('h:i A') ?></span>
                                    </div>
                                    <span class="badge badge-<?= $order->status ?>"><?= ucfirst($order->status) ?></span>
                                </div>
                                
                                <div class="order-items">
                                    <?php foreach($order->items as $item): ?>
                                        <div class="order-item">
                                            <span class="qty"><?= $item->quantity ?>x</span>
                                            <div class="item-details">
                                                <span class="name"><?= $item->product_name ?></span>
                                                <?php if($item->customer_name): ?>
                                                    <span class="customer"><i class="fa-solid fa-user"></i> <?= $item->customer_name ?></span>
                                                <?php endif; ?>
                                                <?php if(!empty($item->addons)): ?>
                                                    <span class="addons">+ <?= implode(', ', $item->addons) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="price">₱ <?= number_format($item->item_total, 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="order-footer">
                                    <div class="order-total-info">
                                        <span>Total:</span>
                                        <strong>₱ <?= number_format($order->total, 2) ?></strong>
                                    </div>
                                    <div class="order-actions">
                                        <?php if($order->status === 'pending'): ?>
                                            <button class="btn-void" onclick="updateOrderStatus(<?= $order->id ?>, 'void')"><i class="fa-solid fa-ban"></i> Void</button>
                                            <button class="btn-complete" onclick="updateOrderStatus(<?= $order->id ?>, 'completed')"><i class="fa-solid fa-check"></i> Complete</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Void Auth Modal -->
    <div class="modal-overlay" id="voidAuthModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: #fff; padding: 2rem; border-radius: 10px; max-width: 400px; width: 90%;">
            <h3 style="margin-top: 0; font-family: 'Montserrat', sans-serif;">Void Authentication</h3>
            <p style="font-size: 0.9rem; margin-bottom: 1rem;">Please enter the Owner's Void PIN to cancel this order.</p>
            <input type="password" id="voidPinInput" placeholder="Enter PIN" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-family: 'Poppins', sans-serif; margin-bottom: 1rem;">
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="closeVoidModal()" style="padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; background: #eee;">Cancel</button>
                <button onclick="submitVoidAuth()" style="padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; background: #d35400; color: #fff;">Authenticate</button>
            </div>
        </div>
    </div>

    <script>
        let pendingVoidOrderId = null;

        function closeVoidModal() {
            document.getElementById('voidAuthModal').style.display = 'none';
            document.getElementById('voidPinInput').value = '';
            pendingVoidOrderId = null;
        }

        async function submitVoidAuth() {
            const pin = document.getElementById('voidPinInput').value;
            if (!pin) return alert('Please enter a PIN');

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('<?= url('') ?>/api/void-pin/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pin: pin })
                });

                const result = await response.json();
                if (result.success) {
                    const orderId = pendingVoidOrderId;
                    closeVoidModal();
                    // Proceed with actual voiding
                    executeOrderStatusUpdate(orderId, 'void');
                } else {
                    alert(result.message || 'Authentication failed');
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
            }
        }

        async function executeOrderStatusUpdate(orderId, status) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`<?= url('') ?>/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Error updating status');
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
            }
        }

        function updateOrderStatus(orderId, status) {
            if (status === 'void') {
                pendingVoidOrderId = orderId;
                document.getElementById('voidAuthModal').style.display = 'flex';
                return;
            }
            executeOrderStatusUpdate(orderId, status);
        }
    </script>
<script src="<?= asset('js/sidebar-toggle.js') ?>?v=<?= time() ?>"></script>
</body>
</html>


