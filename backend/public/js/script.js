function getAppBasePath() {
    const pathname = window.location.pathname;
    const idx = pathname.toLowerCase().indexOf('/backend/public');
    if (idx !== -1) {
        return pathname.substring(0, idx + '/backend/public'.length);
    }
    return '';
}

document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggle
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.querySelector('.password-toggle');

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        });
    }

    // Login Form Submit Handling
    const loginForm = document.querySelector('.login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.querySelector('.login-btn');

            try {
                loginBtn.textContent = 'Logging in...';
                loginBtn.disabled = true;

                const BASE = getAppBasePath();
                const API_URL = BASE + '/api/login'; 
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();

                if (data.success) {
                    localStorage.setItem('userRole', data.user.role);
                    localStorage.setItem('userName', data.user.name);
                    
                    if (data.user.role === 'owner' || data.user.role === 'manager') {
                        window.location.href = BASE + '/manager';
                    } else {
                        window.location.href = BASE + '/pos';
                    }
                } else {
                    alert(data.message || 'Login failed.');
                    loginBtn.textContent = 'Log In';
                    loginBtn.disabled = false;
                }
                
            } catch (error) {
                console.error('Error logging in:', error);
                loginBtn.textContent = 'Log In';
                loginBtn.disabled = false;
            }
        });
    }

    // Forgot Password Handling
    const forgotPasswordBtn = document.querySelector('.forgot-password');
    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('email');
            let email = emailInput ? emailInput.value.trim() : '';

            if (!email) {
                email = prompt('Please enter your email address:');
            }

            if (!email) return;

            try {
                const BASE = getAppBasePath();
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const response = await fetch(BASE + '/api/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();
                alert(data.message);
            } catch (err) {
                console.error('Forgot Password error:', err);
                alert('An error occurred. Please try again.');
            }
        });
    }
});
