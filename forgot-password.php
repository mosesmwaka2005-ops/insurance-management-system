<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // In a real application, you would:
        // 1. Check if email exists in database
        // 2. Generate a password reset token
        // 3. Send reset link via email
        
        // For demo purposes, we'll just show a success message
        $success = 'If an account exists with this email, you will receive password reset instructions shortly.';
    }
}

$pageTitle = 'Forgot Password - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="bg-animation">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>

    <div class="login-container">
        <form id="forgotPasswordForm" class="login-form" method="POST">
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <p style="text-align: center; color: var(--gray); margin-bottom: 2rem; font-size: 0.95rem;">
                Enter your email address and we'll send you instructions to reset your password.
            </p>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <div class="input-with-icon">
                    <input type="email" id="email" name="email" placeholder="your.email@example.com" required autofocus>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="resetBtn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
            <?php endif; ?>

            <div class="login-links">
                <span>Remember your password? <a href="login.php">Sign in here</a></span>
            </div>
            
            <?php if (!$success): ?>
            <div class="demo-box">
                <h4><i class="fas fa-info-circle"></i> Demo Note</h4>
                <p style="font-size: 0.9rem; margin: 0; color: var(--gray);">
                    This is a demo application. In production, a real email with reset instructions would be sent.
                </p>
            </div>
            <?php else: ?>
            <div class="demo-box">
                <h4><i class="fas fa-check-circle"></i> Next Steps</h4>
                <p style="font-size: 0.9rem; margin: 0 0 0.5rem 0; color: var(--gray);">
                    In a real application, you would:
                </p>
                <ul style="font-size: 0.85rem; color: var(--gray); margin: 0; padding-left: 1.5rem;">
                    <li>Check your email inbox</li>
                    <li>Click the reset link</li>
                    <li>Create a new password</li>
                </ul>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(102, 126, 234, 0.2);">
                    <a href="login.php" class="btn btn-primary btn-sm" style="width: 100%; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Add loading state to button
        document.getElementById('forgotPasswordForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('resetBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            }
        });
    </script>
</body>
</html>
