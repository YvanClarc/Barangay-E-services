<?php
session_start();
require_once '../../config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user requests
$stmt = $conn->prepare("SELECT * FROM tbl_requests WHERE id = ? ORDER BY r_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$total_requests = count($requests);
$stmt->close();

// Count pending and approved
$pending_count = 0;
$completed_count = 0;
foreach ($requests as $req) {
    if ($req['r_status'] === 'pending') $pending_count++;
    if ($req['r_status'] === 'approved') $completed_count++;
}

// Fetch recent published announcements (most recent first, limit 5)
$ann_stmt = $conn->prepare("SELECT ann_id, title, details, image_path, created_at FROM tbl_announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
if ($ann_stmt) {
  $ann_stmt->execute();
  $ann_res = $ann_stmt->get_result();
  $announcements = [];
  while ($r = $ann_res->fetch_assoc()) {
    $announcements[] = $r;
  }
  $ann_stmt->close();
} else {
  $announcements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../styles/Official_dashboard.css" />
  <link rel="stylesheet" href="../../styles/User_dashboard.css" />
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo-section">
        <img src="../../images/ivan.png" alt="Barangay Logo" />
        <h2>Barangay<br>E-Services and Complaint Management System</h2>
      </div>

      <div class="nav">
        <a href="#" class="active">Dashboard</a>
        <a href="#">Announcements</a>
        <a href="#">Request Certificate</a>
        <a href="#">File Complaint</a>
        <a href="#">Settings</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main">
      <div class="topbar">
        <span class="admin">USER</span>
        <img src="../../images/ivan.png" alt="User Icon" class="admin-icon" />
        <button id="logoutBtn" class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </div>

      <!-- Dashboard Section -->
      <div class="dashboard section" id="dashboardSection">
        <h1>Dashboard Overview</h1>
        <div class="cards">
          <div class="card blue">
            <p>Total Certificates Requested</p>
            <span><?php echo $total_requests; ?></span>
          </div>
          <div class="card green">
            <p>Total Complaints Filed</p>
            <span>1</span>
          </div>
          <div class="card yellow">
            <p>Pending Requests</p>
            <span><?php echo $pending_count; ?></span>
          </div>
          <div class="card orange">
            <p>Approved Requests</p>
            <span><?php echo $completed_count; ?></span>
          </div>
        </div>

        <!-- My Requests Section -->
        <div class="users-section">
          <div class="users-header">
            <h2>My Requests</h2>
            <div class="header-actions">
              <div class="search-container">
                <i>🔍</i>
                <input type="text" placeholder="Search requests" />
              </div>
            </div>
          </div>

          <table class="user-table">
            <thead>
              <tr>
                <th>Request ID</th>
                <th>Type</th>
                <th>Date Requested</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($total_requests > 0): ?>
              <?php foreach ($requests as $req): ?>
                <tr>
                  <td><?= htmlspecialchars($req['r_id']) ?></td>
                  <td><?= htmlspecialchars($req['document_type']) ?></td>
                  <td><?= htmlspecialchars($req['date_requested'] ?? '') ?></td>
                  <td><span class="status <?= $req['r_status'] === 'pending' ? 'pending' : ($req['r_status'] === 'approved' ? 'active' : 'denied') ?>">
                    <?= ucfirst($req['r_status']) ?>
                  </span></td>
                  <td>
                    <?php if ($req['r_status'] === 'pending'): ?>
                      <button class="action-btn edit-btn" onclick="editRequest(<?= $req['r_id'] ?>)">Edit</button>
                      <button class="action-btn delete-btn" onclick="deleteRequest(<?= $req['r_id'] ?>)">Delete</button>
                    <?php else: ?>
                      <span style="color:#888;">No action</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align:center;">No requests found.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
              <!-- === My Complaints Section === -->
        <div id="myComplaintsSection" class="complaints-section" style="margin-top: 40px;">
        <h2>My Complaints</h2>
        <p class="subtitle">Here are the complaints you have submitted to the barangay.</p>
        <div class="table-container">
          <table class="complaints-table">
            <thead>
              <tr>
                <th>Reference No</th>
                <th>Complaint Type</th>
                <th>Details</th>
                <th>Date of Incident</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date Filed</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $user_id = $_SESSION['user_id'];
                $complaints_query = $conn->prepare("
                  SELECT reference_no, complaint_type, details, date_of_incident, location, status, date_filed 
                  FROM tbl_complaints 
                  WHERE user_id = ? 
                  ORDER BY date_filed DESC
                ");
                $complaints_query->bind_param("i", $user_id);
                $complaints_query->execute();
                $complaints_result = $complaints_query->get_result();

                if ($complaints_result->num_rows > 0) {
                    while ($row = $complaints_result->fetch_assoc()) {
                        $reference_no = htmlspecialchars($row['reference_no']);
                        $complaint_type = htmlspecialchars($row['complaint_type']);
                        $details = htmlspecialchars($row['details']);
                        $date_incident = htmlspecialchars($row['date_of_incident']);
                        $location = htmlspecialchars($row['location']);
                        $status = htmlspecialchars($row['status']);
                        $date_filed = date('M d, Y h:i A', strtotime($row['date_filed']));
                        $status_class = strtolower(str_replace(' ', '-', $status));

                        echo "
                          <tr>
                            <td>{$reference_no}</td>
                            <td>{$complaint_type}</td>
                            <td>{$details}</td>
                            <td>{$date_incident}</td>
                            <td>{$location}</td>
                            <td><span class='status-badge {$status_class}'>{$status}</span></td>
                            <td>{$date_filed}</td>
                          </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; color:#777;'>No complaints filed yet.</td></tr>";
                }

                $complaints_query->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
      </div>

      <!-- Request Certificate Section -->
      <div class="request-certificate-section section" id="requestCertificateSection" style="display:none;">
        <h2>Request Barangay Certificate</h2>
        <form method="POST" action="add_request.php" class="request-form">

          <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" placeholder="Enter first name" required>
          </div>

          <div class="form-group">
            <label for="second_name">Second Name (Optional):</label>
            <input type="text" name="second_name" id="second_name" placeholder="Enter second name">
          </div>

          <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" placeholder="Enter last name" required>
          </div>

          <div class="form-group">
            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Prefer not to say">Prefer not to say</option>
            </select>
          </div>

          <div class="form-group">
            <label for="age">Age:</label>
            <input type="number" name="age" id="age" placeholder="Enter age" min="1" required>
          </div>

          <div class="form-group">
            <label for="address">Address (Purok):</label>
            <input type="text" name="address" id="address" placeholder="Enter your Purok" required>
          </div>

          <div class="form-group">
            <label for="doc_type">Document Type:</label>
            <select name="doc_type" id="doc_type" required>
              <option value="">Select Document</option>
              <option value="Barangay Certificate">Barangay Certificate</option>
              <option value="Barangay Indigency">Barangay Indigency</option>
              <option value="Business Permit">Business Permit</option>
            </select>
          </div>

          <div class="form-group">
            <label for="purpose">Purpose:</label>
            <input type="text" name="purpose" id="purpose" placeholder="Purpose" required>
          </div>

          <button type="submit" class="add-request-btn">Submit Request</button>
          <button type="button" class="add-request-btn" style="background:#e74c3c;margin-left:10px;" onclick="showDashboardSection()">Cancel</button>
        </form>
      </div>

      <!-- ANNOUNCEMENTS SECTION (user) -->
      <div id="announcementsSection" class="section" style="display:none; margin-top:18px;">
        <h2>Announcements</h2>
        <p class="subtitle">Latest published announcements from the admin.</p>
        <?php if (!empty($announcements)): ?>
          <div class="postcards-container">
            <?php foreach ($announcements as $a): ?>
              <div class="postcard">
                <?php if (!empty($a['image_path']) && file_exists(__DIR__ . '/../../' . $a['image_path'])): ?>
                  <div class="postcard-media"><img src="../../<?= htmlspecialchars($a['image_path']) ?>" alt="announcement image" onclick="previewImage('../../<?= htmlspecialchars($a['image_path']) ?>')"></div>
                <?php else: ?>
                  <div class="postcard-media"><img src="../../images/ivan.png" alt="placeholder"></div>
                <?php endif; ?>
                <div class="postcard-body">
                  <h3 class="postcard-title"><?= htmlspecialchars($a['title']) ?></h3>
                  <p class="postcard-text"><?= htmlspecialchars($a['details']) ?></p>
                  <div class="postcard-meta">
                    <span><?= date('M d, Y', strtotime($a['created_at'])) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="no-announcements">No announcements published yet.</p>
        <?php endif; ?>
      </div>
      <!-- File Complaint Section -->
      <div class="file-complaint-section section" id="fileComplaintSection" style="display:none;">
        <h2>File a Complaint</h2>
        <form method="POST" action="add_complaint.php" class="complaint-form">

          <div class="form-group">
            <label for="complaint_type">Complaint Type:</label>
            <select name="complaint_type" id="complaint_type" required>
              <option value="">Select Complaint Type</option>
              <option value="Noise Disturbance">Noise Disturbance</option>
              <option value="Garbage Problem">Garbage Problem</option>
              <option value="Conflict with Neighbor">Conflict with Neighbor</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="form-group">
            <label for="complaint_details">Details:</label>
            <textarea name="complaint_details" id="complaint_details" placeholder="Describe your complaint..." rows="5" required></textarea>
          </div>

          <div class="form-group">
            <label for="complaint_date">Date of Incident:</label>
            <input type="date" name="complaint_date" id="complaint_date" required>
          </div>

          <div class="form-group">
            <label for="complaint_location">Location:</label>
            <input type="text" name="complaint_location" id="complaint_location" placeholder="Enter location" required>
          </div>

          <button type="submit" class="add-request-btn">Submit Complaint</button>
          <button type="button" class="add-request-btn cancel-btn" onclick="showDashboardSection()">Cancel</button>
        </form>
      </div>
    </div>
  </div>

  <script src="../../scripts/user_dashboard.js"></script>
</body>
</html>