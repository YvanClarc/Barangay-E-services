<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - Barangay E-Services</title>
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
        <li><a href="about.php" class="nav-link">About</a></li>
        <li class="nav-dropdown">
          <a href="#" class="nav-link">People <i class="arrow-down">▼</i></a>
          <ul class="dropdown-menu">
            <li><a href="barangay-council.php">Barangay Council</a></li>
          </ul>
        </li>
        <li><a href="contact.php" class="nav-link active">Contact</a></li>
      </ul>
    </div>
  </nav>

  <!-- Page Content -->
  <section class="service-page">
    <div class="container">
      <div class="service-header">
        <h1>Contact Us</h1>
        <p class="service-description">Get in touch with the barangay office for inquiries, concerns, or assistance.</p>
      </div>

      <div class="service-content">
        <div class="content-card">
          <h2>Barangay Office</h2>
          <p><strong>Address:</strong> [Barangay Address]</p>
          <p><strong>Phone:</strong> [Phone Number]</p>
          <p><strong>Email:</strong> [Email Address]</p>
        </div>

        <div class="content-card">
          <h2>Office Hours</h2>
          <p><strong>Monday - Friday:</strong> 8:00 AM - 5:00 PM</p>
          <p><strong>Saturday:</strong> 8:00 AM - 12:00 PM</p>
          <p><strong>Sunday:</strong> Closed</p>
        </div>

        <div class="content-card">
          <h2>Send Us a Message</h2>
          <p>For online inquiries, please log in to your account and use the complaint management system or contact form available in your dashboard.</p>
        </div>

        <div class="action-buttons">
          <a href="login.php" class="btn btn-primary">Login to Contact</a>
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

