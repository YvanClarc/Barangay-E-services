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
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="logo-section">
        <img src="../../images/ivan.png" alt="Barangay Logo">
        <h2>BARANGAY E-SERVICES<br>AND COMPLAINT<br>MANAGEMENT SYSTEM</h2>
      </div>

      <nav class="nav">
        <a href="#" class="nav-item nav-dashboard active" data-target="dashboardSection"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="#" class="nav-item nav-announcements" data-target="announcementsSection"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="#" class="nav-item nav-requests" data-target="requestCertificateSection"><i class="fas fa-file-alt"></i> Request Certificate</a>
        <a href="#" class="nav-item nav-complaints" data-target="fileComplaintSection"><i class="fas fa-exclamation-circle"></i> File Complaint</a>
        <a href="#" class="nav-item nav-settings" data-target="settingsSection"><i class="fas fa-cog"></i> Settings</a>
      </nav>
    </aside>

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
            <i class="fas fa-file-alt"></i>
            <p>Total Certificates Requested</p>
            <h2><?php echo $total_requests; ?></h2>
          </div>
          <div class="card green">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Total Complaints Filed</p>
            <h2><?php
              $complaints_count_query = $conn->prepare("SELECT COUNT(*) as count FROM tbl_complaints WHERE user_id = ?");
              $complaints_count_query->bind_param("i", $user_id);
              $complaints_count_query->execute();
              $complaints_count = $complaints_count_query->get_result()->fetch_assoc()['count'];
              echo $complaints_count;
              $complaints_count_query->close();
            ?></h2>
          </div>
          <div class="card yellow">
            <i class="fas fa-clock"></i>
            <p>Pending Requests</p>
            <h2><?php echo $pending_count; ?></h2>
          </div>
          <div class="card orange">
            <i class="fas fa-check-circle"></i>
            <p>Approved Requests</p>
            <h2><?php echo $completed_count; ?></h2>
          </div>
        </div>



        <!-- My Requests Section -->
        <div class="users-section">
          <div class="users-header">
            <h2>My Requests</h2>
            <div class="header-actions">
              <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="requestSearch" placeholder="Search requests..." onkeyup="searchTable('userTable', this.value)">
              </div>
              <select id="requestStatusFilter" onchange="filterUserRequests()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="denied">Denied</option>
              </select>
            </div>
          </div>

          <table class="user-table" id="userTable">
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
                <tr data-status="<?= htmlspecialchars($req['r_status']) ?>">
                  <td><span class="request-id-badge"><?= htmlspecialchars($req['r_id']) ?></span></td>
                  <td><span class="doc-type-badge"><?= htmlspecialchars($req['document_type']) ?></span></td>
                  <td style="font-size:13px;"><?= htmlspecialchars($req['date_requested'] ?? '') ?></td>
                  <td><span class="status <?= $req['r_status'] === 'pending' ? 'pending' : ($req['r_status'] === 'approved' ? 'active' : 'denied') ?>">
                    <?= ucfirst($req['r_status']) ?>
                  </span></td>
                  <td>
                    <?php if ($req['r_status'] === 'pending'): ?>
                      <button class="action-btn edit-btn" onclick="editRequest(<?= $req['r_id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                      <button class="action-btn delete-btn" onclick="deleteRequest(<?= $req['r_id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
                    <?php elseif ($req['r_status'] === 'approved' && !empty($req['pickup_datetime'])): ?>
                      <button class="action-btn view-btn" onclick="viewPickupDetails(<?= $req['r_id'] ?>)"><i class="fas fa-eye"></i> View Pickup</button>
                    <?php else: ?>
                      <span style="color:#888;font-size:12px;">Completed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align:center;padding:40px;color:#666;">No requests found.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>

          <!-- Pagination for User Requests -->
          <div id="requestPagination" style="margin-top:20px;text-align:center">
            <button id="requestPrevPage" onclick="changeRequestPage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
            <span id="requestPageInfo">Page 1 of 1</span>
            <button id="requestNextPage" onclick="changeRequestPage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
          </div>
        </div>
        <!-- My Complaints Section -->
        <div class="complaints-section" style="margin-top: 40px;">
          <div class="users-header">
            <h2>My Complaints</h2>
            <div class="header-actions">
              <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="complaintSearch" placeholder="Search complaints..." onkeyup="searchTable('complaintsTable', this.value)">
              </div>
              <select id="complaintStatusFilter" onchange="filterUserComplaints()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Resolved">Resolved</option>
                <option value="Dismissed">Dismissed</option>
              </select>
            </div>
          </div>
          <p class="subtitle">Here are the complaints you have submitted to the barangay.</p>

          <table class="user-table" id="complaintsTable">
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
                          <tr data-status='{$status}'>
                            <td><span class='reference-badge'>{$reference_no}</span></td>
                            <td><span class='complaint-type-badge'>{$complaint_type}</span></td>
                            <td style='max-width:200px;word-break:break-word;font-size:13px;'>{$details}</td>
                            <td style='font-size:13px;'>{$date_incident}</td>
                            <td style='max-width:150px;word-break:break-word;font-size:13px;'>{$location}</td>
                            <td><span class='status-badge {$status_class}'>{$status}</span></td>
                            <td style='font-size:12px;'>{$date_filed}</td>
                          </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;padding:40px;color:#666;'>No complaints filed yet.</td></tr>";
                }

                $complaints_query->close();
              ?>
            </tbody>
          </table>

          <!-- Pagination for User Complaints -->
          <div id="complaintPagination" style="margin-top:20px;text-align:center">
            <button id="complaintPrevPage" onclick="changeComplaintPage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
            <span id="complaintPageInfo">Page 1 of 1</span>
            <button id="complaintNextPage" onclick="changeComplaintPage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
          </div>
        </div>
      </div>

      <!-- Request Certificate Section -->
      <div class="request-certificate-section section" id="requestCertificateSection" style="display:none;">
        <div class="page-section">
          <div class="users-header">
            <h2>Request Barangay Certificate</h2>
            <div class="header-actions">
              <button type="button" class="add-user-btn" onclick="resetRequestForm()" style="background: #17a2b8;"><i class="fas fa-redo"></i> Reset Form</button>
            </div>
          </div>
          <p class="subtitle">Fill out the form below to request your barangay certificate. All fields marked with * are required.</p>

          <div class="request-form-container" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            <form method="POST" action="add_request.php" class="request-form" id="certificateRequestForm">

              <!-- Personal Information Section -->
              <div class="form-section">
                <h3 style="color: #1e3d8f; margin-bottom: 20px; font-size: 18px;"><i class="fas fa-user"></i> Personal Information</h3>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="first_name"><i class="fas fa-user-tag"></i> First Name *</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required>
                  </div>

                  <div class="form-group">
                    <label for="second_name"><i class="fas fa-user-tag"></i> Second Name</label>
                    <input type="text" name="second_name" id="second_name" placeholder="Enter your second name (optional)">
                  </div>

                  <div class="form-group">
                    <label for="last_name"><i class="fas fa-user-tag"></i> Last Name *</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required>
                  </div>

                  <div class="form-group">
                    <label for="gender"><i class="fas fa-venus-mars"></i> Gender *</label>
                    <select name="gender" id="gender" required>
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="age"><i class="fas fa-birthday-cake"></i> Age *</label>
                    <input type="number" name="age" id="age" placeholder="Enter your age" min="1" max="120" required>
                  </div>

                  <div class="form-group full-width">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address (Purok) *</label>
                    <input type="text" name="address" id="address" placeholder="Enter your complete address including purok" required>
                  </div>
                </div>
              </div>

              <!-- Certificate Details Section -->
              <div class="form-section" style="margin-top: 30px;">
                <h3 style="color: #1e3d8f; margin-bottom: 20px; font-size: 18px;"><i class="fas fa-file-alt"></i> Certificate Details</h3>
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label for="doc_type"><i class="fas fa-file-contract"></i> Document Type *</label>
                    <select name="doc_type" id="doc_type" required>
                      <option value="">Select Document Type</option>
                      <option value="Barangay Certificate">Barangay Certificate</option>
                      <option value="Barangay Indigency">Barangay Indigency</option>
                      <option value="Business Permit">Business Permit</option>
                    </select>
                  </div>

                  <div class="form-group full-width">
                    <label for="purpose"><i class="fas fa-question-circle"></i> Purpose *</label>
                    <input type="text" name="purpose" id="purpose" placeholder="State the purpose of this certificate (e.g., Employment, Loan Application, etc.)" required>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Please provide a clear and specific purpose for your certificate request.</small>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; display: flex; gap: 15px; justify-content: flex-end;">
                <button type="button" class="cancel-btn" onclick="showDashboardSection()" style="padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: background 0.3s;">
                  <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="submit-btn" style="padding: 12px 24px; background: linear-gradient(135deg, #1e3d8f, #3f51b5); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                  <i class="fas fa-paper-plane"></i> Submit Request
                </button>
              </div>
            </form>
          </div>
        </div>
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
        <div class="page-section">
          <div class="users-header">
            <h2>File a Complaint</h2>
            <div class="header-actions">
              <button type="button" class="add-user-btn" onclick="resetComplaintForm()" style="background: #17a2b8;"><i class="fas fa-redo"></i> Reset Form</button>
            </div>
          </div>
          <p class="subtitle">Report community issues or concerns. Please provide accurate details to help us address your complaint effectively.</p>

          <div class="request-form-container" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            <form method="POST" action="add_complaint.php" class="complaint-form" id="complaintForm">

              <!-- Complaint Details Section -->
              <div class="form-section">
                <h3 style="color: #1e3d8f; margin-bottom: 20px; font-size: 18px;"><i class="fas fa-exclamation-triangle"></i> Complaint Information</h3>
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label for="complaint_type"><i class="fas fa-tags"></i> Complaint Type *</label>
                    <select name="complaint_type" id="complaint_type" required>
                      <option value="">Select Complaint Type</option>
                      <option value="Noise Disturbance">Noise Disturbance</option>
                      <option value="Garbage Problem">Garbage Problem</option>
                      <option value="Conflict with Neighbor">Conflict with Neighbor</option>
                      <option value="Infrastructure Issue">Infrastructure Issue</option>
                      <option value="Public Safety">Public Safety Concern</option>
                      <option value="Environmental Issue">Environmental Issue</option>
                      <option value="Others">Others</option>
                    </select>
                  </div>

                  <div class="form-group full-width">
                    <label for="complaint_details"><i class="fas fa-file-alt"></i> Complaint Details *</label>
                    <textarea name="complaint_details" id="complaint_details" placeholder="Please provide a detailed description of your complaint. Include what happened, when it occurred, and any relevant information that can help us understand and resolve the issue." rows="6" required></textarea>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Be specific and include all relevant details to help us address your concern effectively.</small>
                  </div>
                </div>
              </div>

              <!-- Incident Information Section -->
              <div class="form-section" style="margin-top: 30px;">
                <h3 style="color: #1e3d8f; margin-bottom: 20px; font-size: 18px;"><i class="fas fa-calendar-alt"></i> Incident Information</h3>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="complaint_date"><i class="fas fa-calendar-day"></i> Date of Incident *</label>
                    <input type="date" name="complaint_date" id="complaint_date" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">When did this incident occur?</small>
                  </div>

                  <div class="form-group">
                    <label for="complaint_time"><i class="fas fa-clock"></i> Time of Incident</label>
                    <input type="time" name="complaint_time" id="complaint_time">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Approximate time (optional)</small>
                  </div>

                  <div class="form-group full-width">
                    <label for="complaint_location"><i class="fas fa-map-marker-alt"></i> Location *</label>
                    <input type="text" name="complaint_location" id="complaint_location" placeholder="Provide the exact location where the incident occurred (e.g., Purok 5, Barangay Hall, etc.)" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Be as specific as possible to help us locate and address the issue.</small>
                  </div>
                </div>
              </div>

              <!-- Contact Information Section -->
              <div class="form-section" style="margin-top: 30px;">
                <h3 style="color: #1e3d8f; margin-bottom: 20px; font-size: 18px;"><i class="fas fa-phone"></i> Contact Information</h3>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="contact_name"><i class="fas fa-user"></i> Contact Name</label>
                    <input type="text" name="contact_name" id="contact_name" placeholder="Your full name">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Optional - for follow-up purposes</small>
                  </div>

                  <div class="form-group">
                    <label for="contact_phone"><i class="fas fa-mobile-alt"></i> Contact Phone</label>
                    <input type="tel" name="contact_phone" id="contact_phone" placeholder="Your phone number">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Optional - for urgent follow-up</small>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; display: flex; gap: 15px; justify-content: flex-end;">
                <button type="button" class="cancel-btn" onclick="showDashboardSection()" style="padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: background 0.3s;">
                  <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="submit-btn" style="padding: 12px 24px; background: linear-gradient(135deg, #dc3545, #c82333); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                  <i class="fas fa-paper-plane"></i> Submit Complaint
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- VIEW PICKUP MODAL -->
  <div id="viewPickupModal" class="modal">
    <div class="modal-content" style="max-width:600px">
      <span class="close-btn" onclick="closeModal('viewPickupModal')" style="float:right;cursor:pointer">&times;</span>
      <h3>Pickup Details</h3>
      <div id="pickupDetails" style="line-height:1.6">
        <!-- Pickup details will be loaded here -->
      </div>
      <div style="margin-top:20px;text-align:right">
        <button onclick="closeModal('viewPickupModal')">Close</button>
      </div>
    </div>
  </div>

  <script src="../../scripts/user_dashboard.js"></script>
</body>
</html>
