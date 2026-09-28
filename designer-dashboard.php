<?php
require_once 'includes/config.php';
requireRole('designer');

// Get designer statistics from database
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE designer_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $activeProjects = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE designer_id = ? AND status = 'in_progress'");
    $stmt->execute([$_SESSION['user_id']]);
    $inDesign = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE designer_id = ? AND status = 'review'");
    $stmt->execute([$_SESSION['user_id']]);
    $readyForReview = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE designer_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingRevisions = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching designer stats: " . $e->getMessage());
    $activeProjects = 0;
    $inDesign = 0;
    $readyForReview = 0;
    $pendingRevisions = 0;
}

$pageTitle = 'Designer Dashboard - ' . SITE_NAME;
$sidebarTitle = 'Techna Design';
$sidebarIcon = 'palette';
$searchPlaceholder = 'Search jobs, clients...';
$menuItems = [
    ['url' => 'designer-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => true],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'My Projects', 'active' => false],
    ['url' => 'job-details.php', 'icon' => 'inbox', 'label' => 'Job Details', 'active' => false],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => false],
];

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <?php include 'includes/dashboard-header.php'; ?>
        
        <main class="main-content">
        <div class="jobs-overview">
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Active Projects</div>
                <div class="job-status-value"><?php echo $activeProjects; ?></div>
                <div class="job-status-icon">🎨</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">In Design</div>
                <div class="job-status-value"><?php echo $inDesign; ?></div>
                <div class="job-status-icon">✏️</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Ready for Review</div>
                <div class="job-status-value"><?php echo $readyForReview; ?></div>
                <div class="job-status-icon">👁️</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Pending Revisions</div>
                <div class="job-status-value"><?php echo $pendingRevisions; ?></div>
                <div class="job-status-icon">🔄</div>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Design Queue</h3>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <select id="statusFilter" class="search-input" style="width: 200px;">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Client Review</option>
                    </select>
                    <a href="my-projects.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-tasks"></i> View All Projects
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Client</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>001</strong></td>
                            <td>ABC Corporation</td>
                            <td>Business Cards</td>
                            <td><span class="status-badge status-in_progress">In Progress</span></td>
                            <td><span class="status-badge status-high">High</span></td>
                            <td>2024-01-20</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=1" class="btn btn-sm btn-primary">
                                        <i class="fas fa-pencil-alt"></i> Work On
                                    </a>
                                    <a href="job-details.php?id=1" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>002</strong></td>
                            <td>XYZ Company</td>
                            <td>Marketing Brochures</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td><span class="status-badge status-medium">Medium</span></td>
                            <td>2024-01-25</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=2" class="btn btn-sm btn-primary">
                                        <i class="fas fa-pencil-alt"></i> Work On
                                    </a>
                                    <a href="job-details.php?id=2" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>003</strong></td>
                            <td>Tech Startup</td>
                            <td>Product Catalog</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td><span class="status-badge status-low">Low</span></td>
                            <td>2024-01-15</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job-details.php?id=3" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stats-grid">
            <a href="my-projects.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-info">
                    <h3>15</h3>
                    <p>Total Projects</p>
                </div>
            </a>
            <a href="my-projects.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3>42</h3>
                    <p>Completed This Month</p>
                </div>
            </a>
            <a href="profile.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3>4.8/5</h3>
                    <p>Average Rating</p>
                </div>
            </a>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3>3.2 days</h3>
                    <p>Avg. Completion Time</p>
                </div>
            </div>
        </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
