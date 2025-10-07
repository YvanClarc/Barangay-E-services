<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/Official_dashboard.css">
  <style>
    /* ==== MODAL STYLES ==== */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #fff;
      padding: 25px;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.9);}
      to {opacity: 1; transform: scale(1);}
    }

    .close-btn {
      float: right;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
      color: #333;
    }

    .modal-content h2 {
      margin-bottom: 15px;
      color: #1e3d8f;
      font-size: 20px;
    }

    .modal-content form input,
    .modal-content form select,
    .modal-content form textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #999;
      border-radius: 4px;
      font-size: 14px;
    }

    .modal-content form button {
      width: 100%;
      background: #1e3d8f;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    .modal-content form button:hover {
      background: #274ea1;
    }
  </style>
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
        <a href="#">Resident Information</a>
        <a href="#">Barangay Certificate</a>
        <a href="#">Barangay Of Indigency</a>
        <a href="#">Business Permit</a>
        <a href="#">Complaint Records</a>
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
          <div class="card blue" onclick="openModal('residentModal')">
            <p>Add Resident</p>
          </div>
          <div class="card yellow" onclick="openModal('indigencyModal')">
            <p>Indigency</p>
          </div>
          <div class="card green" onclick="openModal('certificateModal')">
            <p>Certificate</p>
          </div>
          <div class="card red" onclick="openModal('permitModal')">
            <p>Permit</p>
          </div>
          <div class="card orange" onclick="openModal('complaintModal')">
            <p>Complaint</p>
          </div>
      </section>
    </main>
  </div>

  <!-- ✅ Add Resident Modal -->
<div id="residentModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('residentModal')">&times;</span>
    <h2>Add Resident Information</h2>
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

<script>
  function openModal(id) {
    document.getElementById(id).style.display = 'flex';
  }

  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  // ✅ NEW FUNCTION: Show success/error message modal
  function showMessage(status, text) {
  const modal = document.getElementById('messageModal');
  const title = document.getElementById('messageTitle');
  const messageText = document.getElementById('messageText');

  // Reset old classes
  modal.classList.remove('message-success', 'message-error');

  // Apply styling based on status
  if (status.toLowerCase() === 'success') {
    modal.classList.add('message-success');
    title.innerText = "✅ Success";
  } else {
    modal.classList.add('message-error');
    title.innerText = "❌ Error";
  }

  messageText.innerText = text;
  openModal('messageModal');
}

  // ✅ Close message modal only (keeps the form modal open)
  function closeMessageModal() {
    closeModal('messageModal');
  }

  // Close modal when clicking outside (for all except the inner message)
  window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
      if (event.target === modal && modal.id !== 'messageModal') {
        modal.style.display = 'none';
      }
    });
  };
</script>

</body>
</html>
