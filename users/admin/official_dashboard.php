<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Basic safety: ensure $conn exists
if (!isset($conn)) {
  die('Database connection not configured.');
}

// === AGGREGATES ===
$user_count = 0;
$cert_count = 0;
$complaint_count = 0;

$user_query = "SELECT COUNT(*) AS total_users FROM tbl_users";
$cert_query = "SELECT COUNT(*) AS total_certificates FROM tbl_requests";
$complaint_query = "SELECT COUNT(*) AS total_complaints FROM tbl_complaints";

if ($res = $conn->query($user_query)) { $user_count = $res->fetch_assoc()['total_users'] ?? 0; }
if ($res = $conn->query($cert_query)) { $cert_count = $res->fetch_assoc()['total_certificates'] ?? 0; }
if ($res = $conn->query($complaint_query)) { $complaint_count = $res->fetch_assoc()['total_complaints'] ?? 0; }

// === CHART DATA ===
// Requests by document type
$doc_type_query = "SELECT document_type, COUNT(*) AS count FROM tbl_requests GROUP BY document_type";
$doc_type_result = $conn->query($doc_type_query);
$doc_types = [];
$doc_counts = [];
if ($doc_type_result) {
  while ($row = $doc_type_result->fetch_assoc()) {
    $doc_types[] = $row['document_type'];
    $doc_counts[] = $row['count'];
  }
}

// Request statuses
$req_status_query = "SELECT r_status, COUNT(*) AS count FROM tbl_requests GROUP BY r_status";
$req_status_result = $conn->query($req_status_query);
$req_statuses = [];
$req_status_counts = [];
if ($req_status_result) {
  while ($row = $req_status_result->fetch_assoc()) {
    $req_statuses[] = ucfirst($row['r_status']);
    $req_status_counts[] = $row['count'];
  }
}

// Complaint statuses
$comp_status_query = "SELECT status, COUNT(*) AS count FROM tbl_complaints GROUP BY status";
$comp_status_result = $conn->query($comp_status_query);
$comp_statuses = [];
$comp_status_counts = [];
if ($comp_status_result) {
  while ($row = $comp_status_result->fetch_assoc()) {
    $comp_statuses[] = ucfirst($row['status']);
    $comp_status_counts[] = $row['count'];
  }
}

// === FETCH USERS ===
$users_query = "SELECT id, first_name, last_name, email, role, account_status FROM tbl_users ORDER BY id DESC";
$users_result = $conn->query($users_query);

// === FETCH REQUESTS ===
$requests_query = "SELECT r.r_id, r.first_name, r.last_name, r.document_type, r.purpose, r.r_status, r.date_requested, u.email
                   FROM tbl_requests r
                   LEFT JOIN tbl_users u ON r.id = u.id
                   ORDER BY r.r_id DESC";
$requests_result = $conn->query($requests_query);

// === FETCH COMPLAINTS ===
$complaints_query = "SELECT c.c_id, c.reference_no, c.complaint_type, c.details, c.date_of_incident, c.location, c.status, c.date_filed,
                            u.first_name, u.last_name, u.email
                     FROM tbl_complaints c
                     LEFT JOIN tbl_users u ON c.user_id = u.id
                     ORDER BY c.date_filed DESC";
$complaints_result = $conn->query($complaints_query);

// === FETCH ANNOUNCEMENTS ===
$announcements_query = "SELECT a.ann_id, a.title, a.details, a.image_path, a.status, a.created_at, u.first_name, u.last_name
                        FROM tbl_announcements a
                        LEFT JOIN tbl_users u ON a.created_by = u.id
                        ORDER BY a.created_at DESC";
