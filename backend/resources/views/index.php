<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred Coffee Studio - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body>
    <div class="login-container">
        <!-- Left Side Image -->
        <div class="login-image-section"></div>
        
        <!-- Right Side Form -->
        <div class="login-form-section">
            <div class="form-wrapper">
                
                <div class="logo-container">
                    <h1 class="logo-main">earthbred</h1>
                    <p class="logo-sub">Coffee Studio</p>
                </div>
                
                <div class="heading-container">
                    <h2 class="main-heading">Brewing<br>excellence<br>behind every cup.</h2>
                    <img src="<?= asset('images/cup.png') ?>" alt="Coffee Cup" class="coffee-cup-image">
                </div>

                <form class="login-form">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required>
                            <i class="fa-regular fa-eye password-toggle"></i>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">Log In</button>
                </form>

            </div>
        </div>
    </div>
    <script src="<?= asset('js/script.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
