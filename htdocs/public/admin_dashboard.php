<?php
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/ReportItem.php';
require_once __DIR__ . '/../classes/Item.php';

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

// Initialize classes
$admin = new Admin();
$student = new Student();
$reportItem = new ReportItem();
$item = new Item();

// Get dashboard statistics
$stats = $admin->getDashboardStats();

// Get pending approvals
$pendingApprovals = $admin->getPendingApprovals();

// Get completed approvals
$completedApprovals = $admin->getCompletedApprovals();

// Get all admins
$admins = $admin->getAllAdmins();

// Section logic
$section = $_GET['section'] ?? 'pending';
function sidebar_active($sec, $current) { return $sec === $current ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - UB Lost & Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/ub.css" rel="stylesheet">
  <link href="../assets/admin_dashboard.css" rel="stylesheet">
  <link href="../assets/notifications.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="admin-topbar">
  <div class="d-flex align-items-center gap-2">
    <span class="ub-logo-3d me-2">UB</span>
    <span class="fw-bold fs-4">UB Lost & Found Admin</span>
  </div>
  <div>
    <span class="me-3">👤 <?php echo htmlspecialchars($_SESSION['admin']['AdminName'] ?? ''); ?></span>
    <a href="admin_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</div>
<div class="container-fluid">
  <div class="row g-4">
    <div class="col-md-2">
      <div class="admin-sidebar">
        <a href="admin_dashboard.php?section=pending" class="<?php echo sidebar_active('pending', $section); ?>">Pending</a>
        <a href="admin_dashboard.php?section=completed" class="<?php echo sidebar_active('completed', $section); ?>">Completed</a>
        <a href="admin_dashboard.php?section=adminmgmt" class="<?php echo sidebar_active('adminmgmt', $section); ?>">Admin Management</a>
        <a href="admin_dashboard.php?section=settings" class="<?php echo sidebar_active('settings', $section); ?>">Settings</a>
      </div>
    </div>
    <div class="col-md-10">
      <div class="admin-header text-center mb-4">
        <h1 class="fw-bold">Admin Dashboard</h1>
        <p class="lead">UB Lost & Found</p>
      </div>
      <?php if (!empty($_SESSION['admin_msg'])): ?>
        <div class="alert alert-info text-center mb-4"><?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?></div>
      <?php endif; ?>
      <?php if ($section === 'pending'): ?>
        <!-- Pending Section -->
        <div class="container mb-5">
          <div class="row g-4 mb-4">
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Profile Photos</h5>
                <div class="display-6 fw-bold"><?php echo $stats['pendingPhotoApprovals'] ?? 0; ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Lost Reports</h5>
                <div class="display-6 fw-bold"><?php echo $stats['pendingLostApprovals'] ?? 0; ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-4 admin-stat">
                <h5 class="mb-1">Pending Found Reports</h5>
                <div class="display-6 fw-bold"><?php echo $stats['pendingFoundApprovals'] ?? 0; ?></div>
              </div>
            </div>
          </div>
          <div class="row g-4 mb-4">
            <div class="col-md-4">
              <div class="admin-card p-3">
                <h5 class="mb-3">Profile Photo Confirmations</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Name</th><th>Email</th><th>Photo</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($pendingApprovals['photos'] as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['StudentName'] ?? $row['AdminName'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['Email'] ?? ''); ?></td>
                      <td><?php if (!empty($row['ProfilePhoto'])): ?><img src="../<?php echo htmlspecialchars($row['ProfilePhoto']); ?>" alt="Photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="photo">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['StudentNo'] ?? $row['StudentID'] ?? ''); ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="photo">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['StudentNo'] ?? $row['StudentID'] ?? ''); ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-8">
              <div class="admin-card p-3 mb-4">
                <h5 class="mb-3">Lost Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Student No</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($pendingApprovals['lostItems'] as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['Description'] ?? ''); ?></td>
                      <td><?php if (!empty($row['PhotoURL'])): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td><?php echo htmlspecialchars($row['StudentNo'] ?? ''); ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="lost">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ReportID'] ?? ''); ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="lost">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ReportID'] ?? ''); ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="admin-card p-3">
                <h5 class="mb-3">Found Item Reports</h5>
                <table class="table admin-table table-sm">
                  <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($pendingApprovals['foundItems'] as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['ItemName'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['Description'] ?? ''); ?></td>
                      <td><?php if (!empty($row['PhotoURL'])): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                      <td>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="found">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ItemID'] ?? ''); ?>">
                          <button class="btn btn-admin-approve btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="admin_action.php" style="display:inline">
                          <input type="hidden" name="type" value="found">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ItemID'] ?? ''); ?>">
                          <button class="btn btn-admin-reject btn-sm" type="submit">Reject</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php elseif ($section === 'completed'): ?>
        <!-- Completed Section -->
        <div class="container mb-5">
          <div class="completed-section-cards">
            <div class="admin-card p-3">
              <h5 class="mb-3">Completed Profile Photos</h5>
              <table class="table admin-table table-sm">
                <thead><tr><th>Name</th><th>Email</th><th>Photo</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($completedApprovals['photos'] as $row): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['StudentName'] ?? $row['AdminName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['Email'] ?? ''); ?></td>
                    <td><?php if (!empty($row['ProfilePhoto'])): ?><img src="../<?php echo htmlspecialchars($row['ProfilePhoto']); ?>" alt="Photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                    <td>
                      <?php if (($row['PhotoConfirmed'] ?? null) == 1): ?>
                        <span class="badge bg-success badge-status">Approved</span>
                      <?php else: ?>
                        <span class="badge bg-danger badge-status">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="admin-card p-3 mb-4">
              <h5 class="mb-3">Completed Lost Item Reports</h5>
              <table class="table admin-table table-sm">
                <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Student No</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($completedApprovals['lostItems'] as $row): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['ItemName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['Description'] ?? ''); ?></td>
                    <td><?php if (!empty($row['PhotoURL'])): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                    <td><?php echo htmlspecialchars($row['StudentNo'] ?? ''); ?></td>
                    <td>
                      <?php if (($row['StatusConfirmed'] ?? null) == 1): ?>
                        <span class="badge bg-success badge-status">Approved</span>
                      <?php else: ?>
                        <span class="badge bg-danger badge-status">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="admin-card p-3">
            <h5 class="mb-3">Completed Found Item Reports</h5>
            <table class="table admin-table table-sm">
              <thead><tr><th>Item</th><th>Description</th><th>Photo</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach ($completedApprovals['foundItems'] as $row): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['ItemName'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($row['Description'] ?? ''); ?></td>
                  <td><?php if (!empty($row['PhotoURL'])): ?><img src="../<?php echo htmlspecialchars($row['PhotoURL']); ?>" alt="Photo" style="width:40px;height:40px;object-fit:cover;"><?php endif; ?></td>
                  <td>
                    <?php if (($row['StatusConfirmed'] ?? null) == 1): ?>
                      <span class="badge bg-success badge-status">Approved</span>
                    <?php else: ?>
                      <span class="badge bg-danger badge-status">Rejected</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($row['UpdatedAt'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php elseif ($section === 'adminmgmt'): ?>
        <!-- Admin Management Section -->
        <div class="container mb-5">
          <div class="row g-4 mb-4">
            <div class="col-md-8">
              <div class="admin-card p-3">
                <h5 class="mb-3">All Admins</h5>
                <table class="table admin-management-table table-sm">
                  <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Action</th></tr></thead>
                  <tbody>
                  <?php foreach ($admins as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['AdminName'] ?? $row['StudentName'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['Username'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['Email'] ?? ''); ?></td>
                      <td>
                        <?php if (($_SESSION['admin']['AdminID'] ?? null) != ($row['AdminID'] ?? null)): ?>
                          <form method="POST" action="remove_admin.php" style="display:inline" onsubmit="return confirm('Remove this admin?');">
                            <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($row['AdminID'] ?? ''); ?>">
                            <button class="btn btn-admin-remove btn-sm" type="submit">Remove</button>
                          </form>
                        <?php else: ?>
                          <span class="badge bg-secondary">You</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-4">
              <div class="admin-card p-3">
                <h5 class="mb-3">Add New Admin</h5>
                <form method="POST" action="add_admin.php">
                  <div class="mb-2">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="admin_name" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="admin_username" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="admin_email" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="admin_password" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Add Admin</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php elseif ($section === 'settings'): ?>
        <!-- Settings Section -->
        <div class="container mb-5">
          <div class="settings-card admin-card p-4 mt-4">
            <h5 class="mb-3">Change Password</h5>
            <?php if (!empty($_SESSION['admin_settings_msg'])): ?>
              <div class="alert alert-info text-center mb-3"><?php echo $_SESSION['admin_settings_msg']; unset($_SESSION['admin_settings_msg']); ?></div>
            <?php endif; ?>
            <form method="POST" action="change_admin_password.php">
              <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Change Password</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/notifications.js"></script>
<script src="../assets/forms.js"></script>
</body>
</html> 