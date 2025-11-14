<?php
session_start();
<<<<<<< HEAD
session_unset();
session_destroy();
header("Location: index.php");
exit();
?>

=======
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: login.php');
exit();
?>
>>>>>>> 0cc6017d840f8380fb8f0e05793df521f58678d0
