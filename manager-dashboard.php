<?php
require_once 'includes/config.php';
requireRole('manager');

// Get manager statistics from database
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs");
    $stmt->execute();
    $totalJobs = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'in_progress'");
    $stmt->execute();
    $inProgress = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'completed'");
    $stmt->execute();
    $completed = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'pending'");
    $stmt->execute();
    $pendingQuotes = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_cost), 0) FROM jobs WHERE status = 'completed' AND MONTH(completed_date) = MONTH(CURRENT_DATE())");
    $stmt->execute();
    $monthlyRevenue = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $stmt->execute();
    $activeClients = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'designer'");
    $stmt->execute();
    $teamMembers = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching manager stats: " . $e->getMessage());
    $totalJobs = 0;
    $inProgress = 0;
    $completed = 0;
    $pendingQuotes = 0;
    $monthlyRevenue = 0;
    $activeClients = 0;
    $teamMembers = 0;
}

$pageTitle = 'Manager Dashboard - ' . SITE_NAME;
$sidebarTitle = 'Techna Manager';
$sidebarIcon = 'chart-line';
$searchPlaceholder = 'Search reports, users...';
$menuItems = [
    ['url' => 'manager-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => true],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'All Projects', 'active' => false],
    ['url' => 'my-quotes.php', 'icon' => 'file-invoice', 'label' => 'Quotes', 'active' => false],
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
                <div class="job-status-label">Total Jobs</div>
                <div class="job-status-value"><?php echo $totalJobs; ?></div>
                <div class="job-status-icon">📊</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">In Progress</div>
                <div class="job-status-value"><?php echo $inProgress; ?></div>
                <div class="job-status-icon">⏳</div>
            </a>
            <a href="my-projects.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Completed</div>
                <div class="job-status-value"><?php echo $completed; ?></div>
                <div class="job-status-icon">✅</div>
            </a>
            <a href="my-quotes.php" class="job-status-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="job-status-label">Pending Quotes</div>
                <div class="job-status-value"><?php echo $pendingQuotes; ?></div>
                <div class="job-status-icon">💰</div>
            </a>
        </div>

        <div class="stats-grid">
            <a href="my-quotes.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <h3>KES <?php echo number_format($monthlyRevenue); ?></h3>
                    <p>Revenue This Month</p>
                </div>
            </a>
            <a href="my-projects.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalJobs; ?></h3>
                    <p>Active Jobs</p>
                </div>
            </a>
            <a href="my-projects.php" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3><?php echo $completed; ?></h3>
                    <p>Completed Jobs</p>
                </div>
            </a>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $teamMembers; ?></h3>
                    <p>Active Designers</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Active Designers</h3>
                <a href="profile.php" class="btn btn-sm btn-outline">
                    <i class="fas fa-user-plus"></i> Manage Team
                </a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Designer</th>
                            <th>Active Projects</th>
                            <th>Completed</th>
                            <th>Status</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">JD</div>
                                    <strong>John Designer</strong>
                                </div>
                            </td>
                            <td>5</td>
                            <td>42</td>
                            <td><span class="status-badge status-in_progress">Active</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 95%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">95%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">SM</div>
                                    <strong>Sarah Miller</strong>
                                </div>
                            </td>
                            <td>3</td>
                            <td>38</td>
                            <td><span class="status-badge status-in_progress">Active</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 92%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">92%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">MJ</div>
                                    <strong>Mike Johnson</strong>
                                </div>
                            </td>
                            <td>4</td>
                            <td>35</td>
                            <td><span class="status-badge status-in_progress">Active</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill progress-primary" style="width: 88%"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: var(--gray);">88%</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> System Overview</h3>
            <div class="form-row" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label><strong><i class="fas fa-server"></i> System Status:</strong></label>
                    <p><span class="status-badge status-completed">Operational</span></p>
                </div>
                <div class="form-group">
                    <label><strong><i class="fas fa-database"></i> Database:</strong></label>
                    <p><span class="status-badge status-completed">Connected</span></p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><strong><i class="fas fa-clock"></i> Last Backup:</strong></label>
                    <p>2024-01-15 03:00 AM</p>
                </div>
                <div class="form-group">
                    <label><strong><i class="fas fa-hdd"></i> Storage Used:</strong></label>
                    <p>2.4 GB / 50 GB</p>
                </div>
            </div>
            <div class="form-group">
                <label><strong><i class="fas fa-chart-line"></i> System Performance:</strong></label>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                    <div class="progress-bar" style="width: 100%; max-width: 400px;">
                        <div class="progress-fill progress-primary" style="width: 78%"></div>
                    </div>
                    <span style="font-size: 0.875rem; color: var(--gray);">78% Efficiency</span>
                </div>
            </div>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p style="color: var(--gray); margin: 0;">
                    <i class="fas fa-info-circle"></i> All systems operational. Manage all operations from this central dashboard.
                </p>
            </div>
        </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
