<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elected Officials - Barangay E-Services</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/index.css">
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
          <a href="#" class="nav-link active">People <i class="arrow-down">▼</i></a>
          <ul class="dropdown-menu">
            <li><a href="barangay-council.php">Elected Officials</a></li>
          </ul>
        </li>
        <li><a href="contact.php" class="nav-link">Contact</a></li>
        <?php if ($is_logged_in): ?>
        <li class="nav-dropdown user-dropdown">
          <a href="#" class="nav-link user-link">
            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
            <i class="arrow-down">▼</i>
          </a>
          <ul class="dropdown-menu user-menu">
            <li><a href="logout.php" class="logout-link">Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li><a href="login.php" class="nav-link">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Elected Officials Section -->
  <section class="elected-officials" style="padding-top: 40px;">
    <div class="container">
      <h2>Elected Officials</h2>
      
      <!-- Barangay Captain -->
      <div class="captain-section">
        <div class="captain-info">
          <h3>Barangay Captain</h3>
          <h4>Mark Joseph Canedo</h4>
          <p>Leading our barangay with dedication and commitment to serve the community.</p>
        </div>
        <div class="captain-image">
          <img src="images/captain.jpg" alt="Mark Joseph Canedo - Barangay Captain">
        </div>
      </div>

      <!-- Kagawad Section -->
      <div class="kagawad-section">
        <h3>Barangay Kagawad</h3>
        <div class="kagawad-grid">
          <div class="kagawad-card">
            <div class="kagawad-icon">👤</div>
            <h4>Vienz Cliff Libradilla</h4>
            <p>Kagawad</p>
          </div>
          <div class="kagawad-card">
            <div class="kagawad-icon">👤</div>
            <h4>Claire Bohol</h4>
            <p>Kagawad</p>
          </div>
          <div class="kagawad-card">
            <div class="kagawad-icon">👤</div>
            <h4>Ivan Clark Yonzon</h4>
            <p>Kagawad</p>
          </div>
          <div class="kagawad-card">
            <div class="kagawad-icon">👤</div>
            <h4>John Rey Umpad</h4>
            <p>Kagawad</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Contact Us</h3>
          <p>Barangay Hall, Main Street</p>
          <p>Email: info@barangay-eservices.gov.ph</p>
          <p>Phone: (02) 1234-5678</p>
        </div>
        
        <div class="footer-section">
          <h3>Emergency Hotline</h3>
          <p><strong>911</strong> - Emergency Response</p>
          <p><strong>117</strong> - Police Hotline</p>
          <p><strong>166</strong> - Fire Department</p>
        </div>
        
        <div class="footer-section">
          <h3>Services</h3>
          <ul class="footer-links">
            <li><a href="business-permit.php">Business Permit</a></li>
            <li><a href="barangay-clearance.php">Barangay Clearance</a></li>
            <li><a href="barangay-indigency.php">Barangay Indigency</a></li>
            <li><a href="barangay-residency.php">Barangay Residency</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3>Quick Links</h3>
          <ul class="footer-links">
            <li><a href="login.php">Login</a></li>
          </ul>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2025 Barangay E-Services by Team Earth. All rights reserved.</p>
        <div class="social-media">
          <a href="#" class="social-link" aria-label="Facebook">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>
          <a href="#" class="social-link" aria-label="Instagram">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>

