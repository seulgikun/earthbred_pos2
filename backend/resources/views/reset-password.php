<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Earthbred Coffee Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #222222;
            color: #1a1a1a;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .card {
            background-color: #f7f3eb;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2c2520;
            text-align: center;
        }
        p.subtitle {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .input-group {
            margin-bottom: 1.25rem;
        }
        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: #333;
        }
        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #fff;
        }
        .btn {
            width: 100%;
            background-color: #2c2520;
            color: #ffffff;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background-color: #4a3f37; }
        .msg {
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: none;
        }
        .msg.error { background: #fde8e8; color: #c5221f; }
        .msg.success { background: #e8f8f0; color: #1e824c; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Owner Password</h2>
        <p class="subtitle">Enter your new password for Super Admin access</p>

        <div id="alertMsg" class="msg"></div>

        <form id="resetForm">
            <input type="hidden" id="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
            <input type="hidden" id="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">

            <div class="input-group">
                <label for="password">New Password</label>
                <input type="password" id="password" required minlength="6" placeholder="At least 6 characters">
            </div>

            <div class="input-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" required minlength="6" placeholder="Repeat new password">
            </div>

            <button type="submit" class="btn" id="submitBtn">Update Password</button>
        </form>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const alertMsg = document.getElementById('alertMsg');
            const token = document.getElementById('token').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            alertMsg.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            try {
                const getAppBasePath = () => {
                    const pathname = window.location.pathname;
                    const idx = pathname.toLowerCase().indexOf('/backend/public');
                    return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
                };

                const response = await fetch(getAppBasePath() + '/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email, token, password, password_confirmation })
                });

                const data = await response.json();

                if (data.success) {
                    alertMsg.className = 'msg success';
                    alertMsg.textContent = data.message;
                    alertMsg.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = getAppBasePath() + '/login';
                    }, 2000);
                } else {
                    alertMsg.className = 'msg error';
                    alertMsg.textContent = data.message || 'Error updating password';
                    alertMsg.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Update Password';
                }
            } catch (err) {
                alertMsg.className = 'msg error';
                alertMsg.textContent = 'Failed to submit password reset.';
                alertMsg.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Password';
            }
        });
    </script>
</body>
</html>
