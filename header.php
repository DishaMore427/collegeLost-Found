<?php
require_once "config.php";
?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>CampusFind</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<nav>

    <a href="index.php">
        CampusFind
    </a>

    <a href="index.php">
        Home
    </a>

    <a href="items.php">
        Browse
    </a>

    <?php if (logged()): ?>

        <a href="report.php">
            Report Item
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    <?php else: ?>

        <a href="login.php">
            Student Login
        </a>
            <a href="admin_login.php">Admin Login</a>

        <a href="register.php">
            Register
        </a>
      

    <?php endif; ?>

</nav>
