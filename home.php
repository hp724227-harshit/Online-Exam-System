<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Home</title>
<style>
  body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 2rem; }
  .card { background: white; padding: 2rem; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  a.btn { display: inline-block; padding: 10px 20px; margin: 8px 4px; background: #4a90e2; color: white; text-decoration: none; border-radius: 5px; }
  a.btn.green { background: #27ae60; }
  a.btn.red { background: #e74c3c; }
</style>
</head>
<body>
<div class="card">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>Role: <strong><?php echo $_SESSION['role']; ?></strong></p>

  <?php if ($_SESSION['role'] == 'student'): ?>
    <a class="btn green" href="exam.php">Start Exam</a>
    <a class="btn" href="result.php">My Results</a>
  <?php else: ?>
    <a class="btn" href="manage_questions.php">Manage Questions</a>
    <a class="btn" href="result.php">View All Results</a>
  <?php endif; ?>

  <a class="btn red" href="logout.php">Logout</a>
</div>
</body>
</html>