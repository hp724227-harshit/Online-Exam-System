<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

require_once "config.php";

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username   = trim($_POST["username"]);
    $password   = trim($_POST["password"]);
    $confirm    = trim($_POST["confirm_password"]);
    $role       = $_POST["role"]; // 'student' or 'admin'

    //Validation
    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";

    } elseif (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $username)) {
        $error = "Username must be 4–30 characters (letters, numbers, underscore only).";

    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";

    } elseif (!in_array($role, ['student', 'admin'])) {
        $error = "Invalid role selected.";

    } else {
        // Check if username already exists 
        $check_sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $check_sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Username already taken. Please choose another.";
            }
            mysqli_stmt_close($stmt);
        }

        // Insert new user 
        if (empty($error)) {
            $hashed = MD5($password); // MD5 used to match your login.php
            $ins_sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $ins_sql)) {
                mysqli_stmt_bind_param($stmt, "sss", $username, $hashed, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Online Exam System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 380px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .subtitle {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 4px;
            margin-top: 12px;
        }

        input[type=text],
        input[type=password],
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type=text]:focus,
        input[type=password]:focus,
        select:focus {
            border-color: #4a90e2;
            outline: none;
        }

        /* Role selector as two visible buttons */
        .role-group {
            display: table;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 4px;
        }

        .role-option {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .role-option input[type=radio] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 9px 0;
            margin: 0;
            cursor: pointer;
            background: #f9f9f9;
            font-size: 14px;
            color: #555;
            border: none;
        }

        .role-option input[type=radio]:checked + label {
            background: #4a90e2;
            color: white;
            font-weight: bold;
        }

        .role-option:first-child label {
            border-right: 1px solid #ccc;
        }

        input[type=submit] {
            width: 100%;
            padding: 11px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 1.25rem;
        }

        input[type=submit]:hover {
            background: #357abd;
        }

        .error {
            background: #fff0f0;
            border: 1px solid #f5c6c6;
            color: #c0392b;
            padding: 10px 12px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 1rem;
        }

        .success {
            background: #f0fff4;
            border: 1px solid #b2dfdb;
            color: #2e7d32;
            padding: 10px 12px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 1rem;
        }

        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 1.25rem 0;
        }

        .footer-text {
            text-align: center;
            font-size: 13px;
            color: #888;
        }

        .footer-text a {
            color: #4a90e2;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Online Exam System</h2>
    <p class="subtitle">Create a new account</p>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
            &nbsp;<a href="login.php">Login now &rarr;</a>
        </div>
    <?php endif; ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="4–30 characters"
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
               required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Min. 6 characters" required />

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Repeat password" required />

        <label>Register As</label>
        <div class="role-group">
            <div class="role-option">
                <input type="radio" id="role_student" name="role" value="student"
                    <?php echo (!isset($_POST['role']) || $_POST['role'] == 'student') ? 'checked' : ''; ?> />
                <label for="role_student">Student</label>
            </div>
            <div class="role-option">
                <input type="radio" id="role_admin" name="role" value="admin"
                    <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'checked' : ''; ?> />
                <label for="role_admin">Admin</label>
            </div>
        </div>

        <input type="submit" value="Register" />
    </form>

    <hr>
    <p class="footer-text">Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>