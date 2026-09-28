<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Check if email already exists in database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Insert new user into database
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, role, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $username = strtolower($firstName . $lastName . rand(100, 999));
                
                $stmt->execute([
                    $username,
                    $email,
                    $password, // In production, use password_hash($password, PASSWORD_DEFAULT)
                    $firstName,
                    $lastName,
                    $role
                ]);
                
                $success = 'Account created successfully! Redirecting to login...';
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $error = 'Registration error. Please try again.';
            error_log("Signup error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Sign Up - ' . SITE_NAME;
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
    <div class="signup-container">
        <form id="signupForm" class="signup-form" method="POST">
            <a href="index.php" class="btn btn-outline" style="position: absolute; top: 20px; left: 20px; padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <div class="signup-header">
                <div class="signup-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Join Techna Print</h2>
                <p>Create your account to get started</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName"><i class="fas fa-user"></i> First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="lastName"><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Doe" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="input-with-icon">
                    <input type="password" id="password" name="password" placeholder="Create a secure password" required>
                    <i class="fas fa-lock"></i>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small style="color: var(--gray); font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                    <i class="fas fa-info-circle"></i> Minimum 4 characters required
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                <div class="input-with-icon">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required>
                    <i class="fas fa-lock"></i>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small id="passwordMatch" style="font-size: 0.85rem; margin-top: 0.5rem; display: none;"></small>
            </div>
            
            <div class="form-group">
                <label for="role"><i class="fas fa-briefcase"></i> Account Type</label>
                <div class="role-options">
                    <div class="role-option" data-value="customer">
                        <i class="fas fa-user"></i>
                        <span>Customer</span>
                    </div>
                    <div class="role-option" data-value="designer">
                        <i class="fas fa-palette"></i>
                        <span>Designer</span>
                    </div>
                    <div class="role-option" data-value="manager">
                        <i class="fas fa-chart-line"></i>
                        <span>Manager</span>
                    </div>
                </div>
                <input type="hidden" id="role" name="role" required>
            </div>
            
            <button type="submit" class="btn-signup">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </form>
    </div>

    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('role').value = this.getAttribute('data-value');
            });
        });
        
        // Password visibility toggle
        function togglePassword(inputId, button) {
            const passwordInput = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // Password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const matchMessage = document.getElementById('passwordMatch');
        
        function checkPasswordMatch() {
            if (confirmPassword.value === '') {
                matchMessage.style.display = 'none';
                return;
            }
            
            matchMessage.style.display = 'block';
            if (password.value === confirmPassword.value) {
                matchMessage.style.color = 'var(--success)';
                matchMessage.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
            } else {
                matchMessage.style.color = 'var(--danger)';
                matchMessage.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
            }
        }
        
        password.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
        
        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