$announcements_result = $conn->query($announcements_query);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barangay Dashboard</title>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/Official_dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Minimal internal styles to ensure layout works if external CSS missing */
    :root{--sidebar-w:260px}
    body{font-family: 'Poppins',sans-serif;margin:0;background:#f5f7fb;color:#222}
    .container{display:flex;min-height:100vh}
    .sidebar{width:var(--sidebar-w);background:#0b2a4a;color:#fff;padding:20px 16px;box-sizing:border-box}
    .logo-section img{width:64px;height:64px;border-radius:8px}
    .logo-section h2{font-size:14px;margin-top:8px;line-height:1.1}
    .nav{margin-top:24px;display:flex;flex-direction:column;gap:8px}
    .nav a{color:inherit;text-decoration:none;padding:10px;border-radius:8px}
    .nav a.active{background:rgba(255,255,255,0.08)}
    .main{flex:1;padding:18px}
    .topbar{display:flex;justify-content:flex-end;align-items:center;gap:12px}
    .cards{display:flex;gap:12px;margin:16px 0}
    .card{flex:1;padding:16px;border-radius:8px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
    table{width:100%;border-collapse:collapse;background:#fff}
    thead th{background:#f1f5f9;padding:10px;text-align:left}
    td, th{padding:10px;border-bottom:1px solid #eee}
    .status.active{color:green}
    .status.pending{color:orange}
    .status.denied{color:red}
    .modal{display:none;position:fixed;inset:0;align-items:center;justify-content:center}
    .modal .modal-content{background:#fff;padding:16px;border-radius:8px;max-width:720px;width:100%}
    .search-container{display:flex;align-items:center;gap:8px}
    .search-container input{padding:8px;border-radius:6px;border:1px solid #ddd}
    .header-actions{display:flex;gap:12px;align-items:center}
    @media (max-width:900px){.sidebar{display:none}.main{padding:12px}}
  </style>
</head>
<body>
  <div class="container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="logo-section">
        <img src="../../images/logo.png" alt="Barangay Logo">
        <h2>BARANGAY E-SERVICES<br>AND COMPLAINT<br>MANAGEMENT SYSTEM</h2>
      </div>

      <nav class="nav">
        <a href="#" class="nav-item nav-dashboard active" data-target="dashboardSection"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="#" class="nav-item nav-users" data-target="usersSection"><i class="fas fa-users"></i> Users</a>
        <a href="#" class="nav-item nav-requests" data-target="requestsSection"><i class="fas fa-file-alt"></i> Requests</a>
        <a href="#" class="nav-item nav-complaints" data-target="complaintsSection"><i class="fas fa-exclamation-circle"></i> Complaints</a>
        <a href="#" class="nav-item nav-announcements" data-target="announcementsSection"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="#" class="nav-item" data-target="settingsSection"><i class="fas fa-cog"></i> Settings</a>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <header class="topbar">
        <span class="admin">ADMIN</span>
        <img src="../../images/ivan.png" alt="Admin Icon" class="admin-icon" style="width:36px;border-radius:6px">
        <button id="logoutBtn" class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </header>

      <!-- SECTIONS (as tabs) -->

      <!-- DASHBOARD -->
      <section id="dashboardSection" class="page-section">
        <h1>Dashboard Overview</h1>

        <?php if (isset($_SESSION['message'])): ?>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              // display session message in the message modal
              window.__sessionMessage = {
                status: '<?php echo $_SESSION['status'] ?? ''; ?>',
                message: '<?php echo addslashes($_SESSION['message'] ?? ''); ?>'
              };
            });
          </script>
          <?php unset($_SESSION['message'], $_SESSION['status']); ?>
        <?php endif; ?>

        <div class="cards">
          <div class="card blue">
            <i class="fas fa-users"></i>
            <p>Total Users</p>
            <h2><?= htmlspecialchars($user_count) ?></h2>
          </div>
          <div class="card green">
            <i class="fas fa-file-alt"></i>
            <p>Total Certificates Requested</p>
            <h2><?= htmlspecialchars($cert_count) ?></h2>
          </div>
          <div class="card red">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Total Complaints Filed</p>
            <h2><?= htmlspecialchars($complaint_count) ?></h2>
          </div>
        </div>

        <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 30px;">
          <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3 style="margin: 0; color: #1e3d8f;">Requests by Document Type</h3>
              <button onclick="refreshCharts()" style="padding: 5px 10px; background: #1e3d8f; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
            <canvas id="docTypeChart"></canvas>
          </div>
          <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3 style="margin: 0; color: #1e3d8f;">Request Statuses</h3>
              <button onclick="refreshCharts()" style="padding: 5px 10px; background: #1e3d8f; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
            <canvas id="reqStatusChart"></canvas>
          </div>
          <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3 style="margin: 0; color: #1e3d8f;">Complaint Statuses</h3>
              <button onclick="refreshCharts()" style="padding: 5px 10px; background: #1e3d8f; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
            <canvas id="compStatusChart"></canvas>
          </div>
        </div>

      </section>

      <!-- USERS -->
      <section id="usersSection" class="page-section" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
          <h2>Registered Users</h2>
          <div class="header-actions">
            <div class="search-container">
              <i class="fas fa-search"></i>
              <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchTable('userTable', this.value)">
            </div>
            <select id="roleFilter" onchange="filterUsers()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Roles</option>
              <option value="resident">Resident</option>
              <option value="staff">Staff</option>
              <option value="official">Official</option>
            </select>
            <select id="statusFilter" onchange="filterUsers()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="denied">Denied</option>
            </select>
            <button class="add-user-btn" onclick="addResident()"><i class="fas fa-user-plus"></i> Add User</button>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div id="bulkActions" style="display:none;margin-bottom:15px;padding:10px;background:#f8f9fa;border-radius:6px;border:1px solid #dee2e6">
          <span id="selectedCount">0 users selected</span>
          <button onclick="bulkApprove()" style="margin-left:10px;padding:5px 10px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check"></i> Bulk Approve</button>
          <button onclick="bulkDelete()" style="margin-left:5px;padding:5px 10px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-trash"></i> Bulk Delete</button>
        </div>

        <div style="margin-top:12px;overflow:auto">
          <table class="user-table" id="userTable">
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($users_result && $users_result->num_rows > 0): ?>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                  <tr data-role="<?= htmlspecialchars($user['role']) ?>" data-status="<?= htmlspecialchars($user['account_status']) ?>">
                    <td><input type="checkbox" class="user-checkbox" value="<?= $user['id'] ?>" onchange="updateBulkActions()"></td>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="role-badge role-<?= htmlspecialchars($user['role']) ?>"><?= ucfirst(htmlspecialchars($user['role'])) ?></span></td>
                    <td><span class="status <?= $user['account_status'] === 'pending' ? 'pending' : ($user['account_status'] === 'active' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($user['account_status'])) ?></span></td>
                    <td>
                      <button onclick="viewUser(<?= $user['id'] ?>)" title="View Details" style="margin-right:5px;padding:5px 8px;background:#17a2b8;color:white;border:none;border-radius:4px"><i class="fas fa-eye"></i></button>
                      <button onclick="editUser(<?= $user['id'] ?>)" title="Edit User" style="margin-right:5px;padding:5px 8px;background:#ffc107;color:white;border:none;border-radius:4px"><i class="fas fa-edit"></i></button>
                      <?php if ($user['account_status'] === 'pending'): ?>
                        <button onclick="updateStatus(<?= $user['id'] ?>, 'active')" title="Approve" style="margin-right:5px;padding:5px 8px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check"></i></button>
                      <?php endif; ?>
                      <button onclick="deleteUser(<?= $user['id'] ?>)" title="Delete" style="padding:5px 8px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" style="text-align:center">No users found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div id="pagination" style="margin-top:20px;text-align:center">
          <button id="prevPage" onclick="changePage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
          <span id="pageInfo">Page 1 of 1</span>
          <button id="nextPage" onclick="changePage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
        </div>
      </section>

      <!-- REQUESTS -->
      <section id="requestsSection" class="page-section" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
          <h2>Document Requests</h2>
          <div class="header-actions">
            <div class="search-container">
              <i class="fas fa-search"></i>
              <input type="text" id="requestSearch" placeholder="Search requests..." onkeyup="searchTable('requestsTable', this.value)">
            </div>
            <select id="requestStatusFilter" onchange="filterRequests()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="denied">Denied</option>
            </select>
            <select id="requestTypeFilter" onchange="filterRequests()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Types</option>
              <option value="Barangay Clearance">Barangay Clearance</option>
              <option value="Certificate of Residency">Certificate of Residency</option>
              <option value="Certificate of Indigency">Certificate of Indigency</option>
              <option value="Business Permit">Business Permit</option>
            </select>
          </div>
        </div>

        <!-- Bulk Actions for Requests -->
        <div id="requestBulkActions" style="display:none;margin-bottom:15px;padding:10px;background:#f8f9fa;border-radius:6px;border:1px solid #dee2e6">
          <span id="requestSelectedCount">0 requests selected</span>
          <button onclick="bulkApproveRequests()" style="margin-left:10px;padding:5px 10px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check"></i> Bulk Approve</button>
          <button onclick="bulkDenyRequests()" style="margin-left:5px;padding:5px 10px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-times"></i> Bulk Deny</button>
        </div>

        <div style="margin-top:12px;overflow:auto">
          <table class="user-table" id="requestsTable">
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllRequests" onchange="toggleSelectAllRequests()"></th>
                <th>Request ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Document Type</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Date Requested</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                <?php while ($req = $requests_result->fetch_assoc()): ?>
                  <tr data-status="<?= htmlspecialchars($req['r_status']) ?>" data-type="<?= htmlspecialchars($req['document_type']) ?>">
                    <td><input type="checkbox" class="request-checkbox" value="<?= $req['r_id'] ?>" onchange="updateRequestBulkActions()"></td>
                    <td><?= htmlspecialchars($req['r_id']) ?></td>
                    <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></td>
                    <td><?= htmlspecialchars($req['email']) ?></td>
                    <td><span class="doc-type-badge"><?= htmlspecialchars($req['document_type']) ?></span></td>
                    <td style="max-width:200px;word-break:break-word;"><?= htmlspecialchars($req['purpose']) ?></td>
                    <td><span class="status <?= $req['r_status'] === 'pending' ? 'pending' : ($req['r_status'] === 'approved' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($req['r_status'])) ?></span></td>
                    <td><?= htmlspecialchars($req['date_requested']) ?></td>
                    <td>
                      <button onclick="viewRequestDetails(<?= $req['r_id'] ?>)" title="View Details" style="margin-right:5px;padding:5px 8px;background:#17a2b8;color:white;border:none;border-radius:4px"><i class="fas fa-eye"></i></button>
                      <?php if ($req['r_status'] === 'pending'): ?>
                        <button onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'approved')" title="Approve" style="margin-right:5px;padding:5px 8px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check"></i></button>
                        <button onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'denied')" title="Deny" style="padding:5px 8px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-times"></i></button>
                      <?php else: ?>
                        <span style="color:#888;font-size:12px;">Completed</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="9" style="text-align:center;padding:40px;color:#666;">No requests found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination for Requests -->
        <div id="requestPagination" style="margin-top:20px;text-align:center">
          <button id="requestPrevPage" onclick="changeRequestPage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
          <span id="requestPageInfo">Page 1 of 1</span>
          <button id="requestNextPage" onclick="changeRequestPage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
        </div>
      </section>

      <!-- COMPLAINTS -->
      <section id="complaintsSection" class="page-section" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
          <h2>Filed Complaints</h2>
          <div class="header-actions">
            <div class="search-container">
              <i class="fas fa-search"></i>
              <input type="text" id="complaintSearch" placeholder="Search complaints..." onkeyup="searchTable('complaintsTable', this.value)">
            </div>
            <select id="complaintStatusFilter" onchange="filterComplaints()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Statuses</option>
              <option value="Pending">Pending</option>
              <option value="Resolved">Resolved</option>
              <option value="Dismissed">Dismissed</option>
            </select>
            <select id="complaintTypeFilter" onchange="filterComplaints()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Types</option>
              <option value="Noise Complaint">Noise Complaint</option>
              <option value="Environmental">Environmental</option>
              <option value="Public Safety">Public Safety</option>
              <option value="Infrastructure">Infrastructure</option>
              <option value="Service Quality">Service Quality</option>
              <option value="Other">Other</option>
            </select>
            <button class="add-user-btn" onclick="openComplaintReportModal()"><i class="fas fa-plus-circle"></i> New Report</button>
          </div>
        </div>

        <!-- Bulk Actions for Complaints -->
        <div id="complaintBulkActions" style="display:none;margin-bottom:15px;padding:10px;background:#f8f9fa;border-radius:6px;border:1px solid #dee2e6">
          <span id="complaintSelectedCount">0 complaints selected</span>
          <button onclick="bulkResolveComplaints()" style="margin-left:10px;padding:5px 10px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check-circle"></i> Bulk Resolve</button>
          <button onclick="bulkDismissComplaints()" style="margin-left:5px;padding:5px 10px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-times-circle"></i> Bulk Dismiss</button>
        </div>

        <div style="margin-top:12px;overflow:auto">
          <table class="user-table" id="complaintsTable">
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllComplaints" onchange="toggleSelectAllComplaints()"></th>
                <th>Complaint ID</th>
                <th>Reference No</th>
                <th>Complainant</th>
                <th>Email</th>
                <th>Type</th>
                <th>Location</th>
                <th>Date of Incident</th>
                <th>Status</th>
                <th>Date Filed</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($complaints_result && $complaints_result->num_rows > 0): ?>
                <?php while ($comp = $complaints_result->fetch_assoc()): ?>
                  <tr data-status="<?= htmlspecialchars($comp['status']) ?>" data-type="<?= htmlspecialchars($comp['complaint_type']) ?>">
                    <td><input type="checkbox" class="complaint-checkbox" value="<?= $comp['c_id'] ?>" onchange="updateComplaintBulkActions()"></td>
                    <td><?= htmlspecialchars($comp['c_id']) ?></td>
                    <td><span class="reference-badge"><?= htmlspecialchars($comp['reference_no']) ?></span></td>
                    <td><?= htmlspecialchars($comp['first_name'] . ' ' . $comp['last_name']) ?></td>
                    <td><?= htmlspecialchars($comp['email']) ?></td>
                    <td><span class="complaint-type-badge"><?= htmlspecialchars($comp['complaint_type']) ?></span></td>
                    <td style="max-width:150px;word-break:break-word;"><?= htmlspecialchars($comp['location']) ?></td>
                    <td><?= htmlspecialchars($comp['date_of_incident']) ?></td>
                    <td><span class="status <?= strtolower($comp['status']) === 'pending' ? 'pending' : (strtolower($comp['status']) === 'resolved' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($comp['status'])) ?></span></td>
                    <td><?= htmlspecialchars($comp['date_filed']) ?></td>
                    <td>
                      <button onclick="viewComplaintDetails(<?= $comp['c_id'] ?>)" title="View Details" style="margin-right:5px;padding:5px 8px;background:#17a2b8;color:white;border:none;border-radius:4px"><i class="fas fa-eye"></i></button>
                      <?php if (strtolower($comp['status']) === 'pending'): ?>
                        <button onclick="updateComplaintStatus(<?= $comp['c_id'] ?>, 'Resolved')" title="Resolve" style="margin-right:5px;padding:5px 8px;background:#28a745;color:white;border:none;border-radius:4px"><i class="fas fa-check-circle"></i></button>
                        <button onclick="updateComplaintStatus(<?= $comp['c_id'] ?>, 'Dismissed')" title="Dismiss" style="padding:5px 8px;background:#dc3545;color:white;border:none;border-radius:4px"><i class="fas fa-times-circle"></i></button>
                      <?php else: ?>
                        <span style="color:#888;font-size:12px;">Completed</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="11" style="text-align:center;padding:40px;color:#666;">No complaints filed yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination for Complaints -->
        <div id="complaintPagination" style="margin-top:20px;text-align:center">
          <button id="complaintPrevPage" onclick="changeComplaintPage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
          <span id="complaintPageInfo">Page 1 of 1</span>
          <button id="complaintNextPage" onclick="changeComplaintPage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
        </div>
      </section>

      <!-- ANNOUNCEMENTS (separate, NOT nested) -->
      <section id="announcementsSection" class="page-section" style="display:none">
        <div class="users-header">
          <h2>Announcements Management</h2>
          <div class="header-actions">
            <div class="search-container">
              <i class="fas fa-search"></i>
              <input type="text" id="announcementSearch" placeholder="Search announcements..." onkeyup="searchTable('announcementsTable', this.value)">
            </div>
            <select id="announcementStatusFilter" onchange="filterAnnouncements()" style="padding:8px;border-radius:6px;border:1px solid #ddd;margin-right:10px">
              <option value="">All Statuses</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
            <button class="add-user-btn" onclick="openAddAnnouncementModal()" style="background: linear-gradient(135deg, #1e3d8f, #3f51b5);"><i class="fas fa-plus"></i> Create Announcement</button>
          </div>
        </div>
        <p class="subtitle">Create, edit, and manage announcements for the barangay community.</p>

        <!-- Enhanced Add/Edit Modal -->
        <div id="addAnnouncementModal" class="modal">
          <div class="modal-content" style="max-width:700px">
            <span class="close-btn" onclick="closeModal('addAnnouncementModal')" style="float:right;cursor:pointer;font-size:24px;">&times;</span>
            <h3 id="announcementModalTitle" style="color: #1e3d8f; margin-bottom: 20px;"><i class="fas fa-bullhorn"></i> Create Announcement</h3>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #1e3d8f;">
              <form id="announcementForm" method="POST" action="add_announcement.php" enctype="multipart/form-data">
                <input type="hidden" name="ann_id" id="ann_id" value="">

                <!-- Title Section -->
                <div style="margin-bottom: 20px;">
                  <label for="ann_title" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">
                    <i class="fas fa-heading"></i> Announcement Title *
                  </label>
                  <input type="text" name="title" id="ann_title" required
                         style="width:100%;padding:12px;border:2px solid #e9ecef;border-radius:8px;font-size:14px;transition:all 0.3s;"
                         placeholder="Enter a clear, descriptive title for your announcement">
                </div>

                <!-- Details Section -->
                <div style="margin-bottom: 20px;">
                  <label for="ann_details" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">
                    <i class="fas fa-file-alt"></i> Announcement Details *
                  </label>
                  <textarea name="details" id="ann_details" rows="8" required
                            style="width:100%;padding:12px;border:2px solid #e9ecef;border-radius:8px;font-size:14px;resize:vertical;min-height:120px;transition:all 0.3s;"
                            placeholder="Provide detailed information about the announcement. Include important dates, locations, and any relevant instructions."></textarea>
                  <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Write clearly and concisely to ensure community members understand the information.
                  </small>
                </div>

                <!-- Image Upload Section -->
                <div style="margin-bottom: 20px;">
                  <label for="ann_image" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">
                    <i class="fas fa-image"></i> Announcement Image
                  </label>
                  <input type="file" name="image" id="ann_image" accept="image/*"
                         style="width:100%;padding:12px;border:2px solid #e9ecef;border-radius:8px;background:white;">
                  <div id="imageInfo" style="margin:8px 0;color:#666;font-size:12px;">
                    <i class="fas fa-lightbulb"></i> Optional: Upload an image to make your announcement more engaging (JPG, PNG, GIF - Max 5MB)
                  </div>
                  <div id="imagePreview" style="margin-top:10px;display:none;">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;max-height:150px;object-fit:cover;border-radius:8px;border:2px solid #e9ecef;">
                  </div>
                </div>

                <!-- Status Section -->
                <div style="margin-bottom: 25px;">
                  <label for="ann_status" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">
                    <i class="fas fa-toggle-on"></i> Publication Status *
                  </label>
                  <select name="status" id="ann_status" style="padding:12px;border:2px solid #e9ecef;border-radius:8px;font-size:14px;min-width:200px;">
                    <option value="published">📢 Published - Visible to all users</option>
                    <option value="draft">📝 Draft - Save for later editing</option>
                  </select>
                  <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    <i class="fas fa-eye"></i> Published announcements will be visible to all barangay residents immediately.
                  </small>
                </div>

                <!-- Form Actions -->
                <div style="display:flex;gap:12px;justify-content:flex-end;padding-top:20px;border-top:1px solid #dee2e6;">
                  <button type="button" onclick="closeModal('addAnnouncementModal')"
                          style="padding:12px 24px;background:#6c757d;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500;">
                    <i class="fas fa-times"></i> Cancel
                  </button>
                  <button type="submit"
                          style="padding:12px 24px;background:linear-gradient(135deg,#1e3d8f,#3f51b5);color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500;">
                    <i class="fas fa-paper-plane"></i> <span id="submitBtnText">Create Announcement</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Image Preview Modal -->
        <div id="imagePreviewModal" class="modal">
          <div class="modal-content" style="max-width:800px;text-align:center">
            <span class="close-btn" onclick="closeModal('imagePreviewModal')" style="float:right;cursor:pointer">&times;</span>
            <h3 style="color: #1e3d8f; margin-bottom: 20px;">Image Preview</h3>
            <img id="previewImage" src="" alt="Preview" style="max-width:100%;height:auto;border-radius:8px;margin-top:20px;border:3px solid #e9ecef;">
            <div style="margin-top:20px;text-align:right">
              <button onclick="closeModal('imagePreviewModal')" style="padding:10px 20px;background:#1e3d8f;color:white;border:none;border-radius:6px;">Close</button>
            </div>
          </div>
        </div>

        <!-- Announcements Table -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
          <div style="padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #1e3d8f;"><i class="fas fa-list"></i> All Announcements</h3>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Manage your announcements below</p>
          </div>

          <div style="overflow:auto">
            <table class="user-table" id="announcementsTable">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th style="width: 200px;">Title</th>
                  <th>Details</th>
                  <th style="width: 100px;">Image</th>
                  <th style="width: 100px;">Status</th>
                  <th style="width: 120px;">Posted By</th>
                  <th style="width: 120px;">Posted At</th>
                  <th style="width: 150px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($announcements_result && $announcements_result->num_rows > 0): ?>
                  <?php while ($ann = $announcements_result->fetch_assoc()): ?>
                    <tr data-status="<?= htmlspecialchars($ann['status']) ?>">
                      <td><span class="announcement-id-badge" style="background: #e3f2fd; color: #1976d2;"><?= htmlspecialchars($ann['ann_id']) ?></span></td>
                      <td style="max-width:200px;word-break:break-word;font-weight:500;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                          <i class="fas fa-heading" style="color: #666;"></i>
                          <?= htmlspecialchars($ann['title']) ?>
                        </div>
                      </td>
                      <td style="max-width:300px;white-space:pre-wrap;word-break:break-word;font-size:13px;">
                        <?= htmlspecialchars(substr($ann['details'], 0, 150)) ?><?php if (strlen($ann['details']) > 150): ?>...<?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($ann['image_path']) && file_exists(__DIR__ . '/../../' . $ann['image_path'])): ?>
                          <img src="../../<?= htmlspecialchars($ann['image_path']) ?>" alt="thumb"
                               style="width:60px;height:40px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid #e9ecef;"
                               onclick="previewImage('../../<?= htmlspecialchars($ann['image_path']) ?>')"
                               title="Click to preview">
                        <?php else: ?>
                          <span style="color:#999;font-size:11px;"><i class="fas fa-image"></i> None</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="status <?= $ann['status'] === 'published' ? 'active' : 'pending' ?>" style="font-size: 12px;">
                          <i class="fas fa-<?= $ann['status'] === 'published' ? 'check-circle' : 'drafting-compass' ?>"></i>
                          <?= ucfirst(htmlspecialchars($ann['status'])) ?>
                        </span>
                      </td>
                      <td style="font-size:13px;">
                        <i class="fas fa-user" style="color: #666;"></i>
                        <?= htmlspecialchars($ann['first_name'] . ' ' . $ann['last_name']) ?>
                      </td>
                      <td style="font-size:12px;">
                        <div><i class="fas fa-calendar" style="color: #666;"></i> <?= htmlspecialchars(date('M d, Y', strtotime($ann['created_at']))) ?></div>
                        <div style="color:#666;margin-top:2px;"><i class="fas fa-clock" style="color: #666;"></i> <?= htmlspecialchars(date('H:i', strtotime($ann['created_at']))) ?></div>
                      </td>
                      <td>
                        <button onclick="editAnnouncement(<?= $ann['ann_id'] ?>)" title="Edit Announcement"
                                style="margin-right:5px;padding:6px 10px;background:#ffc107;color:#212529;border:none;border-radius:6px;font-size:12px;">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteAnnouncement(<?= $ann['ann_id'] ?>)" title="Delete Announcement"
                                style="padding:6px 10px;background:#dc3545;color:white;border:none;border-radius:6px;font-size:12px;">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" style="text-align:center;padding:60px;color:#666;">
                      <i class="fas fa-bullhorn" style="font-size: 48px; color: #e9ecef; margin-bottom: 15px;"></i>
                      <div style="font-size: 18px; font-weight: 500; margin-bottom: 10px;">No announcements yet</div>
                      <div>Create your first announcement to communicate with the barangay community.</div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination for Announcements -->
        <div id="announcementPagination" style="margin-top:20px;text-align:center">
          <button id="announcementPrevPage" onclick="changeAnnouncementPage(-1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Previous</button>
          <span id="announcementPageInfo">Page 1 of 1</span>
          <button id="announcementNextPage" onclick="changeAnnouncementPage(1)" disabled style="padding:8px 12px;margin:0 5px;border:1px solid #ddd;border-radius:4px;background:white">Next</button>
        </div>
      </section>

      <!-- SETTINGS placeholder -->
      <section id="settingsSection" class="page-section" style="display:none">
        <h2>Settings</h2>
        <p>Settings coming soon.</p>
      </section>

    </main>
  </div>

  <!-- ADD RESIDENT MODAL (shared) -->
  <div id="residentModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal('residentModal')" style="float:right;cursor:pointer">&times;</span>
      <h3 id="residentModalTitle">Add User Information</h3>
      <form id="residentForm" method="POST" action="add_resident.php">
        <input type="hidden" name="user_id" id="user_id" value="">
        <input type="text" name="first_name" id="first_name" placeholder="First Name" required style="width:100%;padding:8px;margin-bottom:8px">
        <input type="text" name="last_name" id="last_name" placeholder="Last Name" required style="width:100%;padding:8px;margin-bottom:8px">
        <input type="date" name="birth_date" id="birth_date" required style="width:100%;padding:8px;margin-bottom:8px">
        <select name="gender" id="gender" required style="width:100%;padding:8px;margin-bottom:8px">
          <option value="">Select Gender</option>
          <option>Male</option>
          <option>Female</option>
        </select>
        <input type="email" name="email" id="email" placeholder="Email Address" required style="width:100%;padding:8px;margin-bottom:8px">
        <div id="passwordField">
          <input type="password" name="password" id="password" placeholder="Password" required style="width:100%;padding:8px;margin-bottom:8px">
        </div>
        <div id="emailNote" style="display:none;color:#666;font-size:12px;margin-bottom:8px">
          Note: Email cannot be changed when editing a user.
        </div>
        <select name="role" id="role" required style="width:100%;padding:8px;margin-bottom:8px">
          <option value="resident">Resident</option>
          <option value="staff">Staff</option>
          <option value="official">Official</option>
        </select>
        <select name="account_status" id="account_status" required style="width:100%;padding:8px;margin-bottom:8px">
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="denied">Denied</option>
        </select>
        <div style="display:flex;gap:8px">
          <button type="submit" name="register" id="submitBtn">Submit</button>
          <button type="button" onclick="closeModal('residentModal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- VIEW USER MODAL -->
  <div id="viewUserModal" class="modal">
    <div class="modal-content" style="max-width:600px">
      <span class="close-btn" onclick="closeModal('viewUserModal')" style="float:right;cursor:pointer">&times;</span>
      <h3>User Details</h3>
      <div id="userDetails" style="line-height:1.6">
        <!-- User details will be loaded here -->
      </div>
      <div style="margin-top:20px;text-align:right">
        <button onclick="closeModal('viewUserModal')">Close</button>
      </div>
    </div>
  </div>

  <!-- MESSAGE MODAL -->
  <div id="messageModal" class="modal" style="background:rgba(0,0,0,0.3);z-index:1000;">
    <div class="modal-content" style="max-width:400px;text-align:center;padding:30px 20px">
      <h2 id="messageTitle">Status</h2>
      <p id="messageText" style="font-size:15px;margin:10px 0 20px"></p>
      <button onclick="closeModal('messageModal')" style="width:100px;cursor:pointer">OK</button>
    </div>
  </div>

  <!-- SCRIPTS: navigation, modals, small helpers -->
  <script>
    // Tab navigation
    document.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', function(e){
        e.preventDefault();
        const targetId = this.dataset.target;
        // hide all sections
        document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');
        // show target
        const target = document.getElementById(targetId);
        if (target) target.style.display = 'block';
        // active class
        document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Simple modal helpers
    function openModal(id){
      const m = document.getElementById(id);
      if (m) m.style.display = 'flex';
    }
    function closeModal(id){
      const m = document.getElementById(id);
      if (m) m.style.display = 'none';
    }

    // Preview image
    function previewImage(src){
      const modalId = 'imagePreviewModal';
      let modal = document.getElementById(modalId);
      if (!modal){
        modal = document.createElement('div'); modal.id = modalId; modal.className = 'modal';
        modal.innerHTML = '<div class="modal-content" style="max-width:800px;text-align:center"><span style="float:right;cursor:pointer" onclick="closeModal(\'imagePreviewModal\')">&times;</span><img id="previewImg" style="max-width:100%;height:auto;border-radius:8px"></div>';
        document.body.appendChild(modal);
      }
      document.getElementById('previewImg').src = src;
      openModal(modalId);
    }

    // table search helper (simple)
    function searchTable(tableId, query){
      const q = query.toLowerCase();
      const tbl = document.getElementById(tableId);
      if (!tbl) return;
      Array.from(tbl.tBodies[0].rows).forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.indexOf(q) === -1 ? 'none' : '';
      });
    }

    // logout
    function logout(){ if (!confirm('Logout?')) return; window.location = '../../logout.php'; }

    // show session message if any
    document.addEventListener('DOMContentLoaded', ()=>{
      if (window.__sessionMessage && window.__sessionMessage.message){
        document.getElementById('messageTitle').innerText = window.__sessionMessage.status || 'Notice';
        document.getElementById('messageText').innerText = window.__sessionMessage.message;
        openModal('messageModal');
      }
    });
  </script>
  <script src="../../scripts/official_dashboard.js"></script>

  <!-- User Management Scripts -->
  <script>
    let currentPage = 1;
    const itemsPerPage = 10;
    let allUsers = [];

    // Load all users data
    document.addEventListener('DOMContentLoaded', function() {
      loadUsers();
    });

    function loadUsers() {
      // In a real implementation, this would fetch data via AJAX
      // For now, we'll work with the existing table data
      const rows = Array.from(document.querySelectorAll('#userTable tbody tr'));
      allUsers = rows.map(row => ({
        element: row,
        role: row.dataset.role,
        status: row.dataset.status
      }));
      renderPage(1);
    }

    function filterUsers() {
      const roleFilter = document.getElementById('roleFilter').value;
      const statusFilter = document.getElementById('statusFilter').value;

      allUsers.forEach(user => {
        const roleMatch = !roleFilter || user.role === roleFilter;
        const statusMatch = !statusFilter || user.status === statusFilter;
        user.element.style.display = (roleMatch && statusMatch) ? '' : 'none';
      });

      currentPage = 1;
      renderPage(currentPage);
    }

    function renderPage(page) {
      const start = (page - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      let visibleCount = 0;

      allUsers.forEach((user, index) => {
        if (user.element.style.display !== 'none') {
          user.element.style.display = (visibleCount >= start && visibleCount < end) ? '' : 'none';
          visibleCount++;
        }
      });

      const totalPages = Math.ceil(visibleCount / itemsPerPage);
      document.getElementById('pageInfo').textContent = `Page ${page} of ${totalPages}`;
      document.getElementById('prevPage').disabled = page === 1;
      document.getElementById('nextPage').disabled = page === totalPages;
    }

    function changePage(direction) {
      currentPage += direction;
      renderPage(currentPage);
    }

    function toggleSelectAll() {
      const selectAll = document.getElementById('selectAll');
      const checkboxes = document.querySelectorAll('.user-checkbox');
      checkboxes.forEach(cb => cb.checked = selectAll.checked);
      updateBulkActions();
    }

    function updateBulkActions() {
      const selected = document.querySelectorAll('.user-checkbox:checked');
      const bulkActions = document.getElementById('bulkActions');
      const selectedCount = document.getElementById('selectedCount');

      if (selected.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${selected.length} users selected`;
      } else {
        bulkActions.style.display = 'none';
      }
    }

    function bulkApprove() {
      const selected = document.querySelectorAll('.user-checkbox:checked');
      if (selected.length === 0) return;

      if (confirm(`Approve ${selected.length} users?`)) {
        selected.forEach(cb => {
          updateStatus(cb.value, 'active');
        });
      }
    }

    function bulkDelete() {
      const selected = document.querySelectorAll('.user-checkbox:checked');
      if (selected.length === 0) return;

      if (confirm(`Delete ${selected.length} users? This action cannot be undone.`)) {
        const userIds = Array.from(selected).map(cb => cb.value);
        // Create a form to submit the array of IDs
        const form = new FormData();
        form.append('ids', JSON.stringify(userIds));

        fetch('delete_user.php', {
            method: 'POST',
            body: form
        })
        .then(res => res.text())
        .then(data => {
            showMessage('Success', `${userIds.length} users deleted successfully.`);
            setTimeout(() => location.reload(), 1000);
        })
        .catch(err => showMessage('Error', 'Failed to delete users.'));
      }
    }
    
    function viewUser(id) {
      // Show loading state
      document.getElementById('userDetails').innerHTML = `
        <p><strong>User ID:</strong> ${id}</p>
        <p><strong>Name:</strong> Loading...</p>
        <p><strong>Email:</strong> Loading...</p>
        <p><strong>Role:</strong> Loading...</p>
        <p><strong>Status:</strong> Loading...</p>
        <p><strong>Registration Date:</strong> Loading...</p>
      `;
      openModal('viewUserModal');

      // Fetch user data via AJAX
      fetch(`get_user.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const user = data.user;
            document.getElementById('userDetails').innerHTML = `
              <p><strong>User ID:</strong> ${user.id}</p>
              <p><strong>Full Name:</strong> ${user.first_name} ${user.last_name}</p>
              <p><strong>Email:</strong> ${user.email}</p>
              <p><strong>Birth Date:</strong> ${user.birth_date || 'Not specified'}</p>
              <p><strong>Gender:</strong> ${user.gender || 'Not specified'}</p>
              <p><strong>Role:</strong> ${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Not specified'}</p>
              <p><strong>Account Status:</strong> ${user.account_status ? user.account_status.charAt(0).toUpperCase() + user.account_status.slice(1) : 'Not specified'}</p>
              <p><strong>Registration Date:</strong> ${user.created_at || 'Not available'}</p>
            `;
          } else {
            document.getElementById('userDetails').innerHTML = `<p style="color: red;">Error: ${data.message}</p>`;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('userDetails').innerHTML = `<p style="color: red;">Error loading user data.</p>`;
        });
    }
  </script>

  <!-- Chart.js Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Requests by Document Type - Bar Chart
      const docTypeCtx = document.getElementById('docTypeChart').getContext('2d');
      new Chart(docTypeCtx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($doc_types); ?>,
          datasets: [{
            label: 'Number of Requests',
            data: <?php echo json_encode($doc_counts); ?>,
            backgroundColor: 'rgba(30, 61, 143, 0.6)',
            borderColor: 'rgba(30, 61, 143, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Request Statuses - Pie Chart
      const reqStatusCtx = document.getElementById('reqStatusChart').getContext('2d');
      new Chart(reqStatusCtx, {
        type: 'pie',
        data: {
          labels: <?php echo json_encode($req_statuses); ?>,
          datasets: [{
            data: <?php echo json_encode($req_status_counts); ?>,
            backgroundColor: [
              'rgba(255, 193, 7, 0.6)', // pending
              'rgba(40, 167, 69, 0.6)', // approved
              'rgba(220, 53, 69, 0.6)'  // denied
            ],
            borderColor: [
              'rgba(255, 193, 7, 1)',
              'rgba(40, 167, 69, 1)',
              'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true
        }
      });

      // Complaint Statuses - Pie Chart
      const compStatusCtx = document.getElementById('compStatusChart').getContext('2d');
      new Chart(compStatusCtx, {
        type: 'pie',
        data: {
          labels: <?php echo json_encode($comp_statuses); ?>,
          datasets: [{
            data: <?php echo json_encode($comp_status_counts); ?>,
            backgroundColor: [
              'rgba(255, 193, 7, 0.6)', // pending
              'rgba(40, 167, 69, 0.6)', // resolved
              'rgba(220, 53, 69, 0.6)'  // dismissed
            ],
            borderColor: [
              'rgba(255, 193, 7, 1)',
              'rgba(40, 167, 69, 1)',
              'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true
        }
      });
    });
  </script>

</body>
</html>
