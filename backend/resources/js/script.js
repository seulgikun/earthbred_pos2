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

                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    alert(data.message || 'Login failed. Please check your credentials.');
                    loginBtn.textContent = 'Log In';
                    loginBtn.disabled = false;
                    return;
                }

                // Store user session info
                localStorage.setItem('userId', data.user.id);
                localStorage.setItem('userName', data.user.name);
                localStorage.setItem('userEmail', data.user.email);
                localStorage.setItem('userRole', data.user.role);

                // Role-based redirection
                if (data.user.role === 'owner' || data.user.role === 'manager') {
                    window.location.href = '/manager';
                } else {
                    window.location.href = '/pos';
                }
                
            } catch (error) {
                console.error('Error logging in:', error);
                alert('A network error occurred. Please try again.');
                loginBtn.textContent = 'Log In';
                loginBtn.disabled = false;
            }
        });
    }
});
