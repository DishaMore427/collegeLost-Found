<?php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $roll_no = trim($_POST["roll_no"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE roll_no = ? AND role = 'student'"
    );

    $stmt->bind_param("s", $roll_no);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["roll_no"] = $user["roll_no"];

        header("Location: index.php");
        exit;

    } else {
        $message = "Invalid roll number or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form-container">

    <h2>Student Login</h2>

    <?php if (isset($_GET["registered"])): ?>
        <p class="success">Registration successful. Please login.</p>
    <?php endif; ?>

    <?php if ($message): ?>
        <p class="error"><?php echo e($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="roll_no" placeholder="Roll Number" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>

    </form>

    <p>
        New student?
        <a href="register.php">Create Account</a>
    </p>

</div>

</body>
</html>