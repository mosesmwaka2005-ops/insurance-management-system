<?php
require_once 'includes/config.php';
requireLogin();

$jobId = intval($_GET['id'] ?? 0);

// Get project from database
try {
    $userRole = getUserRole();
    
    // Different queries based on role
    if ($userRole === 'customer') {
        // Customers can only see their own jobs
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                j.title as name,
                j.paper_type as type,
                j.start_date as created,
                u.first_name,
                u.last_name,
                u.company
            FROM jobs j
            LEFT JOIN users u ON j.customer_id = u.id
            WHERE j.id = ? AND j.customer_id = ?
        ");
        $stmt->execute([$jobId, $_SESSION['user_id']]);
    } else {
        // Designers and managers can see all jobs
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                j.title as name,
                j.paper_type as type,
                j.start_date as created,
                u.first_name,
                u.last_name,
                u.company
            FROM jobs j
            LEFT JOIN users u ON j.customer_id = u.id
            WHERE j.id = ?
        ");
        $stmt->execute([$jobId]);
    }
    
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If project not found, redirect to my-projects
    if (!$project) {
        header('Location: my-projects.php');
        exit;
    }
    
    // Calculate days remaining
    $dueDate = new DateTime($project['due_date']);
    $today = new DateTime();
    $daysRemaining = $today->diff($dueDate)->days;
    if ($dueDate < $today) {
        $daysRemaining = -$daysRemaining;
    }
    
    // Calculate progress based on status
    $progressMap = [
        'pending' => 0,
        'in_progress' => 50,
        'review' => 75,
        'completed' => 100,
        'cancelled' => 0
    ];
    $project['progress'] = $progressMap[$project['status']] ?? 0;
    
    // Use total_cost from database or default
    $estimatedCost = $project['total_cost'] ?? 30000;
    
    // Get customer name
    $customerName = trim($project['first_name'] . ' ' . $project['last_name']);
    if (empty($customerName)) {
        $customerName = $project['company'] ?? 'Customer';
    }
    
} catch (PDOException $e) {
    error_log("Error fetching job details: " . $e->getMessage());
    header('Location: my-projects.php');
    exit;
}

$pageTitle = 'Job Details - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'arrow-left', 'label' => 'Back to Dashboard', 'active' => false],
    ['url' => 'job-details.php?id=' . $jobId, 'icon' => 'info-circle', 'label' => 'Job Details', 'active' => true],
];

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <header class="header">
            <div>
                <h1><?php echo htmlspecialchars($project['job_number']); ?> - <?php echo htmlspecialchars($project['name']); ?></h1>
                <p><?php echo htmlspecialchars($customerName); ?></p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="exportJob(<?php echo $jobId; ?>)">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-primary" onclick="window.location.href='my-projects.php'">
                    <i class="fas fa-edit"></i> Edit Job
                </button>
            </div>
        </header>

        <main class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-flag"></i></div>
                <div class="stat-info">
                    <h3><?php echo ucfirst($project['priority']); ?></h3>
                    <p>Priority</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?php echo abs($daysRemaining); ?></h3>
                    <p><?php echo $daysRemaining >= 0 ? 'Days Remaining' : 'Days Overdue'; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-info">
                    <h3><?php echo $project['progress']; ?>%</h3>
                    <p>Progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <h3>KES <?php echo number_format($estimatedCost); ?></h3>
                    <p>Total Cost</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-info-circle"></i> Job Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Client:</strong></label>
                    <p><?php echo htmlspecialchars($customerName); ?></p>
                </div>
                <div class="form-group">
                    <label><strong>Project Type:</strong></label>
                    <p><?php echo htmlspecialchars($project['type']); ?></p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Start Date:</strong></label>
                    <p><?php echo htmlspecialchars($project['created']); ?></p>
                </div>
                <div class="form-group">
                    <label><strong>Due Date:</strong></label>
                    <p><?php echo htmlspecialchars($project['due_date']); ?></p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Status:</strong></label>
                    <p>
                        <span class="status-badge status-<?php echo $project['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                        </span>
                    </p>
                </div>
                <div class="form-group">
                    <label><strong>Progress:</strong></label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                        <div class="progress-bar" style="width: 200px;">
                            <div class="progress-fill progress-primary" style="width: <?php echo $project['progress']; ?>%"></div>
                        </div>
                        <span style="font-size: 0.875rem; color: var(--gray);"><?php echo $project['progress']; ?>%</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label><strong>Description:</strong></label>
                <p><?php echo htmlspecialchars($project['description'] ?? 'Professional ' . strtolower($project['type']) . ' printing service with high-quality materials and finishing.'); ?></p>
            </div>
        </div>
        </main>
    </div>
</div>

<script>
function exportJob(jobId) {
    // Get project data from page
    const projectName = document.querySelector('h1').textContent.split(' - ')[1];
    const client = document.querySelectorAll('.form-group p')[0].textContent;
    const projectType = document.querySelectorAll('.form-group p')[1].textContent;
    const startDate = document.querySelectorAll('.form-group p')[2].textContent;
    const dueDate = document.querySelectorAll('.form-group p')[3].textContent;
    const status = document.querySelectorAll('.form-group p')[4].textContent.trim();
    const progress = document.querySelector('.stat-card:nth-child(3) h3').textContent;
    const priority = document.querySelector('.stat-card:nth-child(1) h3').textContent;
    const daysRemaining = document.querySelector('.stat-card:nth-child(2) h3').textContent;
    const totalCost = document.querySelector('.stat-card:nth-child(4) h3').textContent;
    const description = document.querySelectorAll('.form-group p')[6].textContent;
    
    // Create job details content
    const jobContent = `
TECHNA PRINT - JOB DETAILS EXPORT
==================================

Job ID: #${String(jobId).padStart(3, '0')}
Project: ${projectName}
Client: ${client}

JOB INFORMATION
---------------
Project Type: ${projectType}
Start Date: ${startDate}
Due Date: ${dueDate}
Priority: ${priority}
Progress: ${progress}
Total Cost: ${totalCost}

DESCRIPTION
-----------
${description}

STATUS
------
Days Remaining: ${daysRemaining}
Current Status: ${status}

Export Date: ${new Date().toLocaleString()}
    `;
    
    // Create blob and download
    const blob = new Blob([jobContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Job-${String(jobId).padStart(3, '0')}-Details.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // Show success message
    showToast('Job details exported successfully!', 'success');
}

function showToast(message, type = 'info') {
    // Remove existing toasts
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
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
