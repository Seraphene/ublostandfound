<?php
session_start();
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../classes/Notification.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_dashboard.php');
    exit;
}
$db = new Database();
$conn = $db->getConnection();
$notification = new Notification($conn);
$type = $_POST['type'] ?? '';
$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$msg = '';

if ($type === 'photo' && $id) {
    // Get student info first
    $stmt = $conn->prepare('SELECT StudentNo, StudentName FROM student WHERE StudentNo = :id');
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($action === 'approve') {
        $stmt = $conn->prepare('UPDATE student SET PhotoConfirmed = 1 WHERE StudentNo = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Profile photo approved.';
        
        // Create notification
        $notification->create(
            $student['StudentNo'],
            'photo_approved',
            'Profile Photo Approved!',
            'Your profile photo has been approved and is now visible to other users.',
            $id
        );
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare('UPDATE student SET ProfilePhoto = NULL, PhotoConfirmed = 0 WHERE StudentNo = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Profile photo rejected.';
        
        // Create notification
        $notification->create(
            $student['StudentNo'],
            'photo_rejected',
            'Profile Photo Rejected',
            'Your profile photo was rejected. Please upload a different photo.',
            $id
        );
    }
} elseif ($type === 'lost' && $id) {
    // Get report info first
    $stmt = $conn->prepare('SELECT r.StudentNo, r.ItemName, s.StudentName FROM reportitem r JOIN student s ON r.StudentNo = s.StudentNo WHERE r.ReportID = :id');
    $stmt->execute(['id' => $id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($action === 'approve') {
        $stmt = $conn->prepare('UPDATE reportitem SET StatusConfirmed = 1 WHERE ReportID = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Lost item report approved.';
        
        // Create notification
        $notification->create(
            $report['StudentNo'],
            'report_approved',
            'Lost Item Report Approved!',
            'Your lost item report for "' . $report['ItemName'] . '" has been approved and is now visible to other users.',
            $id
        );
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare('UPDATE reportitem SET StatusConfirmed = -1 WHERE ReportID = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Lost item report rejected.';
        
        // Create notification
        $notification->create(
            $report['StudentNo'],
            'report_rejected',
            'Lost Item Report Rejected',
            'Your lost item report for "' . $report['ItemName'] . '" was rejected. Please check the details and submit again.',
            $id
        );
    }
} elseif ($type === 'found' && $id) {
    if ($action === 'approve') {
        $stmt = $conn->prepare('UPDATE item SET StatusConfirmed = 1 WHERE ItemID = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Found item report approved.';
        // Note: Found items are reported by admins, so no user notification needed
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare('UPDATE item SET StatusConfirmed = -1 WHERE ItemID = :id');
        $stmt->execute(['id' => $id]);
        $msg = 'Found item report rejected.';
        // Note: Found items are reported by admins, so no user notification needed
    }
}
$_SESSION['admin_msg'] = $msg;
header('Location: admin_dashboard.php');
exit; 