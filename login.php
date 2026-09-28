<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    redirect($role . '-dashboard.php');
}

$error = '';
$selectedPortal = $_GET['portal'] ?? null;

// Portal information
$portalInfo = [
    'customer' => ['icon' => 'fa-user', 'title' => 'Customer Portal', 'desc' => 'Access your print jobs and orders'],
    'designer' => ['icon' => 'fa-palette', 'title' => 'Designer Portal', 'desc' => 'Manage design projects'],
    'manager' => ['icon' => 'fa-chart-line', 'title' => 'Manager Portal', 'desc' => 'Oversee operations and analytics']
];

// Demo accounts mapping
$demoAccounts = [
    'customer' => 'demo@technaprint.com',
    'designer' => 'designer@technaprint.com',
    'manager' => 'manager@technaprint.com'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        try {
            // Query database for user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check password (demo users have plain text 'demo', real users would be hashed)
                if ($password === $user['password']) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['logged_in'] = true;
                    
                    // Check if a specific portal was requested
                    $requestedPortal = $_POST['requested_portal'] ?? null;
                    
                    if ($requestedPortal && $requestedPortal === $user['role']) {
                        // User role matches requested portal
                        redirect($user['role'] . '-dashboard.php');
                    } elseif ($requestedPortal && $requestedPortal !== $user['role']) {
                        // User role doesn't match requested portal
                        $error = 'Your account does not have access to the ' . ucfirst($requestedPortal) . ' Portal. You have been logged in to your ' . ucfirst($user['role']) . ' Portal.';
                        $_SESSION['portal_mismatch'] = true;
                        redirect($user['role'] . '-dashboard.php');
                    } else {
                        // No specific portal requested, use user's role
                        redirect($user['role'] . '-dashboard.php');
                    }
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Email not found. Please sign up first.';
            }
        } catch (PDOException $e) {
            $error = 'Login error. Please try again.';
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $error = 'Please enter both email and password';
    }
}

$pageTitle = 'Login - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="bg-animation">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>

    <div class="login-container">
        <form id="loginForm" class="login-form" method="POST">
            <a href="index.php" class="btn btn-outline" style="position: absolute; top: 20px; left: 20px; padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2><i class="fas fa-print"></i> Techna Print</h2>
            
            <?php if ($selectedPortal && isset($portalInfo[$selectedPortal])): ?>
                <div class="portal-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                    <i class="fas <?php echo $portalInfo[$selectedPortal]['icon']; ?>" style="font-size: 2rem; margin-bottom: 8px;"></i>
                    <h3 style="margin: 0; font-size: 1.3rem;"><?php echo $portalInfo[$selectedPortal]['title']; ?></h3>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.9;"><?php echo $portalInfo[$selectedPortal]['desc']; ?></p>
                </div>
                <input type="hidden" name="requested_portal" value="<?php echo htmlspecialchars($selectedPortal); ?>">
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <div class="input-with-icon">
                    <?php
                    $defaultEmail = $selectedPortal && isset($demoAccounts[$selectedPortal]) 
                        ? $demoAccounts[$selectedPortal] 
                        : 'demo@technaprint.com';
                    ?>
                    <input type="email" id="email" name="email" placeholder="your.email@example.com" required value="<?php echo $defaultEmail; ?>">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="input-with-icon">
                    <input type="password" id="password" name="password" placeholder="Enter your password" value="demo" required>
                    <i class="fas fa-lock"></i>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <label style="margin: 0; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="rememberMe">
                    <span style="font-weight: 500;">Remember me</span>
                </label>
                <a href="forgot-password.php" style="font-size: 0.9rem; color: var(--primary); text-decoration: none; font-weight: 600;">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>

            <div class="login-links">
                <span>Don't have an account? <a href="signup.php">Sign up here</a></span>
                <?php if ($selectedPortal): ?>
                <span style="margin-top: 10px; display: block;"><a href="index.php#portals"><i class="fas fa-arrow-left"></i> Choose different portal</a></span>
                <?php endif; ?>
            </div>

            <div class="demo-box">
                <h4><i class="fas fa-info-circle"></i> Demo Access</h4>
                <?php
                $demoEmail = $selectedPortal && isset($demoAccounts[$selectedPortal]) 
                    ? $demoAccounts[$selectedPortal] 
                    : 'demo@technaprint.com';
                ?>
                <div class="demo-credentials">
                    <div class="demo-credential">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo $demoEmail; ?></span>
                    </div>
                    <div class="demo-credential">
                        <i class="fas fa-key"></i>
                        <span>Password: demo</span>
                    </div>
                </div>
                <?php if ($selectedPortal): ?>
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(67, 97, 238, 0.2); font-size: 0.85rem; color: var(--gray);">
                    <i class="fas fa-lightbulb"></i> Use this account to access the <?php echo ucfirst($selectedPortal); ?> Portal
                </div>
                <?php else: ?>
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(67, 97, 238, 0.2); font-size: 0.8rem; color: var(--gray);">
                    <strong>Other demo accounts:</strong><br>
                    designer@technaprint.com | manager@technaprint.com
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        // Simple role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('roleInput').value = this.dataset.role;
            });
        });

        // Password visibility toggle
        document.getElementById('passwordToggle')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    </script>
</body>
</html>
