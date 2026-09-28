<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'New Project - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'arrow-left', 'label' => 'Back to Dashboard', 'active' => false],
    ['url' => 'new-project.php', 'icon' => 'plus-circle', 'label' => 'New Project', 'active' => true],
];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = $_POST['project_name'] ?? '';
    $projectType = $_POST['project_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $dueDate = $_POST['due_date'] ?? '';
    
    if (empty($projectName) || empty($projectType)) {
        $error = 'Project name and type are required';
    } else {
        try {
            // Generate job number
            $jobNumber = 'JOB-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO jobs (job_number, customer_id, title, description, status, priority, 
                                 start_date, due_date, quantity, created_at) 
                VALUES (?, ?, ?, ?, 'pending', 'medium', NOW(), ?, ?, NOW())
            ");
            
            $dueDate = $dueDate ?: date('Y-m-d', strtotime('+7 days'));
            
            $stmt->execute([
                $jobNumber,
                $_SESSION['user_id'],
                $projectName,
                $description,
                $dueDate,
                $quantity
            ]);
            
            $success = 'Project created successfully! Redirecting...';
            header("refresh:2;url=my-projects.php");
        } catch (PDOException $e) {
            $error = 'Error creating project. Please try again.';
            error_log("Project creation error: " . $e->getMessage());
        }
    }
}

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <header class="header">
            <div>
                <h1>Create New Project</h1>
                <p>Submit your print job details</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo getUserRole(); ?>-dashboard.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </header>
        
        <main class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h3><i class="fas fa-file-alt"></i> Project Information</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_name">Project Name *</label>
                            <input type="text" id="project_name" name="project_name" placeholder="e.g., Business Cards" required>
                        </div>
                        <div class="form-group">
                            <label for="project_type">Project Type *</label>
                            <select id="project_type" name="project_type" required>
                                <option value="">Select type...</option>
                                <option value="business_cards">Business Cards</option>
                                <option value="brochures">Brochures</option>
                                <option value="flyers">Flyers</option>
                                <option value="posters">Posters</option>
                                <option value="banners">Banners</option>
                                <option value="catalogs">Catalogs</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" placeholder="e.g., 500" min="1">
                        </div>
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Provide details about your project..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="file_upload">Upload Files (Optional)</label>
                        <div class="file-upload" id="dropZone">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                            <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>Drag and drop files here</strong></p>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;">or</p>
                            <input type="file" id="file_upload" name="files[]" multiple style="display: none;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ai,.psd">
                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('file_upload').click()">
                                <i class="fas fa-folder-open"></i> Browse Files
                            </button>
                            <p style="color: var(--gray); font-size: 0.85rem; margin-top: 1rem;">
                                Supported: PDF, JPG, PNG, DOC, AI, PSD (Max 10MB per file)
                            </p>
                        </div>
                        <div id="fileList" style="margin-top: 1rem;"></div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Create Project
                        </button>
                        <a href="<?php echo getUserRole(); ?>-dashboard.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
let uploadedFiles = [];

// Get elements
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('file_upload');
const fileList = document.getElementById('fileList');

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop zone when dragging over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.style.borderColor = 'var(--primary)';
    dropZone.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
}

function unhighlight(e) {
    dropZone.style.borderColor = 'var(--border)';
    dropZone.style.backgroundColor = 'transparent';
}

// Handle dropped files
dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

// Handle file input change
fileInput.addEventListener('change', function(e) {
    handleFiles(this.files);
});

function handleFiles(files) {
    [...files].forEach(file => {
        // Check file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showToast(`File "${file.name}" is too large. Maximum size is 10MB.`, 'error');
            return;
        }
        
        // Check if file already added
        if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
            showToast(`File "${file.name}" is already added.`, 'warning');
            return;
        }
        
        uploadedFiles.push(file);
        displayFile(file);
    });
    
    if (files.length > 0) {
        showToast(`${files.length} file(s) added successfully!`, 'success');
    }
}

function displayFile(file) {
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    fileItem.style.cssText = `
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--light);
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid var(--border);
    `;
    
    const fileIcon = getFileIcon(file.name);
    const fileSize = formatFileSize(file.size);
    
    fileItem.innerHTML = `
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <div style="font-size: 2rem;">${fileIcon}</div>
            <div style="flex: 1;">
                <div style="font-weight: 600; color: var(--dark);">${file.name}</div>
                <div style="font-size: 0.85rem; color: var(--gray);">${fileSize}</div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline" onclick="removeFile('${file.name}', ${file.size})" style="color: var(--danger);">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    fileList.appendChild(fileItem);
}

function removeFile(fileName, fileSize) {
    uploadedFiles = uploadedFiles.filter(f => !(f.name === fileName && f.size === fileSize));
    updateFileList();
    showToast('File removed', 'info');
}

function updateFileList() {
    fileList.innerHTML = '';
    uploadedFiles.forEach(file => displayFile(file));
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': '📄',
        'doc': '📝',
        'docx': '📝',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️',
        'ai': '🎨',
        'psd': '🎨',
        'zip': '📦'
    };
    return icons[ext] || '📎';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function showToast(message, type = 'info') {
    document.querySelectorAll('.toast').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} show`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
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

// Form submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (uploadedFiles.length > 0) {
        console.log('Files to upload:', uploadedFiles);
        // In a real application, you would upload these files via AJAX/FormData
        showToast(`Project created with ${uploadedFiles.length} file(s)!`, 'success');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
