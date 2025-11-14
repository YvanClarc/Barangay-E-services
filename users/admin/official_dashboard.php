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
        <img src="../../images/ivan.png" alt="Logo">
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
          <div class="card">
            <p>Total Users</p>
            <h2><?= htmlspecialchars($user_count) ?></h2>
          </div>
          <div class="card">
            <p>Total Certificates Requested</p>
            <h2><?= htmlspecialchars($cert_count) ?></h2>
          </div>
          <div class="card">
            <p>Total Complaints Filed</p>
            <h2><?= htmlspecialchars($complaint_count) ?></h2>
          </div>
        </div>

      </section>

      <!-- USERS -->
      <section id="usersSection" class="page-section" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h2>Registered Users</h2>
          <div class="header-actions">
            <div class="search-container">
              <i class="fas fa-search"></i>
              <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchTable('userTable', this.value)">
            </div>
            <button class="add-user-btn" onclick="openModal('residentModal')"><i class="fas fa-user-plus"></i> Add User</button>
          </div>
        </div>

        <div style="margin-top:12px;overflow:auto">
          <table class="user-table" id="userTable">
            <thead>
              <tr>
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
                  <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                    <td><span class="status <?= $user['account_status'] === 'pending' ? 'pending' : ($user['account_status'] === 'active' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($user['account_status'])) ?></span></td>
                    <td>
                      <?php if ($user['account_status'] === 'pending'): ?>
                        <button onclick="updateStatus(<?= $user['id'] ?>, 'active')">Approve</button>
                      <?php endif; ?>
                      <button onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" style="text-align:center">No users found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- REQUESTS -->
      <section id="requestsSection" class="page-section" style="display:none">
        <h2>Current Requests</h2>
        <div style="margin-top:12px;overflow:auto">
          <table id="requestsTable">
            <thead>
              <tr>
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
                  <tr>
                    <td><?= htmlspecialchars($req['r_id']) ?></td>
                    <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></td>
                    <td><?= htmlspecialchars($req['email']) ?></td>
                    <td><?= htmlspecialchars($req['document_type']) ?></td>
                    <td><?= htmlspecialchars($req['purpose']) ?></td>
                    <td><span class="status <?= $req['r_status'] === 'pending' ? 'pending' : ($req['r_status'] === 'approved' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($req['r_status'])) ?></span></td>
                    <td><?= htmlspecialchars($req['date_requested']) ?></td>
                    <td>
                      <?php if ($req['r_status'] === 'pending'): ?>
                        <button onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'approved')">Approve</button>
                        <button onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'denied')">Deny</button>
                      <?php else: ?>
                        <span style="color:#888">No action</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="8" style="text-align:center">No requests found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- COMPLAINTS -->
      <section id="complaintsSection" class="page-section" style="display:none">
        <h2>Filed Complaints</h2>
        <div style="margin-top:12px;overflow:auto">
          <table id="complaintsTable">
            <thead>
              <tr>
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
                  <tr>
                    <td><?= htmlspecialchars($comp['c_id']) ?></td>
                    <td><?= htmlspecialchars($comp['reference_no']) ?></td>
                    <td><?= htmlspecialchars($comp['first_name'] . ' ' . $comp['last_name']) ?></td>
                    <td><?= htmlspecialchars($comp['email']) ?></td>
                    <td><?= htmlspecialchars($comp['complaint_type']) ?></td>
                    <td><?= htmlspecialchars($comp['location']) ?></td>
                    <td><?= htmlspecialchars($comp['date_of_incident']) ?></td>
                    <td><span class="status <?= strtolower($comp['status']) === 'pending' ? 'pending' : (strtolower($comp['status']) === 'resolved' ? 'active' : 'denied') ?>"><?= ucfirst(htmlspecialchars($comp['status'])) ?></span></td>
                    <td><?= htmlspecialchars($comp['date_filed']) ?></td>
                    <td>
                      <?php if (strtolower($comp['status']) === 'pending'): ?>
                        <button onclick="updateComplaintStatus(<?= $comp['c_id'] ?>, 'Resolved')">Resolve</button>
                        <button onclick="updateComplaintStatus(<?= $comp['c_id'] ?>, 'Dismissed')">Dismiss</button>
                      <?php else: ?>
                        <span style="color:#888">No action</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="10" style="text-align:center">No complaints filed yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ANNOUNCEMENTS (separate, NOT nested) -->
      <section id="announcementsSection" class="page-section" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h2>Announcements</h2>
          <button class="add-user-btn" onclick="openModal('addAnnouncementModal')"><i class="fas fa-bullhorn"></i> New Announcement</button>
        </div>

        <!-- Add/Edit Modal -->
        <div id="addAnnouncementModal" class="modal">
          <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addAnnouncementModal')" style="float:right;cursor:pointer">&times;</span>
            <h3 id="announcementModalTitle">Post Announcement</h3>
            <form id="announcementForm" method="POST" action="add_announcement.php" enctype="multipart/form-data">
              <input type="hidden" name="ann_id" id="ann_id" value="">
              <label>Title</label>
              <input type="text" name="title" id="ann_title" required style="width:100%;padding:8px;margin-bottom:8px">
              <label>Details</label>
              <textarea name="details" id="ann_details" rows="5" required style="width:100%;padding:8px;margin-bottom:8px"></textarea>
              <label>Image (optional)</label>
              <input type="file" name="image" id="ann_image" accept="image/*"><br>
              <label>Status</label>
              <select name="status" id="ann_status" style="padding:8px;margin-top:6px">
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
              <div style="display:flex;gap:8px;margin-top:12px">
                <button type="submit">Post Announcement</button>
                <button type="button" onclick="closeModal('addAnnouncementModal')">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <div style="margin-top:12px;overflow:auto">
          <table id="announcementsTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Details</th>
                <th>Image</th>
                <th>Status</th>
                <th>Posted By</th>
                <th>Posted At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($announcements_result && $announcements_result->num_rows > 0): ?>
                <?php while ($ann = $announcements_result->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($ann['ann_id']) ?></td>
                    <td><?= htmlspecialchars($ann['title']) ?></td>
                    <td style="max-width:300px;white-space:pre-wrap;word-break:break-word"><?= htmlspecialchars($ann['details']) ?></td>
                    <td>
                      <?php if (!empty($ann['image_path']) && file_exists(__DIR__ . '/../../' . $ann['image_path'])): ?>
                        <img src="../../<?= htmlspecialchars($ann['image_path']) ?>" alt="thumb" style="width:84px;height:56px;object-fit:cover;border-radius:6px;cursor:pointer" onclick="previewImage('../../<?= htmlspecialchars($ann['image_path']) ?>')">
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td><span class="status <?= $ann['status'] === 'published' ? 'active' : 'pending' ?>"><?= htmlspecialchars($ann['status']) ?></span></td>
                    <td><?= htmlspecialchars($ann['first_name'] . ' ' . $ann['last_name']) ?></td>
                    <td><?= htmlspecialchars($ann['created_at']) ?></td>
                    <td>
                      <button onclick="editAnnouncement(<?= $ann['ann_id'] ?>)">Edit</button>
                      <button onclick="deleteAnnouncement(<?= $ann['ann_id'] ?>)">Delete</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="8" style="text-align:center">No announcements yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
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
      <h3>Add User Information</h3>
      <form method="POST" action="add_resident.php">
        <input type="text" name="first_name" placeholder="First Name" required style="width:100%;padding:8px;margin-bottom:8px">
        <input type="text" name="last_name" placeholder="Last Name" required style="width:100%;padding:8px;margin-bottom:8px">
        <input type="date" name="birth_date" required style="width:100%;padding:8px;margin-bottom:8px">
        <select name="gender" required style="width:100%;padding:8px;margin-bottom:8px">
          <option value="">Select Gender</option>
          <option>Male</option>
          <option>Female</option>
        </select>
        <input type="email" name="email" placeholder="Email Address" required style="width:100%;padding:8px;margin-bottom:8px">
        <input type="password" name="password" placeholder="Password" required style="width:100%;padding:8px;margin-bottom:8px">
        <select name="role" required style="width:100%;padding:8px;margin-bottom:8px">
          <option value="resident">Resident</option>
          <option value="staff">Staff</option>
          <option value="official">Official</option>
        </select>
        <div style="display:flex;gap:8px">
          <button type="submit" name="register">Submit</button>
          <button type="button" onclick="closeModal('residentModal')">Cancel</button>
        </div>
      </form>
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

    // Placeholder action functions (should call your server endpoints)
    function updateStatus(id, status){ if (!confirm('Change status?')) return; /* Ajax call here */ alert('Would change status for '+id+' -> '+status); }
    function deleteUser(id){ if (!confirm('Delete user?')) return; /* Ajax call here */ alert('Would delete user '+id); }
    function updateRequestStatus(id, status){ if (!confirm('Update request?')) return; alert('Would update request '+id+' -> '+status); }
    function updateComplaintStatus(id, status){ if (!confirm('Update complaint?')) return; alert('Would update complaint '+id+' -> '+status); }
    function editAnnouncement(id){ /* Fill modal with values via ajax */ document.getElementById('announcementModalTitle').innerText = 'Edit Announcement'; document.getElementById('ann_id').value = id; openModal('addAnnouncementModal'); }
    function deleteAnnouncement(id){ if (!confirm('Delete announcement?')) return; alert('Would delete announcement '+id); }

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

</body>
</html>
