<?php
require_once "config.php";
require_login();

$message = "";

$categories = [
    "ID Card",
    "Books",
    "Notebook",
    "Bag",
    "Mobile Phone",
    "Laptop",
    "Charger",
    "USB Drive",
    "Wallet",
    "Keys",
    "Earphones",
    "Documents",
    "Other"
];

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $type = $_POST["type"];
    $title = trim($_POST["title"]);
    $category = $_POST["category"];
    $description = trim($_POST["description"]);
    $location = $_POST["location"];
    $item_date = $_POST["item_date"];

    $image_name = "";

    if ($title == "" || $description == "" || $item_date == "") {
        $message = "Please fill all required fields.";
    } else {

        /* IMAGE UPLOAD */

        if (!empty($_FILES["image"]["name"])) {

            $allowed = ["jpg", "jpeg", "png", "webp"];

            $file_name = $_FILES["image"]["name"];
            $file_tmp = $_FILES["image"]["tmp_name"];
            $file_size = $_FILES["image"]["size"];

            $extension = strtolower(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );

            if (!in_array($extension, $allowed)) {

                $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } elseif ($file_size > 3 * 1024 * 1024) {

                $message = "Image size must be less than 3 MB.";

            } else {

                $image_name =
                    time() . "_" .
                    preg_replace("/[^A-Za-z0-9._-]/", "_", $file_name);

                $upload_folder = "uploads/";

                if (!is_dir($upload_folder)) {
                    mkdir($upload_folder, 0777, true);
                }

                move_uploaded_file(
                    $file_tmp,
                    $upload_folder . $image_name
                );
            }
        }

        if ($message == "") {

            $stmt = $conn->prepare("
                INSERT INTO items
                (user_id, type, title, category, description, location, item_date, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isssssss",
                $_SESSION["user_id"],
                $type,
                $title,
                $category,
                $description,
                $location,
                $item_date,
                $image_name
            );

            if ($stmt->execute()) {
                header("Location: dashboard.php?reported=1");
                exit;
            } else {
                $message = "Unable to report item.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Item</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form-container">

    <h2>Report Lost / Found Item</h2>

    <?php if ($message): ?>
        <p class="error"><?php echo e($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Report Type</label>

        <select name="type" required>
            <option value="Lost">Lost Item</option>
            <option value="Found">Found Item</option>
        </select>

        <input
            type="text"
            name="title"
            placeholder="Item Name"
            required
        >

        <label>Category</label>

        <select name="category" required>

            <?php foreach ($categories as $category): ?>

                <option value="<?php echo e($category); ?>">
                    <?php echo e($category); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <textarea
            name="description"
            placeholder="Describe the item"
            required
        ></textarea>

        <label>College Location</label>

        <select name="location" required>

            <?php foreach ($locations as $location): ?>

                <option value="<?php echo e($location); ?>">
                    <?php echo e($location); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <label>Date</label>

        <input
            type="date"
            name="item_date"
            required
        >

        <label>Item Image</label>

        <input
            type="file"
            name="image"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <button type="submit">
            Submit Report
        </button>

    </form>

</div>

</body>
</html>