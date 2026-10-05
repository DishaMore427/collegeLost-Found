<?php
require_once "config.php";

$recent = $conn->query("
    SELECT *
    FROM items
    ORDER BY created_at DESC
    LIMIT 6
");
?>

<?php include "header.php"; ?>

<section class="hero">
<h1>
    <?php if (logged()): ?>
        Hello, <?= e($_SESSION['name']) ?> 👋
        <br>
        Welcome to CampusFind
    <?php else: ?>
        Lost Something On Campus?
    <?php endif; ?>
</h1>


    <p>
        Find your lost belongings or report an item
        found around your college.
    </p>

    <?php if (logged()): ?>

        <a href="report.php">
            Report an Item
        </a>

    <?php else: ?>

        <a href="login.php">
            Get Started
        </a>

    <?php endif; ?>

    <a href="items.php">
        Browse Items
    </a>
    

</section>


<section>

    <h2>Recent Campus Reports</h2>

    <div class="items-grid">

        <?php while ($item = $recent->fetch_assoc()): ?>

            <div class="item-card">

                <?php if ($item["image"]): ?>

                    <img
                        src="uploads/<?php echo e($item["image"]); ?>"
                        alt="Item"
                    >

                <?php endif; ?>

                <h3>
                    <?php echo e($item["title"]); ?>
                </h3>

                <p>
                    <?php echo e($item["type"]); ?>
                </p>

                <p>
                    <?php echo e($item["location"]); ?>
                </p>

                <p>
                    Status:
                    <?php echo e($item["status"]); ?>
                </p>

                <a href="item.php?id=<?php echo $item["id"]; ?>">
                    View Details
                </a>

            </div>

        <?php endwhile; ?>

    </div>

</section>


<section>

    <h2>College Lost & Found</h2>

    <p class="text-center">
        A simple platform for students to report,
        search and recover lost belongings within campus.
        Helping students find what they lost and return what they found.
    </p>

</section>


<?php include "footer.php"; ?>