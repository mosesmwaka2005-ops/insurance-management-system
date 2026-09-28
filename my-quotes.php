<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'My Quotes - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => false],
    ['url' => 'new-project.php', 'icon' => 'plus-circle', 'label' => 'New Project', 'active' => false],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'My Projects', 'active' => false],
    ['url' => 'my-quotes.php', 'icon' => 'file-invoice', 'label' => 'My Quotes', 'active' => true],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => false],
];

include 'includes/header.php';
?>

<div class="dashboard">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="dashboard-main">
        <?php include 'includes/dashboard-header.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-file-invoice-dollar"></i> My Quotes</h3>
                    <a href="new-project.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Request Quote
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Quote ID</th>
                                <th>Project</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Valid Until</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Q-001</td>
                                <td>Business Cards</td>
                                <td>KES 31,850</td>
                                <td><span class="status-badge status-completed">Accepted</span></td>
                                <td>2024-01-25</td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="downloadQuote('Q-001', 'Business Cards')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Q-002</td>
                                <td>Marketing Brochures</td>
                                <td>KES 58,500</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>2024-01-30</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="acceptQuote('Q-002', this)">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                    <button class="btn btn-sm btn-outline" onclick="downloadQuote('Q-002', 'Marketing Brochures')" style="margin-left: 0.5rem;">
                                        <i class="fas fa-download"></i> View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function downloadQuote(quoteId, projectName) {
    // Create a simple PDF-like content
    const quoteContent = `
TECHNA PRINT - QUOTE DOCUMENT
================================

Quote ID: ${quoteId}
Project: ${projectName}
Date: ${new Date().toLocaleDateString()}

Thank you for your business!

This is a demo quote document.
In a real application, this would be a proper PDF file.
    `;
    
    // Create a blob and download it
    const blob = new Blob([quoteContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Quote-${quoteId}-${projectName.replace(/\s+/g, '-')}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // Show success message
    showToast('Quote downloaded successfully!', 'success');
}

function acceptQuote(quoteId, button) {
    if (confirm('Are you sure you want to accept this quote? This action will create a project.')) {
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Simulate API call
        setTimeout(() => {
            // Update the row
            const row = button.closest('tr');
            const statusCell = row.querySelector('.status-badge');
            statusCell.className = 'status-badge status-completed';
            statusCell.textContent = 'Accepted';
            
            // Replace accept button with download button
            const actionsCell = row.querySelector('td:last-child');
            actionsCell.innerHTML = `
                <button class="btn btn-sm btn-outline" onclick="downloadQuote('${quoteId}', 'Marketing Brochures')">
                    <i class="fas fa-download"></i> Download
                </button>
            `;
            
            showToast('Quote accepted successfully! Project has been created.', 'success');
        }, 1000);
    }
}

function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'toast-error' : ''} show`;
    toast.innerHTML = `
        <div class="toast-content">
            <span><i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
