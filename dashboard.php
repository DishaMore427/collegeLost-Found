<?php
require_once "config.php";
require_login();

$user_id = $_SESSION["user_id"];

/* MY REPORTS */
$stmt = $conn->prepare("
    SELECT *
    FROM items
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$my_items = $stmt->get_result();

/* MY CLAIMS */
$stmt = $conn->prepare("
    SELECT
        claims.*,
        items.title,
        items.type,
        items.status AS item_status
    FROM claims
    JOIN items ON claims.item_id = items.id
    WHERE claims.claimant_id = ?
    ORDER BY claims.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$my_claims = $stmt->get_result();

/* COUNTS */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM items WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$my_reports = $stmt->get_result()->fetch_assoc()["total"];

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM claims WHERE claimant_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$my_claim_count = $stmt->get_result()->fetch_assoc()["total"];

$campus_reports = $conn
    ->query("SELECT COUNT(*) AS total FROM items")
    ->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="dashboard">

    <h1>
        Welcome, <?php echo e($_SESSION["name"]); ?>
    </h1>

    <p>
        Roll No: <?php echo e($_SESSION["roll_no"]); ?>
    </p>

    <?php if (isset($_GET["reported"])): ?>
        <p class="success">
            Item reported successfully.
        </p>
    <?php endif; ?>

    <div class="stats">

        <div>
            <h2><?php echo $my_reports; ?></h2>
            <p>My Reports</p>
        </div>

        <div>
            <h2><?php echo $my_claim_count; ?></h2>
            <p>My Claims</p>
        </div>

        <div>
            <h2><?php echo $campus_reports; ?></h2>
            <p>Campus Reports</p>
        </div>

    </div>

    <h2>My Reported Items</h2>

    <table border="1">

        <tr>
            <th>Item</th>
            <th>Type</th>
            <th>Location</th>
            <th>Status</th>
        </tr>

        <?php while ($row = $my_items->fetch_assoc()): ?>

            <tr>

                <td><?php echo e($row["title"]); ?></td>

                <td><?php echo e($row["type"]); ?></td>

                <td><?php echo e($row["location"]); ?></td>

                <td><?php echo e($row["status"]); ?></td>

            </tr>

        <?php endwhile; ?>

    </table>

    <h2>My Claim Requests</h2>

    <table border="1">

        <tr>
            <th>Item</th>
            <th>Claim Status</th>
            <th>Item Status</th>
        </tr>

        <?php while ($row = $my_claims->fetch_assoc()): ?>

            <tr>

                <td><?php echo e($row["title"]); ?></td>

                <td><?php echo e($row["status"]); ?></td>

                <td><?php echo e($row["item_status"]); ?></td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>
</html>