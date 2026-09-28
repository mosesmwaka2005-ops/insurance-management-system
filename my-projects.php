<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle project update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_project') {
    $projectId = intval($_POST['id'] ?? 0);
    $projectName = $_POST['name'] ?? '';
    $projectType = $_POST['type'] ?? '';
    $projectStatus = $_POST['status'] ?? '';
    $projectDueDate = $_POST['due_date'] ?? '';
    
    if ($projectId && $projectName && $projectType) {
        try {
            // Update job in database
            $stmt = $pdo->prepare("
                UPDATE jobs 
                SET title = ?, status = ?, due_date = ?, updated_at = NOW()
                WHERE id = ? AND customer_id = ?
            ");
            
            $stmt->execute([
                $projectName,
                $projectStatus,
                $projectDueDate,
                $projectId,
                $_SESSION['user_id']
            ]);
            
            if ($stmt->rowCount() > 0) {
                // Add timeline entry
                $timelineStmt = $pdo->prepare("
                    INSERT INTO job_timeline (job_id, user_id, activity_type, description)
                    VALUES (?, ?, 'updated', ?)
                ");
                $timelineStmt->execute([
                    $projectId,
                    $_SESSION['user_id'],
                    "Project updated: status changed to " . str_replace('_', ' ', $projectStatus)
                ]);
                
                $success = 'Project updated successfully!';
            } else {
                $error = 'Project not found or no changes made';
            }
        } catch (PDOException $e) {
            $error = 'Error updating project. Please try again.';
            error_log("Project update error: " . $e->getMessage());
        }
    } else {
        $error = 'Invalid project data';
    }
}

$pageTitle = 'My Projects - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$searchPlaceholder = 'Search projects...';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => false],
    ['url' => 'new-project.php', 'icon' => 'plus-circle', 'label' => 'New Project', 'active' => false],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'My Projects', 'active' => true],
    ['url' => 'my-quotes.php', 'icon' => 'file-invoice', 'label' => 'My Quotes', 'active' => false],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => false],
];

