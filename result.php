<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

$is_admin = $_SESSION['role'] == 'admin';

if ($is_admin) {
    $sql = "SELECT r.id, u.username, r.score, r.total, r.taken_at
            FROM results r JOIN users u ON r.user_id = u.id
            ORDER BY r.taken_at DESC";
} else {
    $sql = "SELECT r.id, r.score, r.total, r.taken_at
            FROM results r WHERE r.user_id = ?";
}
?>
<!DOCTYPE html>
<html>
<head><title>Results</title>
<style>
  body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 2rem; }
  .card { background: white; padding: 2rem; border-radius: 10px; max-width: 700px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
  th { background: #4a90e2; color: white; }
  .score-high { color: green; font-weight: bold; }
  .score-low { color: red; font-weight: bold; }
  a.btn { display: inline-block; padding: 8px 16px; background: #4a90e2; color: white; text-decoration: none; border-radius: 5px; margin-top: 1rem; }
</style>
</head>
<body>
<div class="card">
  <h2><?php echo $is_admin ? 'All Results' : 'My Results'; ?></h2>

  <?php if (isset($_SESSION['last_score'])): ?>
    <div style="background:#eaffea;padding:1rem;border-radius:8px;margin-bottom:1rem;">
      <strong>Last exam score: <?php echo $_SESSION['last_score']; ?>/<?php echo $_SESSION['last_total']; ?></strong>
    </div>
    <?php unset($_SESSION['last_score'], $_SESSION['last_total']); ?>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <?php if ($is_admin): ?><th>Student</th><?php endif; ?>
        <th>Score</th><th>Total</th><th>Percentage</th><th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php
    if ($is_admin) {
        $result = mysqli_query($link, $sql);
    } else {
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
    while ($row = mysqli_fetch_array($result)):
        $pct = round(($row['score'] / $row['total']) * 100);
        $cls = $pct >= 60 ? 'score-high' : 'score-low';
    ?>
      <tr>
        <?php if ($is_admin): ?><td><?php echo htmlspecialchars($row['username']); ?></td><?php endif; ?>
        <td><?php echo $row['score']; ?></td>
        <td><?php echo $row['total']; ?></td>
        <td class="<?php echo $cls; ?>"><?php echo $pct; ?>%</td>
        <td><?php echo $row['taken_at']; ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <a class="btn" href="home.php">Back to Home</a>
</div>
</body>
</html>