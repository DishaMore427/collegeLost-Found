<?php

require_once 'config.php';
require_admin();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


/* =========================
   VERIFY CLAIM
========================= */

if ($action === 'verify' && $id > 0) {

    $stmt = $conn->prepare("SELECT item_id FROM claims WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $claim = $result->fetch_assoc();

    if ($claim) {

        $item_id = (int)$claim['item_id'];

        // Verify claim
        $stmt = $conn->prepare("
            UPDATE claims 
            SET status = 'Verified'
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Mark item as claimed
        $stmt = $conn->prepare("
            UPDATE items 
            SET status = 'Claimed'
            WHERE id = ?
        ");

        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    }

    header("Location: admin.php");
    exit;
}


/* =========================
   REJECT CLAIM
========================= */

if ($action === 'reject' && $id > 0) {

    $stmt = $conn->prepare("
        UPDATE claims
        SET status = 'Rejected'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}


/* =========================
   MARK RETURNED
========================= */

if ($action === 'returned' && $id > 0) {

    $stmt = $conn->prepare("
        UPDATE items
        SET status = 'Returned'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}


/* =========================
   DELETE ITEM
========================= */

if ($action === 'delete' && $id > 0) {

    /* Get image name first */

    $stmt = $conn->prepare("
        SELECT image
        FROM items
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();


    if ($item) {

        /* Delete image */

        if (!empty($item['image'])) {

            $imagePath = __DIR__ . "/uploads/" . $item['image'];

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }


        /* Delete claims first */

        $stmt = $conn->prepare("
            DELETE FROM claims
            WHERE item_id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();


        /* Delete item */

        $stmt = $conn->prepare("
            DELETE FROM items
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();
    }


    header("Location: admin.php");
    exit;
}


/* =========================
   DEFAULT
========================= */

header("Location: admin.php");
exit;

?>