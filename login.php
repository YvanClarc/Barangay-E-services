<?php
    session_start();

    $errors = [
        'login' => $_SESSION['login_error'] ?? '',
        'register' => $_SESSION['register_error'] ?? ''
    ];
    $activeForm = $_SESSION['active_form'] ?? 'login';
    session_unset();

    function displayError($errors) {
        return !empty($errors) ? "<p class='error'>$errors</p>" : '';
    }

    function isActive($form, $activeForm) {
        return $form === $activeForm ? 'active' : '';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay E-Services</title>
  <link rel="stylesheet" href="styles/Login.css">
</head>
<body>
  <div class="main-container">
    
    <!-- Left side (Logo + Title) -->
    <div class="left-section">
      <div class="logo">
        <img src="images/ivan.png" alt="Barangay Logo">
        <h1>BARANGAY E-SERVICES AND<br>COMPLAINT MANAGEMENT SYSTEMS</h1>
      </div>
    </div>

    <!-- Right side (Forms) -->
    <div class="right-section">
      <div class="form-box <?= isActive('login', $activeForm); ?>" id="login-form">
        <form action="login_register.php" method="post">
          <label>Email:</label>
          <input type="email" name="email" placeholder="Enter your email" required>

          <label>Password:</label>
          <input type="password" name="password" placeholder="Enter your password" required>

          <?= displayError($errors['login']) ?>

          <p class="forgot"><a href="#">Forgot password?</a></p>
          <button type="submit" name="login">Login</button>
          <p class="switch">Doesn't have an account? <a href="#" onclick="showForm('register-form')">Create an account</a></p>
        </form>
      </div>

      <div class="form-box <?= isActive('register', $activeForm); ?>" id="register-form">
        <form action="login_register.php" method="post">
          <h2>Create Your Account</h2>

          <?= displayError($errors['register']) ?>

          <input type="text" name="first_name" placeholder="Enter your first name" required>
          <input type="text" name="last_name" placeholder="Enter your last name" required>
          <input type="date" name="birth_date" required>
          
          <select name="gender" required>
            <option value="" selected disabled hidden>Select your gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          
          <input type="email" name="email" placeholder="Enter your email" required>
          <input type="password" name="password" placeholder="Create a password" required>

          <label for="role">Select Role</label>
            <select name="role" id="role" required>
              <option value="user">User</option>
              <option value="Staff">Staff</option>
              <option value="official">Official</option>
            </select>

          <button type="submit" name="register">Register</button>
          <p class="switch">Already have an account? 
            <a href="#" onclick="showForm('login-form')">Sign in</a>
          </p>
        </form>
      </div>
    </div>
  </div>
  <script src="scripts/script.js"></script>
</body>
</html>
