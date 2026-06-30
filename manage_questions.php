<?php

// CRUD Operations

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
require_once "config.php";

$msg = "";

// CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $sql = "INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?,?,?,?,?,?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssss",
            $_POST['question_text'], $_POST['option_a'],
            $_POST['option_b'], $_POST['option_c'],
            $_POST['option_d'], $_POST['correct_option']);
        mysqli_stmt_execute($stmt);
        $msg = "Question added!";
    }
}

// UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $sql = "UPDATE questions SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssssi",
            $_POST['question_text'], $_POST['option_a'],
            $_POST['option_b'], $_POST['option_c'],
            $_POST['option_d'], $_POST['correct_option'], $_POST['id']);
        mysqli_stmt_execute($stmt);
        $msg = "Question updated!";
    }
}

// DELETE (Lab 5 - via GET with id)
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $sql = "DELETE FROM questions WHERE id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_GET['delete']);
        mysqli_stmt_execute($stmt);
        $msg = "Question deleted!";
    }
}

// Fetch for edit (Lab 5 - GET with id)
$edit_row = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $sql = "SELECT * FROM questions WHERE id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_GET['edit']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $edit_row = mysqli_fetch_array($result);
    }
}

// READ all
$all = mysqli_query($link, "SELECT * FROM questions");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Questions</title>
<style>
  body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 2rem; }
  .card { background: white; padding: 1.5rem; border-radius: 10px; max-width: 800px; margin: auto 0 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  input[type=text], select { width: 100%; padding: 8px; margin: 5px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 5px; }
  input[type=submit] { padding: 8px 20px; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 6px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 8px; border: 1px solid #ddd; font-size: 14px; }
  th { background: #4a90e2; color: white; }
  a.edit { color: #27ae60; text-decoration: none; margin-right: 8px; }
  a.del { color: #e74c3c; text-decoration: none; }
  .msg { background: #eaffea; padding: 8px; border-radius: 5px; margin-bottom: 1rem; }
  a.back { display: inline-block; padding: 8px 16px; background: #888; color: white; text-decoration: none; border-radius: 5px; }
</style>
</head>
<body>
<div class="card">
  <?php if ($msg): ?><div class="msg"><?php echo $msg; ?></div><?php endif; ?>
  <h2><?php echo $edit_row ? 'Edit Question' : 'Add New Question'; ?></h2>
  <form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="post">
    <?php if ($edit_row): ?>
      <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>" />
    <?php endif; ?>
    <input type="text" name="question_text" placeholder="Question" value="<?php echo $edit_row ? htmlspecialchars($edit_row['question_text']) : ''; ?>" required />
    <input type="text" name="option_a" placeholder="Option A" value="<?php echo $edit_row ? htmlspecialchars($edit_row['option_a']) : ''; ?>" required />
    <input type="text" name="option_b" placeholder="Option B" value="<?php echo $edit_row ? htmlspecialchars($edit_row['option_b']) : ''; ?>" required />
    <input type="text" name="option_c" placeholder="Option C" value="<?php echo $edit_row ? htmlspecialchars($edit_row['option_c']) : ''; ?>" required />
    <input type="text" name="option_d" placeholder="Option D" value="<?php echo $edit_row ? htmlspecialchars($edit_row['option_d']) : ''; ?>" required />
    <select name="correct_option" required>
      <option value="">-- Correct Option --</option>
      <?php foreach(['a','b','c','d'] as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($edit_row && $edit_row['correct_option']==$opt) ? 'selected' : ''; ?>>
          Option <?php echo strtoupper($opt); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($edit_row): ?>
      <input type="submit" name="update" value="Update Question" />
      <a href="manage_questions.php" style="color:#888;">Cancel</a>
    <?php else: ?>
      <input type="submit" name="create" value="Add Question" />
    <?php endif; ?>
  </form>
</div>

<div class="card">
  <h2>All Questions</h2>
  <table>
    <thead><tr><th>#</th><th>Question</th><th>Correct</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while ($row = mysqli_fetch_array($all)): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars(substr($row['question_text'], 0, 60)) . '...'; ?></td>
        <td><?php echo strtoupper($row['correct_option']); ?></td>
        <td>
          <a class="edit" href="manage_questions.php?edit=<?php echo $row['id']; ?>">Edit</a>
          <a class="del" href="manage_questions.php?delete=<?php echo $row['id']; ?>"
             onclick="return confirm('Delete this question?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <br><a class="back" href="home.php">Back to Home</a>
</div>
</body>
</html>