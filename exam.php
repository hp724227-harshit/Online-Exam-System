<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Handle exam submission (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_exam'])) {
    $sql = "SELECT id, correct_option FROM questions";
    $result = mysqli_query($link, $sql);
    $score = 0;
    $total = mysqli_num_rows($result);

    while ($row = mysqli_fetch_array($result)) {
        $qid = $row['id'];
        if (isset($_POST['q_' . $qid]) && $_POST['q_' . $qid] == $row['correct_option']) {
            $score++;
        }
    }

    // Save result to DB (Lab 4 - prepared statement)
    $ins = "INSERT INTO results (user_id, score, total) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $ins)) {
        mysqli_stmt_bind_param($stmt, "iii", $_SESSION['user_id'], $score, $total);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Store score in session (Lab 7)
    $_SESSION['last_score'] = $score;
    $_SESSION['last_total'] = $total;

    header("Location: result.php");
    exit();
}

// GET - Show exam
$sql = "SELECT * FROM questions";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html>
<head><title>Exam</title>
<style>
  body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 2rem; }
  .card { background: white; padding: 2rem; border-radius: 10px; max-width: 700px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  .question { margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; }
  .question p { font-weight: bold; margin-bottom: 0.5rem; }
  label { display: block; margin: 4px 0; cursor: pointer; }
  input[type=submit] { padding: 12px 30px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
</style>
</head>
<body>
<div class="card">
  <h2>Exam</h2>
  <p>Answer all questions, then click Submit.</p>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <?php $qnum = 1; while ($row = mysqli_fetch_array($result)): ?>
    <div class="question">
      <p>Q<?php echo $qnum++; ?>. <?php echo htmlspecialchars($row['question_text']); ?></p>
      <label><input type="radio" name="q_<?php echo $row['id']; ?>" value="a" required> <?php echo htmlspecialchars($row['option_a']); ?></label>
      <label><input type="radio" name="q_<?php echo $row['id']; ?>" value="b"> <?php echo htmlspecialchars($row['option_b']); ?></label>
      <label><input type="radio" name="q_<?php echo $row['id']; ?>" value="c"> <?php echo htmlspecialchars($row['option_c']); ?></label>
      <label><input type="radio" name="q_<?php echo $row['id']; ?>" value="d"> <?php echo htmlspecialchars($row['option_d']); ?></label>
    </div>
    <?php endwhile; ?>
    <input type="hidden" name="submit_exam" value="1" />
    <input type="submit" value="Submit Exam" />
  </form>
</div>
</body>
</html>