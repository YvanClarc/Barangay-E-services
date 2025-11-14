<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Indigency - Barangay E-Services</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/index.css">
  <link rel="stylesheet" href="styles/service-page.css">
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">
        <img src="images/ivan.png" alt="Barangay Logo">
        <span>Barangay E-Services</span>
      </div>
      
      <ul class="nav-menu">
        <li><a href="index.php" class="nav-link">Home</a></li>
        <li class="nav-dropdown">
          <a href="#" class="nav-link active">Services <i class="arrow-down">▼</i></a>
          <ul class="dropdown-menu">
            <li><a href="business-permit.php">Business Permit</a></li>
            <li><a href="barangay-clearance.php">Barangay Clearance</a></li>
            <li><a href="barangay-indigency.php">Barangay Indigency</a></li>
            <li><a href="barangay-residency.php">Barangay Residency</a></li>
          </ul>
        </li>
        <li><a href="about.php" class="nav-link">About</a></li>
        <li class="nav-dropdown">
          <a href="#" class="nav-link">People <i class="arrow-down">▼</i></a>
          <ul class="dropdown-menu">
            <li><a href="barangay-council.php">Barangay Council</a></li>
          </ul>
        </li>
        <li><a href="contact.php" class="nav-link">Contact</a></li>
      </ul>
    </div>
  </nav>

  <!-- Page Content -->
  <section class="service-page">
    <div class="container">
      <div class="service-header">
        <div class="service-icon-large">💳</div>
        <h1>Barangay Indigency</h1>
        <p class="service-description">Request an indigency certificate for financial assistance and other purposes.</p>
      </div>

      <div class="service-content">
        <div class="content-card">
          <h2>Requirements</h2>
          <ul class="requirements-list">
            <li>Valid ID (Government-issued)</li>
            <li>Proof of Income (if applicable)</li>
            <li>Proof of Residency</li>
            <li>Recent 2x2 ID Picture</li>
            <li>Application Form (available online)</li>
          </ul>
        </div>

        <div class="content-card">
          <h2>How to Apply</h2>
          <ol class="steps-list">
            <li>Log in to your account or create one if you don't have it yet</li>
            <li>Navigate to the Request Certificate section</li>
            <li>Select "Barangay Indigency" from the available services</li>
            <li>Fill out the required information and upload necessary documents</li>
            <li>Submit your application and wait for approval</li>
          </ol>
        </div>

        <div class="content-card">
          <h2>Processing Time</h2>
          <p>Barangay indigency applications are typically processed within 2-3 business days after submission of complete requirements.</p>
        </div>

        <div class="action-buttons">
          <a href="login.php" class="btn btn-primary">Apply Now</a>
          <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>&copy; 2024 Barangay E-Services. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>

