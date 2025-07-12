<?php
session_start();
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../classes/Notification.php';

if (!isset($_SESSION['student'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$notification = new Notification($conn);
$student = $_SESSION['student'];

// Handle mark as read action
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification->markAsRead($_POST['notification_id'], $student['StudentNo']);
    header('Location: notifications.php');
    exit;
}

// Handle mark all as read
if (isset($_POST['mark_all_read'])) {
    $notification->markAllAsRead($student['StudentNo']);
    header('Location: notifications.php');
    exit;
}

// Get all notifications
$allNotifications = $notification->getAll($student['StudentNo'], 50);
$unreadCount = $notification->getUnreadCount($student['StudentNo']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - UB Lost & Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/ub.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .notification-card {
      border-radius: 1rem;
      box-shadow: 0 2px 8px rgba(128,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      border: none;
    }
    .notification-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(128,0,0,0.12);
    }
    .notification-card.unread {
      border-left: 4px solid var(--ub-maroon);
      background: #fff9f6;
    }
    .notification-card.read {
      border-left: 4px solid #e9ecef;
      background: #fff;
    }
    .notification-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
    .notification-time {
      font-size: 0.8rem;
      color: #6c757d;
    }
    .notification-title {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    .notification-message {
      color: #495057;
      line-height: 1.4;
    }
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
  </style>
</head>
<body>
<?php include '../templates/header.php'; ?>
<div class="container py-4">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-1"><i class="bi bi-bell me-2"></i>Notifications</h2>
          <p class="text-muted mb-0">
            <?php if ($unreadCount > 0): ?>
              You have <?php echo $unreadCount; ?> unread notification<?php echo $unreadCount > 1 ? 's' : ''; ?>
            <?php else: ?>
              All caught up! No unread notifications
            <?php endif; ?>
          </p>
        </div>
        <?php if ($unreadCount > 0): ?>
          <form method="POST" style="display: inline;">
            <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
              <i class="bi bi-check-all me-1"></i>Mark All Read
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Notifications List -->
      <?php if (count($allNotifications) > 0): ?>
        <div class="row g-3">
          <?php foreach ($allNotifications as $notif): ?>
            <div class="col-12">
              <div class="card notification-card <?php echo $notif['IsRead'] ? 'read' : 'unread'; ?>">
                <div class="card-body">
                  <div class="row align-items-start">
                    <div class="col-auto">
                      <div class="notification-icon bg-light">
                        <i class="<?php echo Notification::getIcon($notif['Type']); ?>"></i>
                      </div>
                    </div>
                    <div class="col">
                      <div class="notification-title"><?php echo htmlspecialchars($notif['Title']); ?></div>
                      <div class="notification-message"><?php echo htmlspecialchars($notif['Message']); ?></div>
                      <div class="notification-time mt-2">
                        <i class="bi bi-clock me-1"></i><?php echo Notification::formatTime($notif['CreatedAt']); ?>
                      </div>
                    </div>
                    <?php if (!$notif['IsRead']): ?>
                      <div class="col-auto">
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="notification_id" value="<?php echo $notif['NotificationID']; ?>">
                          <button type="submit" name="mark_read" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-check"></i> Mark Read
                          </button>
                        </form>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-bell-slash"></i>
          <h4>No notifications yet</h4>
          <p>You'll see notifications here when admins approve or reject your submissions, or when there are important updates.</p>
          <a href="dashboard.php" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>Go to Dashboard
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-refresh notification count in header
function refreshNotificationCount() {
  // This would typically be done with AJAX, but for simplicity we'll just reload the page
  // In a real app, you'd use WebSockets or polling for real-time updates
}

// Mark notification as read when clicked (for dropdown notifications)
document.addEventListener('DOMContentLoaded', function() {
  const notificationItems = document.querySelectorAll('.notification-item');
  notificationItems.forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const notificationId = this.getAttribute('data-notification-id');
      
      // Send AJAX request to mark as read
      fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
      }).then(() => {
        // Remove the notification from the dropdown
        this.remove();
        
        // Update the notification count
        const badge = document.querySelector('#notificationDropdown .badge');
        if (badge) {
          const currentCount = parseInt(badge.textContent);
          if (currentCount > 1) {
            badge.textContent = currentCount - 1;
          } else {
            badge.remove();
          }
        }
      });
    });
  });
});
</script>
</body>
</html> 