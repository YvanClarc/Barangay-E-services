<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles/Official_dashboard.css" />
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo-section">
        <img src="images/ivan.png" alt="Barangay Logo" />
        <h2>Barangay<br>E-Services and Complaint Management System</h2>
      </div>

      <div class="nav">
        <a href="#" class="active">Dashboard</a>
        <a href="#">Announcements</a>
        <a href="#">Request Certificate</a>
        <a href="#">File Complaint</a>
        <a href="#">My Requests</a>
        <a href="#">Settings</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main">
      <div class="topbar">
        <span class="admin">USER</span>
        <img src="images/ivan.png" alt="User Icon" class="admin-icon" />
      </div>

      <div class="dashboard">
        <h1>Dashboard Overview</h1>
        <div class="cards">
          <div class="card blue">
            <p>Total Certificates Requested</p>
            <span>3</span>
          </div>
          <div class="card green">
            <p>Total Complaints Filed</p>
            <span>1</span>
          </div>
          <div class="card yellow">
            <p>Pending Requests</p>
            <span>2</span>
          </div>
          <div class="card orange">
            <p>Completed Requests</p>
            <span>5</span>
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
              <tr>
                <td>001</td>
                <td>Barangay Clearance</td>
                <td>Oct 5, 2025</td>
                <td><span class="status active">Approved</span></td>
                <td>
                  <button class="approve-btn">View</button>
                </td>
              </tr>
              <tr>
                <td>002</td>
                <td>Complaint Report</td>
                <td>Oct 7, 2025</td>
                <td><span class="status pending">Pending</span></td>
                <td>
                  <button class="approve-btn">View</button>
                </td>
              </tr>
              <tr>
                <td>003</td>
                <td>Business Permit</td>
                <td>Sep 30, 2025</td>
                <td><span class="status active">Approved</span></td>
                <td>
                  <button class="approve-btn">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
