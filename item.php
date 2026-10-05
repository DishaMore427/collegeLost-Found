<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: items.php");
    exit;
}


/* =========================
   GET ITEM
========================= */

$stmt = $conn->prepare("
    SELECT 
        items.*,
        users.name AS student_name,
        users.roll_no AS student_roll
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE items.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    header("Location: items.php");
    exit;
}


/* =========================
   CLAIM ITEM
========================= */

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim'])) {

    require_login();

    // Don't allow claiming your own item
    if ($item['user_id'] == $_SESSION['user_id']) {

        $error = "You cannot claim your own reported item.";

    } elseif ($item['status'] !== 'Open') {

        $error = "This item is no longer available for claiming.";

    } else {

        $color = trim($_POST['color'] ?? '');
        $identifying_mark = trim($_POST['identifying_mark'] ?? '');
        $ownership_details = trim($_POST['ownership_details'] ?? '');

        if (
            empty($color) ||
            empty($identifying_mark) ||
            empty($ownership_details)
        ) {

            $error = "Please fill all claim details.";

        } else {

            // Check duplicate claim
            $check = $conn->prepare("
                SELECT id
                FROM claims
                WHERE item_id = ?
                AND claimant_id = ?
            ");

            $check->bind_param(
                "ii",
                $id,
                $_SESSION['user_id']
            );

            $check->execute();

            $existing = $check->get_result();

            if ($existing->num_rows > 0) {

                $error = "You have already submitted a claim for this item.";

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO claims
                    (
                        item_id,
                        claimant_id,
                        color,
                        identifying_mark,
                        ownership_details,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, 'Pending')
                ");

                $stmt->bind_param(
                    "iisss",
                    $id,
                    $_SESSION['user_id'],
                    $color,
                    $identifying_mark,
                    $ownership_details
                );

                if ($stmt->execute()) {

                    $message = "Your claim has been submitted successfully. Admin will verify it.";

                } else {

                    $error = "Unable to submit claim. Please try again.";

                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($item['title']) ?> - CampusFind</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


<?php include 'header.php'; ?>


<div class="item-container">

    <h1>
        <?= e($item['title']) ?>
    </h1>


    <!-- ITEM IMAGE -->

    <?php if (!empty($item['image'])): ?>

        <img
            src="uploads/<?= e($item['image']) ?>"
            alt="<?= e($item['title']) ?>"
        >

    <?php endif; ?>


    <!-- ITEM INFORMATION -->

    <p>
        <b>Type:</b>
        <?= e($item['type']) ?>
    </p>

    <p>
        <b>Category:</b>
        <?= e($item['category']) ?>
    </p>

    <p>
        <b>Location:</b>
        <?= e($item['location']) ?>
    </p>

    <p>
        <b>Date:</b>
        <?= e($item['item_date']) ?>
    </p>

    <p>
        <b>Status:</b>
        <?= e($item['status']) ?>
    </p>

    <p>
        <b>Description:</b>
        <?= nl2br(e($item['description'])) ?>
    </p>

    <p>
        <b>Reported By:</b>
        <?= e($item['student_name']) ?>
    </p>


    <!-- =========================
         CLAIM SECTION
    ========================= -->

    <?php if ($item['status'] === 'Open'): ?>

        <hr>

        <h2>
            Claim This Item
        </h2>


        <?php if (!empty($message)): ?>

            <div class="success">
                <?= e($message) ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <?php if (logged()): ?>

            <?php if ($item['user_id'] != $_SESSION['user_id']): ?>

                <p style="color:#969bb2; margin-bottom:20px;">
                    Enter some details to prove that this item belongs to you.
                </p>


                <form method="POST">

                    <label>
                        Item Color
                    </label>

                    <input
                        type="text"
                        name="color"
                        placeholder="Example: Black"
                        required
                    >


                    <label>
                        Identifying Mark
                    </label>

                    <input
                        type="text"
                        name="identifying_mark"
                        placeholder="Example: Small scratch on the back"
                        required
                    >


                    <label>
                        Ownership Details
                    </label>

                    <textarea
                        name="ownership_details"
                        placeholder="Tell us something only the owner would know..."
                        required
                    ></textarea>


                    <button
                        type="submit"
                        name="claim"
                    >
                        Submit Claim
                    </button>

                </form>

            <?php else: ?>

                <div class="error">
                    You cannot claim your own reported item.
                </div>

            <?php endif; ?>


        <?php else: ?>

            <p style="color:#969bb2;">
                Please login to claim this item.
            </p>

            <a
                href="login.php"
                style="
                display:inline-block;
                margin-top:10px;
                padding:12px 20px;
                border-radius:9px;
                background:linear-gradient(135deg,#7c5cff,#5940d5);
                color:white;
                font-weight:700;
                "
            >
                Login to Claim
            </a>

        <?php endif; ?>


    <?php elseif ($item['status'] === 'Claimed'): ?>

        <hr>

        <div class="success">
            This item has already been claimed.
        </div>


    <?php elseif ($item['status'] === 'Returned'): ?>

        <hr>

        <div class="success">
            This item has been returned to its owner.
        </div>

    <?php endif; ?>


</div>


<?php include 'footer.php'; ?>


</body>

</html>