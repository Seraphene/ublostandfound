<?php
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/ReportItem.php';
require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/FileUpload.php';

session_start();
if (!isset($_SESSION['student'])) {
    header('Location: index.php');
    exit;
}

// Initialize classes
$student = new Student();
$reportItem = new ReportItem();
$item = new Item();
$fileUpload = new FileUpload();

// Fetch fresh student data
$studentData = $student->getByStudentNo($_SESSION['student']['StudentNo']);
$_SESSION['student'] = $studentData;

// Handle form submissions
$dashboardMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['report_lost'])) {
        $result = handleLostItemReport($reportItem, $fileUpload);
        $dashboardMsg = $result['message'];
    } elseif (isset($_POST['report_found'])) {
        $result = handleFoundItemReport($item, $fileUpload);
        $dashboardMsg = $result['message'];
    }
}

function handleLostItemReport($reportItem, $fileUpload) {
    $studentNo = $_SESSION['student']['StudentNo'];
    $itemName = $_POST['lostItemName'] ?? '';
    $itemClass = $_POST['lostItemClass'] ?? '';
    $description = $_POST['lostDescription'] ?? '';
    $dateOfLoss = $_POST['lostDate'] ?? '';
    $lostLocation = $_POST['lostLocation'] ?? '';
    
    $photoURL = null;
    if (isset($_FILES['lostPhoto']) && $_FILES['lostPhoto']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = $fileUpload->uploadPhoto($_FILES['lostPhoto'], 'lost');
        if ($uploadResult['success']) {
            $photoURL = $uploadResult['path'];
        } else {
            return ['success' => false, 'message' => $uploadResult['message']];
        }
    }
    
    return $reportItem->create($studentNo, $itemName, $itemClass, $description, $dateOfLoss, $lostLocation, $photoURL);
}

