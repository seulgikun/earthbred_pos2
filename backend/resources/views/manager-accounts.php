<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Account Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
    <!-- Inline styles for account management -->
    <style>
        .mgr-accounts-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .mgr-accounts-table th, .mgr-accounts-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .mgr-accounts-table th {
            background-color: #fcfbf9;
            color: #8d786c;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .mgr-accounts-table td {
            font-family: 'Poppins', sans-serif;
            color: #5c4a40;
            font-size: 0.95rem;
        }
        .mgr-accounts-table tr:last-child td {
            border-bottom: none;
        }
        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .role-manager {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .role-cashier {
            background-color: #f1f8e9;
            color: #558b2f;
        }
        .action-btn {
            background: none;
            border: 1px solid #dcdcdc;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #5c4a40;
            transition: all 0.2s;
        }
        .action-btn:hover {
            background: #f0f0f0;
            border-color: #8d786c;
        }
        
        /* Modals */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
            display: none; justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            background: #fff; width: 100%; max-width: 450px;
            border-radius: 12px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease-out forwards;
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-title { font-family: 'Montserrat', sans-serif; font-size: 1.2rem; color: #3d2e24; }
        .close-modal-btn { background: none; border: none; font-size: 1.2rem; color: #888; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #5c4a40; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; border: 1px solid #dcdcdc; border-radius: 6px;
            font-family: 'Poppins', sans-serif; font-size: 0.9rem;
            transition: border-color 0.2s; box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: #a87850;
        }
        .save-btn {
            background: #a87850; color: #fff; border: none; padding: 12px 20px;
            border-radius: 6px; width: 100%; font-family: 'Montserrat', sans-serif;
            font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .save-btn:hover { background: #875830; }
        
        .add-product-btn {
            background-color: #6a3a30;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .add-product-btn:hover {
            background-color: #4a2a22;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
    <script>
        // Check Owner Role on Page Load
        if (localStorage.getItem('userRole') !== 'owner') {
            window.location.href = '<?= url('') ?>/manager';
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('userName')) {
                document.getElementById('sidebarUserName').textContent = localStorage.getItem('userName');
            }
        });
    </script>
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
                    <p class="mgr-user-name" id="sidebarUserName">Owner</p>
                    <p class="mgr-user-id" id="sidebarUserId">OWNER-001</p>
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
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/products'">
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
                    <li class="mgr-nav-item active owner-only-link">
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

        <!-- Main Content Area -->
        <main class="mgr-main">
            <header class="mgr-header" style="justify-content: space-between; display: flex;">
                <h2 class="mgr-page-title">Account Management</h2>
                <div>
                    <button class="add-product-btn" onclick="openAddAccountModal()" style="background-color:#a87850;"><i class="fa-solid fa-plus"></i> Add Account</button>
                </div>
            </header>

            <div class="mgr-content">
                <table class="mgr-accounts-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTableBody">
                        <tr><td colspan="5" style="text-align: center;">Loading accounts...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Account Modal -->
    <div class="modal-overlay" id="addAccountModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Account</h3>
                <button class="close-modal-btn" onclick="closeAddAccountModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="addAccountForm">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="accountName" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="accountEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="accountPassword" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select id="accountRole" required>
                            <option value="cashier">Cashier</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    <button type="submit" class="save-btn" id="saveAccountBtn">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal-overlay" id="changePasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Change Password</h3>
                <button class="close-modal-btn" onclick="closeChangePasswordModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <input type="hidden" id="cpUserId">
                    <p style="font-family: 'Poppins', sans-serif; font-size: 0.9rem; margin-bottom: 15px; color: #5c4a40;">Changing password for: <strong id="cpUserName"></strong></p>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" id="cpNewPassword" required minlength="6">
                    </div>
                    <button type="submit" class="save-btn" id="savePasswordBtn">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/manager-accounts.js') ?>?v=<?= time() ?>"></script>
<script src="<?= asset('js/sidebar-toggle.js') ?>?v=<?= time() ?>"></script>
</body>
</html>

