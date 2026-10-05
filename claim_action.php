<?php
require_once "config.php";
require_login();

$claimId = intval($_GET["id"] ?? 0);
$action = $_GET["action"] ?? "";

if (!in_array($action, ["accept", "reject"])) {
    header("Location: dashboard.php");
    exit;
}

$newStatus = ($action == "accept") ? "Accepted" : "Rejected";

$stmt = $conn->prepare("
    SELECT c.item_id
    FROM claims c
    JOIN items i ON c.item_id=i.id
    WHERE c.id=? AND i.user_id=?
");

$stmt->bind_param(
    "ii",
    $claimId,
    $_SESSION["user_id"]
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Unauthorized request.");
}

$claim = $result->fetch_assoc();

$update = $conn->prepare("
    UPDATE claims
    SET status=?
    WHERE id=?
");

$update->bind_param(
    "si",
    $newStatus,
    $claimId
);

$update->execute();

if ($action == "accept") {

    $itemUpdate = $conn->prepare("
        UPDATE items
        SET status='Claimed'
        WHERE id=?
    ");

    $itemUpdate->bind_param(
        "i",
        $claim["item_id"]
    );

    $itemUpdate->execute();
}

header("Location: dashboard.php");
exit;

?>