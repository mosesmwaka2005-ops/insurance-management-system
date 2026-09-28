<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Notifications - ' . SITE_NAME;
$sidebarTitle = 'Techna Print';
$sidebarIcon = 'print';
$searchPlaceholder = 'Search notifications...';
$menuItems = [
    ['url' => getUserRole() . '-dashboard.php', 'icon' => 'home', 'label' => 'Dashboard', 'active' => false],
    ['url' => 'new-project.php', 'icon' => 'plus-circle', 'label' => 'New Project', 'active' => false],
    ['url' => 'my-projects.php', 'icon' => 'tasks', 'label' => 'My Projects', 'active' => false],
    ['url' => 'my-quotes.php', 'icon' => 'file-invoice', 'label' => 'My Quotes', 'active' => false],
    ['url' => 'profile.php', 'icon' => 'user', 'label' => 'Profile', 'active' => false],
];

// Sample notifications
$notifications = [
    ['id' => 1, 'type' => 'success', 'title' => 'Project Completed', 'message' => 'Your business cards are ready for review', 'time' => '5 minutes ago', 'read' => false, 'link' => 'job-details.php?id=1'],
    ['id' => 2, 'type' => 'warning', 'title' => 'Approval Needed', 'message' => 'Design proof requires your approval', 'time' => '1 hour ago', 'read' => false, 'link' => 'job-details.php?id=2'],
    ['id' => 3, 'type' => 'info', 'title' => 'New Quote Available', 'message' => 'You have received a new quote for your project', 'time' => '3 hours ago', 'read' => true, 'link' => 'my-quotes.php'],
    ['id' => 4, 'type' => 'success', 'title' => 'Payment Received', 'message' => 'Payment of KES 31,850 has been confirmed', 'time' => '1 day ago', 'read' => true, 'link' => 'my-quotes.php'],
    ['id' => 5, 'type' => 'info', 'title' => 'Project Started', 'message' => 'Your brochure project has been assigned to a designer', 'time' => '2 days ago', 'read' => true, 'link' => 'job-details.php?id=2'],
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
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <button class="btn btn-outline btn-sm" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </div>
                
                <div class="notifications-page-list">
                    <?php foreach ($notifications as $notif): ?>
                    <a href="<?php echo $notif['link']; ?>" class="notification-page-item <?php echo !$notif['read'] ? 'unread' : ''; ?>" style="text-decoration: none; color: inherit;">
                        <div class="notification-icon-large" style="background: var(--<?php echo $notif['type'] === 'success' ? 'success' : ($notif['type'] === 'warning' ? 'warning' : 'primary'); ?>);">
                            <i class="fas fa-<?php echo $notif['type'] === 'success' ? 'check-circle' : ($notif['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                        </div>
                        <div class="notification-content-large">
                            <div class="notification-header-row">
                                <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                <?php if (!$notif['read']): ?>
                                    <span class="unread-dot"></span>
                                <?php endif; ?>
                            </div>
                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                            <small style="color: var(--gray);"><?php echo $notif['time']; ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.notifications-page-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.notification-page-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.notification-page-item:hover {
    background: var(--background);
}

.notification-page-item.unread {
    background: rgba(99, 102, 241, 0.05);
}

.notification-icon-large {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.notification-content-large {
    flex: 1;
}

.notification-header-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.unread-dot {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
}

.notification-content-large p {
    margin: 0.5rem 0;
    color: var(--text);
}
</style>

<script>
function markAllAsRead() {
    const unreadItems = document.querySelectorAll('.notification-page-item.unread');
    unreadItems.forEach(item => {
        item.classList.remove('unread');
    });
    
    const unreadDots = document.querySelectorAll('.unread-dot');
    unreadDots.forEach(dot => {
        dot.style.display = 'none';
    });
    
    alert('All notifications marked as read!');
}

// Fix sidebar links
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notifications page - fixing sidebar links...');
    
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
});
</script>

<?php include 'includes/footer.php'; ?>
