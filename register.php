<?php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $roll_no = trim($_POST["roll_no"]);
    $email = trim($_POST["email"]);
    $course = trim($_POST["course"]);
    $semester = trim($_POST["semester"]);
    $password = $_POST["password"];

    if ($name == "" || $roll_no == "" || $email == "" || $password == "") {
        $message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE roll_no = ? OR email = ?"
        );
        $check->bind_param("ss", $roll_no, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "Roll number or email already registered.";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users
                (name, roll_no, email, course, semester, password)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssssss",
                $name,
                $roll_no,
                $email,
                $course,
                $semester,
                $hashed_password
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form-container">

    <h2>Student Registration</h2>

    <?php if ($message): ?>
        <p class="error"><?php echo e($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="name" placeholder="Student Name" required>

        <input type="text" name="roll_no" placeholder="Roll Number" required>

        <input type="email" name="email" placeholder="College Email" required>

        <input type="text" name="course" placeholder="Course">

        <input type="text" name="semester" placeholder="Semester">

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Register</button>

    </form>

    <p>
        Already registered?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>