<?php
require_once 'includes/config.php';
requireRole('customer');

$pageTitle = 'Customer Dashboard - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$searchPlaceholder = 'Search jobs, printers...';
$menuItems = [
    ['url' => 'customer-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => true],
    ['url' => 'new-project.php', 'icon' => 'plus-circle', 'label' => 'New Project', 'active' => false],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'My Projects', 'active' => false],
    ['url' => 'my-quotes.php', 'icon' => 'file-invoice', 'label' => 'My Quotes', 'active' => false],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => false],
];

// Get user projects count from database
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobs WHERE customer_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalProjects = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jobs WHERE customer_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingCount = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jobs WHERE customer_id = ? AND status = 'in_progress'");
    $stmt->execute([$_SESSION['user_id']]);
    $inProgressCount = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jobs WHERE customer_id = ? AND status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $completedCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching dashboard stats: " . $e->getMessage());
    $totalProjects = 0;
    $pendingCount = 0;
    $inProgressCount = 0;
    $completedCount = 0;
}

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <?php include 'includes/dashboard-header.php'; ?>
        
        <main class="main-content">
        <div class="jobs-overview">
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">My Projects</div>
                <div class="job-status-value"><?php echo $totalProjects; ?></div>
                <div class="job-status-icon">📁</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">In Production</div>
                <div class="job-status-value"><?php echo $inProgressCount; ?></div>
                <div class="job-status-icon">🏭</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Completed</div>
                <div class="job-status-value"><?php echo $completedCount; ?></div>
                <div class="job-status-icon">✅</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Pending</div>
                <div class="job-status-value"><?php echo $pendingCount; ?></div>
                <div class="job-status-icon">⏳</div>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-tasks"></i> Recent Print Jobs</h3>
                <div style="display: flex; gap: 1rem;">
                    <a href="my-projects.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-list"></i> View All
                    </a>
                    <a href="new-project.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Job
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>001</strong></td>
                            <td>Business Cards</td>
                            <td><span class="status-badge status-in_progress">In Progress</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 75%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">75%</span>
                                </div>
                            </td>
                            <td>2024-01-20</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=1" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>002</strong></td>
                            <td>Marketing Brochures</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 25%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">25%</span>
                                </div>
                            </td>
                            <td>2024-01-25</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=2" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>003</strong></td>
                            <td>Product Catalog</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 100%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">100%</span>
                                </div>
                            </td>
                            <td>2024-01-15</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=3" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="my-quotes.php" class="btn btn-sm btn-outline">
                                        <i class="fas fa-download"></i> Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stats-grid">
            <a href="my-quotes.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-info">
                    <h3>5</h3>
                    <p>Pending Quotes</p>
                </div>
            </a>
            <a href="my-projects.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3>2.5 days</h3>
                    <p>Avg. Turnaround</p>
                </div>
            </a>
            <a href="my-quotes.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <h3>KES 125,000</h3>
                    <p>Total Spent</p>
                </div>
            </a>
            <a href="profile.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3>4.9/5</h3>
                    <p>Satisfaction Rating</p>
                </div>
            </a>
        </div>
        </main>
    </div>
</div>

<script>
// INLINE FIX - Notification and Sidebar
console.log('=== INLINE SCRIPT LOADED ===');

// Fix notification button
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, fixing notification and sidebar...');
    
    // Notification button fix
    const notifBtn = document.getElementById('notificationsBtn');
    const notifDropdown = document.getElementById('notificationsDropdown');
    
    if (notifBtn && notifDropdown) {
        console.log('Notification elements found, adding listener...');
        notifBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Notification clicked!');
            
            if (notifDropdown.style.display === 'block') {
                notifDropdown.style.display = 'none';
            } else {
                notifDropdown.style.display = 'block';
            }
        });
    }
    
    // Sidebar links fix
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    console.log('Found ' + sidebarLinks.length + ' sidebar links');
    
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            console.log('Navigating to: ' + url);
            window.location.href = url;
        });
    });
    
    // User menu fix
    const userMenu = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenu && userDropdown) {
        userMenu.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (userDropdown.style.display === 'block') {
                userDropdown.style.display = 'none';
            } else {
                userDropdown.style.display = 'block';
            }
            
            // Close notifications
            if (notifDropdown) {
                notifDropdown.style.display = 'none';
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (notifDropdown && !notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            notifDropdown.style.display = 'none';
        }
        if (userDropdown && !userMenu.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.style.display = 'none';
        }
    });
    
    console.log('=== ALL FIXES APPLIED ===');
});
</script>

<?php include 'includes/footer.php'; ?>
