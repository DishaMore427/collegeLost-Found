<?php
require_once 'config.php';
require_admin();

/* =========================

   ADMIN STATISTICS
========================= */

$students = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role = 'student'
")->fetch_assoc()['total'];

$total_reports = $conn->query("
    SELECT COUNT(*) AS total 
    FROM items
")->fetch_assoc()['total'];

$lost_items = $conn->query("
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE type = 'Lost'
")->fetch_assoc()['total'];

$found_items = $conn->query("
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE type = 'Found'
")->fetch_assoc()['total'];

$pending_claims = $conn->query("
    SELECT COUNT(*) AS total 
    FROM claims 
    WHERE status = 'Pending'
")->fetch_assoc()['total'];

$returned_items = $conn->query("
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE status = 'Returned'
")->fetch_assoc()['total'];


/* =========================
   PENDING CLAIMS
========================= */

$claims = $conn->query("
    SELECT 
        claims.*,
        items.title AS item_title,
        items.type AS item_type,
        users.name AS claimant_name,
        users.roll_no AS claimant_roll
    FROM claims
    JOIN items ON claims.item_id = items.id
    JOIN users ON claims.claimant_id = users.id
    ORDER BY claims.created_at DESC
");


/* =========================
   ALL CAMPUS REPORTS
========================= */

$items = $conn->query("
    SELECT 
        items.*,
        users.name AS student_name,
        users.roll_no AS student_roll
    FROM items
    JOIN users ON items.user_id = users.id
    ORDER BY items.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Panel - CampusFind</title>

    <link rel="stylesheet" href="style.css">

</head>


<body>


<!-- =========================
     ADMIN NAVBAR
========================= -->

<nav>

    <a href="index.php">CampusFind</a>

    <a href="index.php">Home</a>

    <a href="items.php">Browse</a>

    <a href="admin.php">Admin Panel</a>

    <a href="logout.php">Logout</a>

</nav>



<!-- =========================
     ADMIN PANEL
========================= -->

<div class="admin-panel">

    <h1>Admin Panel</h1>

    <p>
        Manage college Lost & Found reports, verify claims and manage returned items.
    </p>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="admin-stats">

        <div>
            <h2><?= $students ?></h2>
            <p>Students</p>
        </div>

        <div>
            <h2><?= $total_reports ?></h2>
            <p>Total Reports</p>
        </div>

        <div>
            <h2><?= $lost_items ?></h2>
            <p>Lost Items</p>
        </div>

        <div>
            <h2><?= $found_items ?></h2>
            <p>Found Items</p>
        </div>

        <div>
            <h2><?= $pending_claims ?></h2>
            <p>Pending Claims</p>
        </div>

        <div>
            <h2><?= $returned_items ?></h2>
            <p>Returned</p>
        </div>

    </div>



    <!-- =========================
         CLAIM VERIFICATION
    ========================= -->

    <h2>Claim Verification</h2>


    <div style="overflow-x:auto;">

        <table>

            <tr>

                <th>Item</th>

                <th>Claimant</th>

                <th>Roll No.</th>

                <th>Color</th>

                <th>Identifying Mark</th>

                <th>Ownership Details</th>

                <th>Status</th>

                <th>Action</th>

            </tr>


            <?php if ($claims->num_rows > 0): ?>

                <?php while ($claim = $claims->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= e($claim['item_title']) ?>
                        </td>


                        <td>
                            <?= e($claim['claimant_name']) ?>
                        </td>


                        <td>
                            <?= e($claim['claimant_roll']) ?>
                        </td>


                        <td>
                            <?= e($claim['color']) ?>
                        </td>


                        <td>
                            <?= e($claim['identifying_mark']) ?>
                        </td>


                        <td>
                            <?= e($claim['ownership_details']) ?>
                        </td>


                        <td>

                            <?php if ($claim['status'] === 'Pending'): ?>

                                <span style="color:#f5c542;">
                                    Pending
                                </span>

                            <?php elseif ($claim['status'] === 'Verified'): ?>

                                <span style="color:#20c997;">
                                    Verified
                                </span>

                            <?php else: ?>

                                <span style="color:#ff5c70;">
                                    Rejected
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if ($claim['status'] === 'Pending'): ?>

                                <a
                                    href="admin_action.php?action=verify&id=<?= $claim['id'] ?>"
                                    style="
                                    display:inline-block;
                                    padding:8px 12px;
                                    margin-bottom:5px;
                                    border-radius:7px;
                                    background:#20c997;
                                    color:white;
                                    font-size:12px;
                                    font-weight:600;
                                    "
                                >
                                    Verify
                                </a>


                                <a
                                    href="admin_action.php?action=reject&id=<?= $claim['id'] ?>"
                                    onclick="return confirm('Reject this claim?');"
                                    style="
                                    display:inline-block;
                                    padding:8px 12px;
                                    border-radius:7px;
                                    background:#ff5c70;
                                    color:white;
                                    font-size:12px;
                                    font-weight:600;
                                    "
                                >
                                    Reject
                                </a>

                            <?php else: ?>

                                <span style="color:#777d95;">
                                    No Action
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" style="text-align:center;">
                        No claim requests found.
                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>



    <!-- =========================
         ALL CAMPUS REPORTS
    ========================= -->

    <h2 style="margin-top:60px;">
        All Campus Reports
    </h2>


    <div style="overflow-x:auto;">

        <table>

            <tr>

                <th>ID</th>

                <th>Photo</th>

                <th>Item</th>

                <th>Type</th>

                <th>Category</th>

                <th>Location</th>

                <th>Student</th>

                <th>Date</th>

                <th>Status</th>

                <th>Action</th>

            </tr>


            <?php if ($items->num_rows > 0): ?>

                <?php while ($row = $items->fetch_assoc()): ?>

                    <tr>


                        <!-- ID -->

                        <td>
                            <?= $row['id'] ?>
                        </td>



                        <!-- PHOTO -->

                        <td>

                            <?php if (!empty($row['image'])): ?>

                                <img
                                    src="uploads/<?= e($row['image']) ?>"
                                    alt="Item"
                                    style="
                                    width:70px;
                                    height:55px;
                                    object-fit:cover;
                                    border-radius:7px;
                                    "
                                >

                            <?php else: ?>

                                <span style="color:#777d95;">
                                    No Photo
                                </span>

                            <?php endif; ?>

                        </td>



                        <!-- ITEM -->

                        <td>

                            <a
                                href="item.php?id=<?= $row['id'] ?>"
                                style="color:#9b7cff;font-weight:700;"
                            >
                                <?= e($row['title']) ?>
                            </a>

                        </td>



                        <!-- TYPE -->

                        <td>

                            <?php if ($row['type'] === 'Lost'): ?>

                                <span style="color:#ff8291;">
                                    Lost
                                </span>

                            <?php else: ?>

                                <span style="color:#20c997;">
                                    Found
                                </span>

                            <?php endif; ?>

                        </td>



                        <!-- CATEGORY -->

                        <td>
                            <?= e($row['category']) ?>
                        </td>



                        <!-- LOCATION -->

                        <td>
                            <?= e($row['location']) ?>
                        </td>



                        <!-- STUDENT -->

                        <td>

                            <?= e($row['student_name']) ?>

                            <br>

                            <small style="color:#777d95;">
                                Roll No:
                                <?= e($row['student_roll']) ?>
                            </small>

                        </td>



                        <!-- DATE -->

                        <td>
                            <?= e($row['item_date']) ?>
                        </td>



                        <!-- STATUS -->

                        <td>

                            <?php if ($row['status'] === 'Open'): ?>

                                <span style="color:#9b7cff;">
                                    Open
                                </span>

                            <?php elseif ($row['status'] === 'Claimed'): ?>

                                <span style="color:#f5c542;">
                                    Claimed
                                </span>

                            <?php else: ?>

                                <span style="color:#20c997;">
                                    Returned
                                </span>

                            <?php endif; ?>

                        </td>



                        <!-- ACTION -->

                        <td style="min-width:150px;">


                            <!-- MARK RETURNED -->

                            <?php if ($row['status'] !== 'Returned'): ?>

                                <a
                                    href="admin_action.php?action=returned&id=<?= $row['id'] ?>"
                                    onclick="return confirm('Mark this item as Returned?');"
                                    style="
                                    display:inline-block;
                                    padding:8px 12px;
                                    margin-bottom:7px;
                                    border-radius:7px;
                                    background:#20c997;
                                    color:white;
                                    font-size:12px;
                                    font-weight:600;
                                    "
                                >
                                    Mark Returned
                                </a>

                                <br>

                            <?php endif; ?>



                            <!-- DELETE -->

                            <a
                                href="admin_action.php?action=delete&id=<?= $row['id'] ?>"
                                onclick="return confirm('Are you sure you want to permanently delete this item and its photo?');"
                                style="
                                display:inline-block;
                                padding:8px 12px;
                                border-radius:7px;
                                background:#ff5c70;
                                color:white;
                                font-size:12px;
                                font-weight:600;
                                "
                            >
                                Delete
                            </a>


                        </td>


                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="10" style="text-align:center;">

                        No campus reports found.

                    </td>

                </tr>

            <?php endif; ?>


        </table>

    </div>


</div>



<!-- =========================
     FOOTER
========================= -->

<footer>

    <p>
        CampusFind © <?= date('Y') ?> |
        College Lost & Found System
    </p>

</footer>


</body>

</html>