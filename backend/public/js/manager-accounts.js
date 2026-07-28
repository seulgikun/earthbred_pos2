/* ============================================================
   manager-accounts.js — Earthbred Account Management Logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    
    // Check role again just in case
    if (localStorage.getItem('userRole') !== 'owner') {
        window.location.href = BASE + '/manager';
        return;
    }

    loadAccounts();

    // Setup Modals
    window.openAddAccountModal = function() {
        document.getElementById('addAccountForm').reset();
        document.getElementById('addAccountModal').classList.add('active');
    };

    window.closeAddAccountModal = function() {
        document.getElementById('addAccountModal').classList.remove('active');
    };

    window.openChangePasswordModal = function(id, name) {
        document.getElementById('changePasswordForm').reset();
        document.getElementById('cpUserId').value = id;
        document.getElementById('cpUserName').textContent = name;
        document.getElementById('changePasswordModal').classList.add('active');
    };

    window.closeChangePasswordModal = function() {
        document.getElementById('changePasswordModal').classList.remove('active');
    };

    // Load Accounts
    function loadAccounts() {
        fetch(`${BASE}/api/users`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderAccountsTable(data.users);
                } else {
                    console.error('Failed to load accounts');
                }
            })
            .catch(err => console.error(err));
    }

    function renderAccountsTable(users) {
        const tbody = document.getElementById('accountsTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No accounts found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const tr = document.createElement('tr');
            
            // Format date
            const date = new Date(user.created_at);
            const dateStr = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            const isVerified = user.email_verified_at ? '<span style="color: #2e7d32; font-weight:600;"><i class="fa-solid fa-circle-check"></i> Verified</span>' : '<span style="color: #ed6c02; font-weight:600;"><i class="fa-solid fa-clock"></i> Verification Sent</span>';

            tr.innerHTML = `
                <td><strong>${user.name}</strong></td>
                <td>${user.email}</td>
                <td><span class="role-badge role-${user.role}">${user.role}</span></td>
                <td>${isVerified}</td>
                <td>
                    <button class="action-btn" onclick="openChangePasswordModal(${user.id}, '${user.name.replace(/'/g, "\\'")}')">
                        <i class="fa-solid fa-key"></i> Reset Password
                    </button>
                    <button class="action-btn" onclick="deleteUser(${user.id}, '${user.name.replace(/'/g, "\\'")}')" style="color: #d32f2f; border-color: #ffcdd2; margin-left: 5px;">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Delete User
    window.deleteUser = function(id, name) {
        if (!confirm(`Are you sure you want to delete the account for ${name}? This action cannot be undone.`)) {
            return;
        }

        fetch(`${BASE}/api/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Account deleted successfully.');
                loadAccounts();
            } else {
                alert('Error deleting account: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while deleting the account.');
        });
    };

    // Add Account Submit
    document.getElementById('addAccountForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('saveAccountBtn');
        btn.textContent = 'Saving & Sending Link...';
        btn.disabled = true;

        const payload = {
            name: document.getElementById('accountName').value,
            email: document.getElementById('accountEmail').value,
            password: document.getElementById('accountPassword').value,
            role: document.getElementById('accountRole').value
        };

        fetch(`${BASE}/api/users`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Account created and verification email sent!');
                closeAddAccountModal();
                loadAccounts();
            } else {
                alert('Error creating account: ' + (data.message || JSON.stringify(data.errors)));
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred.');
        })
        .finally(() => {
            btn.textContent = 'Create Account';
            btn.disabled = false;
        });
    });

    // Change Password Submit
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('savePasswordBtn');
        btn.textContent = 'Updating...';
        btn.disabled = true;

        const id = document.getElementById('cpUserId').value;
        const payload = {
            password: document.getElementById('cpNewPassword').value
        };

        fetch(`${BASE}/api/users/${id}/password`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Password updated successfully!');
                closeChangePasswordModal();
            } else {
                alert('Error updating password: ' + (data.message || JSON.stringify(data.errors)));
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred.');
        })
        .finally(() => {
            btn.textContent = 'Update Password';
            btn.disabled = false;
        });
    });

});
