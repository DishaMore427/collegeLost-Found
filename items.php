<?php
require_once "config.php";

$search = trim($_GET["search"] ?? "");
$type = $_GET["type"] ?? "";
$location = $_GET["location"] ?? "";

$sql = "
    SELECT items.*, users.name
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE 1
";

$params = [];
$types = "";

if ($search != "") {

    $sql .= "
        AND (
            items.title LIKE ?
            OR items.category LIKE ?
            OR items.location LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}

if ($type == "Lost" || $type == "Found") {

    $sql .= " AND items.type = ?";

    $params[] = $type;
    $types .= "s";
}

if ($location != "") {

    $sql .= " AND items.location = ?";

    $params[] = $location;
    $types .= "s";
}

$sql .= " ORDER BY items.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$items = $stmt->get_result();

$locations = [
    "Main Gate",
    "Library",
    "Computer Lab",
    "IT Department",
    "Classroom",
    "Canteen",
    "Parking Area",
    "College Ground",
    "Auditorium",
    "College Corridor",
    "Security Desk",
    "Other"
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Items</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="items-page">

    <h1>Campus Lost & Found</h1>

    <form method="GET">

        <input
            type="text"
            name="search"
            placeholder="Search item..."
            value="<?php echo e($search); ?>"
        >

        <select name="type">

            <option value="">All</option>

            <option
                value="Lost"
                <?php if ($type == "Lost") echo "selected"; ?>
            >
                Lost
            </option>

            <option
                value="Found"
                <?php if ($type == "Found") echo "selected"; ?>
            >
                Found
            </option>

        </select>

        <select name="location">

            <option value="">All Locations</option>

            <?php foreach ($locations as $loc): ?>

                <option
                    value="<?php echo e($loc); ?>"
                    <?php if ($location == $loc) echo "selected"; ?>
                >
                    <?php echo e($loc); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit">Search</button>

    </form>

    <div class="items-grid">

        <?php while ($row = $items->fetch_assoc()): ?>

            <div class="item-card">

                <?php if ($row["image"]): ?>

                    <img
                        src="uploads/<?php echo e($row["image"]); ?>"
                        alt="Item"
                    >

                <?php endif; ?>

                <h3><?php echo e($row["title"]); ?></h3>

                <p><?php echo e($row["type"]); ?></p>

                <p><?php echo e($row["category"]); ?></p>

                <p><?php echo e($row["location"]); ?></p>

                <p>Status: <?php echo e($row["status"]); ?></p>

                <a href="item.php?id=<?php echo $row["id"]; ?>">
                    View Details
                </a>

            </div>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>