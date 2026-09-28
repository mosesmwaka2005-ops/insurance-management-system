<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $company = $_POST['company'] ?? '';
        $address = $_POST['address'] ?? '';
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $error = 'First name, last name, and email are required';
        } else {
            try {
                // Update database
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, email = ?, phone = ?, company = ?, address = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $company,
                    $address,
                    $_SESSION['user_id']
                ]);
                
                // Update session data
                $_SESSION['username'] = $email;
                $_SESSION['email'] = $email;
                $_SESSION['full_name'] = $firstName . ' ' . $lastName;
                
                $success = 'Profile updated successfully!';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'Email already exists';
                } else {
                    $error = 'Error updating profile. Please try again.';
                    error_log("Profile update error: " . $e->getMessage());
                }
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required';
        } elseif (strlen($newPassword) < 4) {
            $error = 'Password must be at least 4 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } else {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && $user['password'] === $currentPassword) {
                    // Update password
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newPassword, $_SESSION['user_id']]);
                    
                    $success = 'Password updated successfully!';
                } else {
                    $error = 'Current password is incorrect';
                }
            } catch (PDOException $e) {
                $error = 'Error updating password. Please try again.';
                error_log("Password update error: " . $e->getMessage());
            }
        }
    }
}

// Get user data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $userData = [
        'first_name' => '',
        'last_name' => '',
        'email' => $_SESSION['email'] ?? '',
        'phone' => '',
        'company' => '',
        'address' => '',
        'role' => $_SESSION['role'] ?? 'customer'
    ];
}

$pageTitle = 'Profile - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'arrow-left', 'label' => 'Back to Dashboard', 'active' => false],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => true],
];

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <header class="header">
            <div>
                <h1>User Profile</h1>
                <p>Manage your account settings and preferences</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button class="btn btn-primary" onclick="saveProfile()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </header>

        <main class="main-content">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <!-- Profile Photo Section -->
        <div class="card">
            <h3><i class="fas fa-camera"></i> Profile Photo</h3>
            <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                <div style="position: relative;">
                    <div class="user-avatar" id="profileAvatar" style="width: 120px; height: 120px; font-size: 3rem; cursor: pointer;" onclick="document.getElementById('photoUpload').click()">
                        <?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>
                    </div>
                    <div style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" onclick="document.getElementById('photoUpload').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                    <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                </div>
                <div style="flex: 1;">
                    <h4 style="margin-bottom: 0.5rem;">Upload New Photo</h4>
                    <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;">
                        JPG, PNG or GIF. Max size 2MB. Recommended 400x400px.
                    </p>
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('photoUpload').click()">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="removePhoto()">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="card">
            <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
            <form id="profileForm" method="POST" action="profile.php">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName"><i class="fas fa-user"></i> First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName"><i class="fas fa-user"></i> Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" placeholder="+1 (555) 123-4567">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="company"><i class="fas fa-building"></i> Company (Optional)</label>
                        <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($userData['company'] ?? ''); ?>" placeholder="Your company name">
                    </div>
                    <div class="form-group">
                        <label for="role"><i class="fas fa-briefcase"></i> Role</label>
                        <input type="text" id="role" value="<?php echo ucfirst($userData['role']); ?>" readonly style="background: var(--light);">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address (Optional)</label>
                    <textarea id="address" name="address" rows="2" placeholder="Your address"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>

        <!-- Account Settings -->
        <div class="card">
            <h3><i class="fas fa-cog"></i> Preferences</h3>
            <div class="settings-list">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4><i class="fas fa-envelope"></i> Email Notifications</h4>
                        <p>Receive email updates about your jobs and account activity</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="emailNotif" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4><i class="fas fa-bell"></i> Push Notifications</h4>
                        <p>Get push notifications for important updates</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="pushNotif" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4><i class="fas fa-moon"></i> Dark Mode</h4>
                        <p>Switch to dark theme for better viewing at night</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="darkMode">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h3><i class="fas fa-lock"></i> Change Password</h3>
            <form id="passwordForm" method="POST" action="profile.php">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="currentPassword"><i class="fas fa-key"></i> Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="newPassword"><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Update Password
                </button>
            </form>
        </div>
        </main>
    </div>