// Get user projects from database
try {
    $userRole = getUserRole();
    
    // Different queries based on role
    if ($userRole === 'customer') {
        // Customers see only their own projects
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.title as name,
                j.job_number,
                j.status,
                j.priority,
                j.start_date as created,
                j.due_date,
                j.total_cost as estimated_cost,
                j.quantity,
                COALESCE(j.paper_type, 'Standard') as type
            FROM jobs j
            WHERE j.customer_id = ?
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } elseif ($userRole === 'designer') {
        // Designers see projects assigned to them
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.title as name,
                j.job_number,
                j.status,
                j.priority,
                j.start_date as created,
                j.due_date,
                j.total_cost as estimated_cost,
                j.quantity,
                COALESCE(j.paper_type, 'Standard') as type
            FROM jobs j
            WHERE j.designer_id = ? OR j.designer_id IS NULL
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        // Managers see all projects
        $stmt = $pdo->query("
            SELECT 
                j.id,
                j.title as name,
                j.job_number,
                j.status,
                j.priority,
                j.start_date as created,
                j.due_date,
                j.total_cost as estimated_cost,
                j.quantity,
                COALESCE(j.paper_type, 'Standard') as type
            FROM jobs j
            ORDER BY j.created_at DESC
        ");
    }
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add calculated fields to each project
    foreach ($projects as &$project) {
        // Calculate days remaining
        $dueDate = new DateTime($project['due_date']);
        $today = new DateTime();
        $daysRemaining = $today->diff($dueDate)->days;
        if ($dueDate < $today) {
            $daysRemaining = -$daysRemaining;
        }
        $project['days_remaining'] = $daysRemaining;
        
        // Calculate progress based on status
        $progressMap = [
            'pending' => 0,
            'in_progress' => 50,
            'review' => 75,
            'completed' => 100,
            'cancelled' => 0
        ];
        $project['progress'] = $progressMap[$project['status']] ?? 0;
        
        // Ensure estimated_cost is set
        if (!$project['estimated_cost']) {
            $project['estimated_cost'] = 30000;
        }
    }
    unset($project); // Break reference
    
} catch (PDOException $e) {
    error_log("Error fetching projects: " . $e->getMessage());
    $projects = [];
}

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <?php include 'includes/dashboard-header.php'; ?>
        
        <main class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tasks"></i> All Projects</h3>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <select class="search-input" style="width: 200px;" onchange="filterProjects(this.value)">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <a href="new-project.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Project
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Project ID</th>
                                <th>Project Name</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Days Left</th>
                                <th>Est. Cost</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($projects)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2rem; color: var(--gray);">
                                    <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    <p>No projects found. <a href="new-project.php">Create your first project</a></p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['job_number'] ?? '#' . str_pad($project['id'], 3, '0', STR_PAD_LEFT)); ?></td>
                                <td><strong><?php echo htmlspecialchars($project['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($project['type']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($project['priority']); ?>">
                                        <?php echo ucfirst($project['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $project['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="progress-bar" style="width: 100px;">
                                            <div class="progress-fill progress-primary" style="width: <?php echo $project['progress']; ?>%"></div>
                                        </div>
                                        <span style="font-size: 0.875rem; color: var(--gray);"><?php echo $project['progress']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: <?php echo $project['days_remaining'] < 0 ? 'var(--danger)' : ($project['days_remaining'] <= 3 ? 'var(--warning)' : 'var(--success)'); ?>; font-weight: 600;">
                                        <?php 
                                        if ($project['days_remaining'] < 0) {
                                            echo abs($project['days_remaining']) . ' overdue';
                                        } else {
                                            echo $project['days_remaining'] . ' days';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><strong>KES <?php echo number_format($project['estimated_cost']); ?></strong></td>
                                <td><?php echo $project['due_date']; ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="job-details.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($project['status'] !== 'completed'): ?>
                                        <button class="btn btn-sm btn-outline" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Project Statistics</h3>
                    <button class="btn btn-sm btn-outline" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="stats-grid">
                    <div class="stat-card" onclick="filterProjects('all')" style="cursor: pointer;">
                        <div class="stat-icon"><i class="fas fa-folder"></i></div>
                        <div class="stat-info">
                            <h3><?php echo count($projects); ?></h3>
                            <p>Total Projects</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterProjects('in_progress')" style="cursor: pointer;">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($projects, fn($p) => $p['status'] === 'in_progress')); ?></h3>
                            <p>In Progress</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterProjects('completed')" style="cursor: pointer;">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($projects, fn($p) => $p['status'] === 'completed')); ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterProjects('pending')" style="cursor: pointer;">
                        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="stat-info">
                            <h3><?php echo count(array_filter($projects, fn($p) => $p['status'] === 'pending')); ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal" id="editProjectModal" aria-hidden="true">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-edit"></i> Edit Project</h3>
            <button class="close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editProjectForm">
                <input type="hidden" id="editProjectId">
                
                <div class="form-group">
                    <label for="editProjectName"><i class="fas fa-file-alt"></i> Project Name</label>
                    <input type="text" id="editProjectName" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editProjectType"><i class="fas fa-tag"></i> Type</label>
                        <select id="editProjectType" class="form-control" required>
                            <option value="Cards">Business Cards</option>
                            <option value="Brochure">Brochure</option>
                            <option value="Booklet">Booklet</option>
                            <option value="Flyer">Flyer</option>
                            <option value="Banner">Banner</option>
                            <option value="Poster">Poster</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editProjectStatus"><i class="fas fa-info-circle"></i> Status</label>
                        <select id="editProjectStatus" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editProjectProgress"><i class="fas fa-percentage"></i> Progress (%)</label>
                        <input type="number" id="editProjectProgress" class="form-control" min="0" max="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editProjectDueDate"><i class="fas fa-calendar"></i> Due Date</label>
                        <input type="date" id="editProjectDueDate" class="form-control" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeEditModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" onclick="saveProjectChanges()">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
let currentEditingProject = null;

function filterProjects(status) {
    const statusSelect = document.querySelector('.search-input');
    if (statusSelect) {
        statusSelect.value = status;
        
        // Filter table rows
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge && statusBadge.classList.contains('status-' + status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        // Scroll to table
        document.querySelector('.table-responsive').scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        showToast(`Filtered to show ${status === 'all' ? 'all' : status.replace('_', ' ')} projects`, 'info');
    }
}

function refreshStats() {
    showToast('Statistics refreshed!', 'success');
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function openEditModal(project) {
    currentEditingProject = project;
    
    // Populate form fields
    document.getElementById('editProjectId').value = project.id;
    document.getElementById('editProjectName').value = project.name;
    document.getElementById('editProjectType').value = project.type;
    document.getElementById('editProjectStatus').value = project.status;
    document.getElementById('editProjectProgress').value = project.progress;
    document.getElementById('editProjectDueDate').value = project.due_date;
    
    // Show modal
    const modal = document.getElementById('editProjectModal');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
}

function closeEditModal() {
    const modal = document.getElementById('editProjectModal');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    currentEditingProject = null;
}

function saveProjectChanges() {
    const form = document.getElementById('editProjectForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const projectId = parseInt(document.getElementById('editProjectId').value);
    const projectData = {
        id: projectId,
        name: document.getElementById('editProjectName').value,
        type: document.getElementById('editProjectType').value,
        status: document.getElementById('editProjectStatus').value,
        progress: parseInt(document.getElementById('editProjectProgress').value),
        due_date: document.getElementById('editProjectDueDate').value
    };
    
    // Send to server via form submission
    const saveForm = document.createElement('form');
    saveForm.method = 'POST';
    saveForm.action = 'my-projects.php';
    
    // Add action field
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'update_project';
    saveForm.appendChild(actionInput);
    
    // Add all project data
    Object.keys(projectData).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = projectData[key];
        saveForm.appendChild(input);
    });
    
    document.body.appendChild(saveForm);
    saveForm.submit();
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

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

// Close modal when clicking outside
document.getElementById('editProjectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
