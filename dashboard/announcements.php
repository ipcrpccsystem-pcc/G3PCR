<?php
session_start();
include '../db/connection.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
// Handle Profile Data
$profilePicture = isset($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== ''
    ? '../uploads/' . $_SESSION['profile_picture']
    : '../uploads/1.png';
$fullName = $_SESSION['full_name'] ?? 'Admin';
$email = $_SESSION['email'] ?? '';
// Updated Notification Count
$notificationCount = 0;
$sql = "SELECT COUNT(*) as unread FROM admindashboard_notification WHERE status = 'unread'";
$result = mysqli_query($conn, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $notificationCount = $row['unread'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements - IPCR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --bg-light: #f0f2f5;
            --text-light: #333;
            --card-light: #ffffff;
            --sidebar-bg-light: #3a6ea5;
            --bg-dark: #1e1e2f;
            --text-dark: #dcdcdc;
            --card-dark: #2c2c3e;
            --sidebar-bg-dark: #252536;
        }
        html[data-theme='dark'] body {
            background-color: var(--bg-dark);
            color: var(--text-dark);
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-light);
        }
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background-color: var(--sidebar-bg-light);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
            z-index: 1000;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        [data-theme='dark'] .topbar {
            background-color: var(--sidebar-bg-dark);
        }
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg-light);
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            margin-top: 70px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 999;
        }
        [data-theme='dark'] .sidebar {
            background-color: var(--sidebar-bg-dark);
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin: 5px 0;
            padding: 12px 10px;
            border-radius: 5px;
        }
        .sidebar a i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: white;
            color: #3a6ea5;
            font-weight: bold;
        }
        .main-content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .sidebar-footer {
            margin-top: auto;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
        }
        .notification-bell {
            position: relative;
            cursor: pointer;
            color: white;
        }
        .notification-bell .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        .dark-toggle {
            cursor: pointer;
            font-size: 1.2rem;
            color: white;
        }
        .settings {
            position: relative;
            display: inline-block;
        }
        .settings-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 60px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            min-width: 280px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            z-index: 1050;
        }
        [data-theme='dark'] .settings-menu {
            background-color: #2e2e2e;
            color: white;
        }
        .settings-header {
            background-color: #3a6ea5;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .settings-menu a {
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            border-bottom: 1px solid #f1f1f1;
        }
        .settings-menu a:hover {
            background-color: #f7f7f7;
        }
        [data-theme='dark'] .settings-menu a {
            color: white;
            border-bottom: 1px solid #444;
        }
        [data-theme='dark'] .settings-menu a:hover {
            background-color: #3b3b3b;
        }
        .profile-img-sm {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ccc;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin: 10px auto;
        }
        .pcc-logo {
            width: 40px;
            height: auto;
            border-radius: 10px;
        }
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Page-specific styles */
        .announcement-card {
            border-left: 5px solid #0d6efd;
            background: var(--card-light);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: 0.3s ease-in-out;
        }
        [data-theme='dark'] .announcement-card {
            background: var(--card-dark);
        }
        .announcement-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .announcement-title {
            font-weight: 600;
            font-size: 1.3rem;
        }
        .announcement-meta {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        [data-theme='dark'] .announcement-meta {
            color: var(--text-dark);
        }
        .modal-header {
            background: #0d6efd;
            color: white;
        }
        a:hover {
            text-decoration: underline;
            color: #084298;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .announcement-title {
                font-size: 1.2rem;
            }
            .announcement-meta {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .menu-toggle {
                display: block;
            }
            .topbar {
                padding: 0 10px;
            }
            .profile-img-sm {
                width: 30px;
                height: 30px;
            }
            .pcc-logo {
                width: 35px;
            }
            .settings-menu {
                min-width: 250px;
            }
            .announcement-card {
                padding: 15px;
            }
            .announcement-title {
                font-size: 1.1rem;
            }
            .announcement-meta {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .announcement-card {
                padding: 12px;
            }
            .announcement-title {
                font-size: 1rem;
            }
            .announcement-meta {
                font-size: 0.75rem;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .btn-primary {
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="topbar">
    <div style="display: flex; align-items: center;">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <img src="../images/pcc1.png" alt="PCC Logo" class="pcc-logo">
    </div>
    <div class="topbar-right">
        <a href="notifications.php" class="notification-bell">
            <i class="fas fa-bell fa-lg"></i>
            <?php if ($notificationCount > 0): ?>
                <span class="badge"><?= $notificationCount ?></span>
            <?php endif; ?>
        </a>
        <span class="dark-toggle" id="toggleTheme" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </span>
        <div class="settings" id="profileSettings">
            <img src="<?= $profilePicture ?>" class="profile-img-sm" alt="Profile">
            <div class="settings-menu">
                <div class="settings-header">
                    <img src="<?= $profilePicture ?>" class="profile-img" alt="Profile">
                    <h6><?= htmlspecialchars($fullName) ?></h6>
                    <small><?= htmlspecialchars($email) ?></small>
                </div>
              <a href="my_account.php"><i class="fas fa-user-edit me-2"></i> My Account</a>
              <a href="admin_view_profile.php"><i class="fas fa-eye me-2"></i> View Profile</a>
              <a href="Change_Password.php"><i class="fas fa-key me-2"></i> Change Password</a>
              <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
<div class="sidebar" id="sidebar">
    <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i> Home</a>
    <a href="manage_users.php"><i class="fas fa-users me-2"></i> Manage Users</a>
    <a href="department_reports.php"><i class="fas fa-layer-group me-2"></i> Department Reports</a>
    <a href="announcements.php" class="active"><i class="fas fa-bullhorn me-2"></i> Announcements</a>
    <a href="view_ipcr_submissions.php"><i class="fas fa-file-alt me-2"></i> View Submissions</a>
    <a href="deadline_settings.php"><i class="fas fa-clock me-2"></i> Deadline Settings</a>
    <a href="status_report.php"><i class="fas fa-clipboard-list me-2"></i> Status Report</a>
    <a href="recycle_bin.php"><i class="fas fa-trash-alt me-2"></i> Recycle Bin</a>
    <a href="about.php"><i class="fas fa-info-circle me-2"></i> About</a>
</div>

<div class="main-content">
        
    <!-- Announcements Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fas fa-bullhorn"></i> Announcements</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus-circle"></i> Add Announcement
        </button>
    </div>
    
    <?php
    $query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
    if (mysqli_num_rows($query) > 0):
        while ($row = mysqli_fetch_assoc($query)): ?>
            <div class="announcement-card">
                <div class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="announcement-meta mb-2">
                    📅 Posted: <?php echo date("F d, Y", strtotime($row['created_at'])); ?> &nbsp; | 
                    ⏳ Deadline: <span class="text-danger"><?php echo date("F d, Y", strtotime($row['deadline'])); ?></span>
                </div>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                <form method="POST" action="delete_announcement.php" onsubmit="return confirm('Delete this announcement?');">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger mt-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        <?php endwhile;
    else:
        echo "<div class='alert alert-info'>No announcements posted yet.</div>";
    endif;
    ?>
</div>

<!-- Modal: Add Announcement -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="add_announcement.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel"><i class="fas fa-plus-circle"></i> Add Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter announcement title" required>
                    </div>
                    <div class="mb-3">
                        <label>Content</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Enter announcement content" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Deadline</label>
                        <input type="date" name="deadline" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Post Announcement</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Theme toggle
    toggleTheme.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    });
    
    // Profile settings dropdown
    const profileSettings = document.getElementById("profileSettings");
    const settingsMenu = profileSettings.querySelector(".settings-menu");
    
    profileSettings.addEventListener("click", function (e) {
        e.stopPropagation();
        settingsMenu.style.display = settingsMenu.style.display === "block" ? "none" : "block";
    });
    
    document.addEventListener("click", function (e) {
        if (!profileSettings.contains(e.target)) {
            settingsMenu.style.display = "none";
        }
    });
    
    // Sidebar toggle for mobile
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");
    
    menuToggle.addEventListener("click", function (e) {
        e.stopPropagation();
        sidebar.classList.toggle("active");
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function (e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove("active");
        }
    });
    
    // Handle window resize
    window.addEventListener("resize", function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove("active");
        }
    });
</script>
</body>
</html>