</div>

<script>
// Load saved profile photo on page load
window.addEventListener('DOMContentLoaded', function() {
    const savedPhoto = localStorage.getItem('profilePhoto');
    if (savedPhoto) {
        const avatar = document.getElementById('profileAvatar');
        avatar.style.backgroundImage = `url(${savedPhoto})`;
        avatar.style.backgroundSize = 'cover';
        avatar.style.backgroundPosition = 'center';
        avatar.innerHTML = '';
    }
});

// Profile Photo Upload
let pendingPhoto = null;

document.getElementById('photoUpload')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showToast('Image size must be less than 2MB', 'error');
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            showToast('Please upload an image file', 'error');
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            pendingPhoto = e.target.result;
            const avatar = document.getElementById('profileAvatar');
            avatar.style.backgroundImage = `url(${pendingPhoto})`;
            avatar.style.backgroundSize = 'cover';
            avatar.style.backgroundPosition = 'center';
            avatar.innerHTML = '';
            showToast('Photo uploaded! Click Save Changes to apply.', 'success');
        };
        reader.readAsDataURL(file);
    }
});

function removePhoto() {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        pendingPhoto = 'REMOVE';
        const avatar = document.getElementById('profileAvatar');
        avatar.style.backgroundImage = '';
        avatar.innerHTML = '<?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>';
        localStorage.removeItem('profilePhoto');
        showToast('Photo removed! Click Save Changes to apply.', 'info');
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('profileForm').reset();
        pendingPhoto = null;
        
        // Restore saved photo or default
        const savedPhoto = localStorage.getItem('profilePhoto');
        const avatar = document.getElementById('profileAvatar');
        
        if (savedPhoto) {
            avatar.style.backgroundImage = `url(${savedPhoto})`;
            avatar.style.backgroundSize = 'cover';
            avatar.style.backgroundPosition = 'center';
            avatar.innerHTML = '';
        } else {
            avatar.style.backgroundImage = '';
            avatar.innerHTML = '<?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>';
        }
        
        showToast('Form reset to original values', 'info');
    }
}

function saveProfile() {
    const form = document.getElementById('profileForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Save profile photo to localStorage
    if (pendingPhoto) {
        if (pendingPhoto === 'REMOVE') {
            localStorage.removeItem('profilePhoto');
        } else {
            localStorage.setItem('profilePhoto', pendingPhoto);
        }
        pendingPhoto = null;
    }
    
    // Submit the form
    form.submit();
}

// Password form is now handled by PHP POST

// Dark mode toggle with persistence
document.getElementById('darkMode')?.addEventListener('change', function() {
    if (this.checked) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('darkMode', 'enabled');
        showToast('Dark mode enabled', 'info');
    } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('darkMode', 'disabled');
        showToast('Dark mode disabled', 'info');
    }
});

// Load dark mode preference on page load
const darkModePreference = localStorage.getItem('darkMode');
if (darkModePreference === 'enabled') {
    document.body.classList.add('dark-theme');
    const darkModeToggle = document.getElementById('darkMode');
    if (darkModeToggle) {
        darkModeToggle.checked = true;
    }
}

// Notification toggles
document.getElementById('emailNotif')?.addEventListener('change', function() {
    showToast(this.checked ? 'Email notifications enabled' : 'Email notifications disabled', 'info');
});

document.getElementById('pushNotif')?.addEventListener('change', function() {
    showToast(this.checked ? 'Push notifications enabled' : 'Push notifications disabled', 'info');
});

function showToast(message, type = 'info') {
    document.querySelectorAll('.toast').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} show`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
