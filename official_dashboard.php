<?php
session_start();
require 'config.php';

// Count total users
$user_count_query = "SELECT COUNT(*) AS total_users FROM tbl_users";
$user_count_result = $conn->query($user_count_query);
$user_count = 0;
if ($user_count_result && $row = $user_count_result->fetch_assoc()) {
    $user_count = $row['total_users'];
}

// Count total certificates requested (document_type = 'Barangay Certificate')
$cert_count_query = "SELECT COUNT(*) AS total_certificates FROM tbl_requests";
$cert_count_result = $conn->query($cert_count_query);
$cert_count = 0;
if ($cert_count_result && $row = $cert_count_result->fetch_assoc()) {
    $cert_count = $row['total_certificates'];
}

// Fetch all requests
$requests_query = "SELECT r.r_id, r.first_name, r.last_name, r.document_type, r.purpose, r.r_status, r.date_requested, u.email 
                   FROM tbl_requests r
                   LEFT JOIN tbl_users u ON r.id = u.id
                   ORDER BY r.r_id DESC";
$requests_result = $conn->query($requests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/Official_dashboard.css">

</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo-section">
        <img src="images/ivan.png" alt="Barangay Logo">
        <h2>BARANGAY E-SERVICES<br>AND COMPLAINT<br>MANAGEMENT SYSTEM</h2>
      </div>
      <nav class="nav">
        <a href="#" class="active">Dashboard</a>
        <a href="#">Announcements</a>
        <a href="#">Calendar</a>
        <a href="#">Settings</a>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="main">
      <header class="topbar">
        <span class="admin">ADMIN</span>
        <img src="images/ivan.png" alt="Admin Icon" class="admin-icon">
      </header>

      <section class="dashboard">
        <h1>Dashboard Overview</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                // Reopen the Add Resident modal
                openModal('<?php echo $_GET['modal'] ?? 'residentModal'; ?>');
                // Show the message modal
                showMessage('<?php echo ucfirst($_SESSION['status']); ?>', '<?php echo $_SESSION['message']; ?>');
              });
            </script>
        <?php unset($_SESSION['message']); unset($_SESSION['status']); ?>
        <?php endif; ?>

        <div class="cards">
          <div class="card blue">
            <p>Total Users</p>
            <span><?= $user_count ?></span>
          </div>
          <div class="card yellow">
            <p>Total Certificates Requested</p>
            <span><?= $cert_count ?></span>
          </div>
          <div class="card green">
            <p>Total Complaints Filed</p>
            <!-- You can add a similar counter for complaints if you want -->
          </div>
        </div>
        <?php
          require 'config.php';

          $query = "SELECT id, first_name, last_name, email, role, account_status FROM tbl_users ORDER BY id DESC";
          $result = $conn->query($query);
        ?>

          <!-- === Registered Users Table === -->
          <section class="users-section">
            <div class="users-header">
              <h2>Registered Users</h2>

              <div class="header-actions">
                <div class="search-container">
                  <i class="fas fa-search"></i>
                  <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
                </div>
                <button class="add-user-btn" onclick="openModal('residentModal')">
                  <i class="fas fa-user-plus"></i> Add User
                </button>
              </div>
            </div>

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
                <?php if ($result->num_rows > 0): ?>
                  <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id']; ?></td>
                      <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                      <td><?= htmlspecialchars($row['email']); ?></td>
                      <td><?= ucfirst($row['role']); ?></td>
                      <td>
                        <span class="status <?= $row['account_status'] === 'pending' ? 'pending' : ($row['account_status'] === 'active' ? 'active' : ''); ?>">
                          <?= ucfirst($row['account_status']); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($row['account_status'] === 'pending'): ?>
                          <button class="approve-btn" onclick="updateStatus(<?= $row['id']; ?>, 'active')">Approve</button>
                        <?php endif; ?>
                        <button class="delete-btn" onclick="deleteUser(<?= $row['id']; ?>)">Delete</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="6" style="text-align:center;">No users found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </section>
          <!-- === Current Requests Table === -->
          <section class="users-section requests-section">
  <div class="users-header">
    <h2>Current Requests</h2>
  </div>
  <table class="user-table" id="requestsTable">
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
        <?php while($req = $requests_result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($req['r_id']) ?></td>
            <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></td>
            <td><?= htmlspecialchars($req['email']) ?></td>
            <td><?= htmlspecialchars($req['document_type']) ?></td>
            <td><?= htmlspecialchars($req['purpose']) ?></td>
            <td>
              <span class="status <?= $req['r_status'] === 'pending' ? 'pending' : ($req['r_status'] === 'approved' ? 'active' : 'denied') ?>">
                <?= ucfirst($req['r_status']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($req['date_requested']) ?></td>
            <td>
              <?php if ($req['r_status'] === 'pending'): ?>
                <button class="approve-btn" onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'approved')">Approve</button>
                <button class="deny-btn" onclick="updateRequestStatus(<?= $req['r_id'] ?>, 'denied')">Deny</button>
              <?php else: ?>
                <span style="color:#888;">No action</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No requests found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>
        </main>
  </div> 

    <!-- ✅ Add Resident Modal -->
  <div id="residentModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal('residentModal')">&times;</span>
      <h2>Add User Information</h2>
      <form method="POST" action="add_resident.php">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="date" name="birth_date" required>
        <select name="gender" required>
          <option value="">Select Gender</option>
          <option>Male</option>
          <option>Female</option>
        </select>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
          <option value="resident">Resident</option>
          <option value="staff">Staff</option>
          <option value="official">Official</option>
        </select>
        <button type="submit" name="register">Submit</button>
      </form>
    </div>
  </div>


<!-- ✅ Message Modal -->
<div id="messageModal" class="modal" style="background: rgba(0,0,0,0.3); z-index: 1000;">
  <div class="modal-content" style="max-width: 400px; text-align: center; padding: 30px 20px;">
    <h2 id="messageTitle">Status</h2>
    <p id="messageText" style="font-size: 15px; margin: 10px 0 20px;"></p>
    <button onclick="closeModal('messageModal')" style="width: 100px; cursor: pointer;">OK</button>
  </div>
</div>

<script src="scripts/official_dashboard.js"></script>

</body>
</html>
