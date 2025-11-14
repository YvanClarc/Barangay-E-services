<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - Barangay E-Services</title>
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
          <a href="#" class="nav-link">Services <i class="arrow-down">▼</i></a>
          <ul class="dropdown-menu">
            <li><a href="business-permit.php">Business Permit</a></li>
            <li><a href="barangay-clearance.php">Barangay Clearance</a></li>
            <li><a href="barangay-indigency.php">Barangay Indigency</a></li>
            <li><a href="barangay-residency.php">Barangay Residency</a></li>
          </ul>
        </li>
        <li><a href="about.php" class="nav-link active">About</a></li>
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
        <h1>About Us</h1>
        <p class="service-description">Learn more about the Barangay E-Services and Complaint Management System.</p>
      </div>

      <div class="service-content">
        <div class="content-card">
          <h2>Our Mission</h2>
          <p>To provide efficient, accessible, and transparent barangay services to all residents through a modern digital platform that simplifies the process of obtaining certificates, filing complaints, and accessing barangay information.</p>
        </div>

        <div class="content-card">
          <h2>Our Vision</h2>
          <p>To be a leading barangay in digital governance, ensuring that all residents can easily access government services and participate in community management through innovative technology solutions.</p>
        </div>

        <div class="content-card">
          <h2>What We Offer</h2>
          <ul class="requirements-list">
            <li>Online application for various barangay certificates</li>
            <li>Complaint management system</li>
            <li>Announcement and information dissemination</li>
            <li>Easy access to barangay services</li>
            <li>Transparent and efficient processing</li>
          </ul>
        </div>

        <div class="action-buttons">
          <a href="index.php" class="btn btn-primary">Back to Home</a>
          <a href="contact.php" class="btn btn-secondary">Contact Us</a>
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

