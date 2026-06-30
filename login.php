<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = MD5(trim($_POST["password"]));

    // Prepared statement to prevent SQL injection (Lab 4)
    $sql = "SELECT id, username, role FROM users WHERE username=? AND password=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $uname, $role);
                mysqli_stmt_fetch($stmt);

                // Set session (Lab 7)
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $uname;
                $_SESSION['role'] = $role;

                // Set a cookie to remember username for 7 days (Lab 7)
                setcookie('last_user', $uname, time() + (7 * 24 * 60 * 60));

                header("Location: home.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Exam - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #333; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 5px; }
        input[type=submit] { width: 100%; padding: 10px; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        input[type=submit]:hover { background: #357abd; }
        .error { color: red; text-align: center; margin-top: 10px; }
        .cookie-hint { text-align: center; color: #888; font-size: 13px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Online Exam System</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="text" name="username" placeholder="Username"
               value="<?php echo isset($_COOKIE['last_user']) ? htmlspecialchars($_COOKIE['last_user']) : ''; ?>" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="submit" value="Login" />
    </form>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (isset($_COOKIE['last_user'])): ?>
        <p class="cookie-hint">Welcome back, <?php echo htmlspecialchars($_COOKIE['last_user']); ?>!</p>
    <?php endif; ?>
</div>
</body>
</html>