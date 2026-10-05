<?php
require_once "config.php";

/* CREATE DEFAULT ADMIN */

$admin_email = "admin@college.com";
$admin_password = "admin123";

$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$check->bind_param("s", $admin_email);
$check->execute();

if ($check->get_result()->num_rows == 0) {

    $hashed = password_hash(
        $admin_password,
        PASSWORD_DEFAULT
    );

    $stmt = $conn->prepare("
        INSERT INTO users
        (name, roll_no, email, course, semester, password, role)
        VALUES (?, ?, ?, ?, ?, ?, 'admin')
    ");

    $name = "College Admin";
    $roll = "ADMIN001";
    $course = "Administration";
    $semester = "N/A";

    $stmt->bind_param(
        "ssssss",
        $name,
        $roll,
        $admin_email,
        $course,
        $semester,
        $hashed
    );

    $stmt->execute();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE email = ? AND role = 'admin'
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin["password"])) {

        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["name"];

        header("Location: admin.php");
        exit;

    } else {

        $message = "Invalid admin email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form-container">

    <h2>College Admin Login</h2>

    <?php if ($message): ?>

        <p class="error">
            <?php echo e($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Admin Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>
</html>