function handleFoundItemReport($item, $fileUpload) {
    $adminID = 1; // For demo, use AdminID=1. In real app, use session for admin login.
    $itemName = $_POST['foundItemName'] ?? '';
    $itemClass = $_POST['foundItemClass'] ?? '';
    $description = $_POST['foundDescription'] ?? '';
    $dateFound = $_POST['foundDate'] ?? '';
    $locationFound = $_POST['foundLocation'] ?? '';
    
    $photoURL = null;
    if (isset($_FILES['foundPhoto']) && $_FILES['foundPhoto']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = $fileUpload->uploadPhoto($_FILES['foundPhoto'], 'found');
        if ($uploadResult['success']) {
            $photoURL = $uploadResult['path'];
        } else {
            return ['success' => false, 'message' => $uploadResult['message']];
        }
    }
    
    return $item->create($adminID, $itemName, $itemClass, $description, $dateFound, $locationFound, $photoURL);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - UB Lost & Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/ub.css" rel="stylesheet">
  <link href="../assets/dashboard.css" rel="stylesheet">
  <link href="../assets/notifications.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include '../templates/header.php'; ?>
<div class="container py-4">
  <div class="hero-section position-relative mb-5">
    <div class="hero-bg"></div>
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1><i class="bi bi-gem me-2"></i>Welcome, <?php echo htmlspecialchars($studentData['StudentName']); ?>!</h1>
        <p class="mb-4">This is your University of Batangas Lost & Found dashboard. Browse lost and found items, report your own, and contact the admin—all in a modern, secure, and beautiful interface.</p>
        <a href="#" class="btn btn-danger btn-lg mb-3 me-2" data-bs-toggle="modal" data-bs-target="#reportLostModal"><i class="bi bi-plus-circle"></i> Report Lost Item</a>
        <a href="#" class="btn btn-success btn-lg mb-3 me-2" data-bs-toggle="modal" data-bs-target="#reportFoundModal"><i class="bi bi-plus-circle"></i> Report Found Item</a>
        <a href="all_lost.php" class="btn btn-warning me-2"><i class="bi bi-search"></i> Browse Lost Items</a>
        <a href="found_items.php" class="btn btn-light"><i class="bi bi-box-seam"></i> Browse Found Items</a>
      </div>
      <div class="col-md-4 text-center d-none d-md-block">
        <?php if (!empty($studentData['ProfilePhoto']) && isset($studentData['PhotoConfirmed']) && $studentData['PhotoConfirmed'] == 1): ?>
          <img src="../<?php echo htmlspecialchars($studentData['ProfilePhoto']); ?>" alt="Profile Photo" class="rounded-circle shadow-lg" style="width:120px;height:120px;object-fit:cover;border:4px solid #FFD700;">
        <?php else: ?>
          <i class="bi bi-person-circle" style="font-size:7rem;color:#FFD700;"></i>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($dashboardMsg): ?>
    <div class="alert alert-info mt-3 shadow-sm"> <?php echo htmlspecialchars($dashboardMsg); ?> </div>
  <?php endif; ?>
  
  <!-- Approval System Notice -->
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Admin Approval Required:</strong> All lost and found item reports require admin approval before being visible to other users. You can track the status of your reports in "My Reports".
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  
  <!-- Profile Photo Rejection Notice -->
  <?php if (isset($studentData['PhotoConfirmed']) && $studentData['PhotoConfirmed'] == -1): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <strong>Profile Photo Rejected:</strong> Your profile photo was rejected by admin. Please upload a new photo in your profile page.
      <a href="profile.php" class="btn btn-outline-danger btn-sm ms-2">Upload New Photo</a>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  

  <div class="dashboard-cards">
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 text-center">
          <div class="card-body py-4">
            <i class="bi bi-clipboard-data display-4 mb-3" style="color:var(--ub-maroon);"></i>
            <h5 class="card-title fw-bold mb-2">My Reports</h5>
            <p class="card-text mb-3">View and manage your lost item reports in one place.</p>
            <a href="my_reports.php" class="btn btn-primary w-75"><i class="bi bi-clipboard-data"></i> My Reports</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 text-center">
          <div class="card-body py-4">
            <i class="bi bi-search display-4 mb-3" style="color:var(--ub-maroon);"></i>
            <h5 class="card-title fw-bold mb-2">All Lost Items</h5>
            <p class="card-text mb-3">Browse approved lost items reported by students. Use filters to find your item.</p>
            <a href="all_lost.php" class="btn btn-warning w-75"><i class="bi bi-search"></i> Browse Lost</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 text-center">
          <div class="card-body py-4">
            <i class="bi bi-box-seam display-4 mb-3" style="color:var(--ub-maroon);"></i>
            <h5 class="card-title fw-bold mb-2">Found Items</h5>
            <p class="card-text mb-3">See approved items found and reported by admins. Maybe yours is here!</p>
            <a href="found_items.php" class="btn btn-light w-75"><i class="bi bi-box-seam"></i> Browse Found</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 text-center">
          <div class="card-body py-4">
            <i class="bi bi-envelope-paper-heart display-4 mb-3" style="color:var(--ub-maroon);"></i>
            <h5 class="card-title fw-bold mb-2">Contact Admin</h5>
            <p class="card-text mb-3">Need help? Send a message to the Lost & Found admin directly.</p>
            <a href="contact_admin.php" class="btn btn-primary w-75"><i class="bi bi-envelope"></i> Contact Admin</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 text-center">
          <div class="card-body py-4">
            <i class="bi bi-person display-4 mb-3" style="color:var(--ub-maroon);"></i>
            <h5 class="card-title fw-bold mb-2">My Profile</h5>
            <p class="card-text mb-3">View and update your personal information and profile photo.</p>
            <a href="profile.php" class="btn btn-warning w-75"><i class="bi bi-person"></i> My Profile</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Report Lost Item Modal -->
<div class="modal fade" id="reportLostModal" tabindex="-1" aria-labelledby="reportLostModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="reportLostModalLabel">Report Lost Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="report_lost" value="1">
          <div class="mb-3">
            <label for="lostItemName" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="lostItemName" name="lostItemName" required>
          </div>
          <div class="mb-3">
            <label for="lostItemClass" class="form-label">Item Class</label>
            <input type="text" class="form-control" id="lostItemClass" name="lostItemClass" required>
          </div>
          <div class="mb-3">
            <label for="lostDescription" class="form-label">Description</label>
            <textarea class="form-control" id="lostDescription" name="lostDescription" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="lostDate" class="form-label">Date of Loss</label>
            <input type="date" class="form-control" id="lostDate" name="lostDate" required>
          </div>
          <div class="mb-3">
            <label for="lostLocation" class="form-label">Location Lost</label>
            <input type="text" class="form-control" id="lostLocation" name="lostLocation" required>
          </div>
          <div class="mb-3">
            <label for="lostPhoto" class="form-label">Photo (optional)</label>
            <input type="file" class="form-control" id="lostPhoto" name="lostPhoto" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Submit Lost Report</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Report Found Item Modal -->
<div class="modal fade" id="reportFoundModal" tabindex="-1" aria-labelledby="reportFoundModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="reportFoundModalLabel">Report Found Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="report_found" value="1">
          <div class="mb-3">
            <label for="foundItemName" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="foundItemName" name="foundItemName" required>
          </div>
          <div class="mb-3">
            <label for="foundItemClass" class="form-label">Item Class</label>
            <input type="text" class="form-control" id="foundItemClass" name="foundItemClass" required>
          </div>
          <div class="mb-3">
            <label for="foundDescription" class="form-label">Description</label>
            <textarea class="form-control" id="foundDescription" name="foundDescription" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="foundDate" class="form-label">Date Found</label>
            <input type="date" class="form-control" id="foundDate" name="foundDate" required>
          </div>
          <div class="mb-3">
            <label for="foundLocation" class="form-label">Location Found</label>
            <input type="text" class="form-control" id="foundLocation" name="foundLocation" required>
          </div>
          <div class="mb-3">
            <label for="foundPhoto" class="form-label">Photo (optional)</label>
            <input type="file" class="form-control" id="foundPhoto" name="foundPhoto" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit Found Report</button>
        </div>
      </form>
    </div>
  </div>
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/notifications.js"></script>
  <script src="../assets/forms.js"></script>
</body>
</html> 