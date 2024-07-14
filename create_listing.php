<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']);

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ds_estate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $area = $_POST['area'];
    $rooms = $_POST['rooms'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id'];

    // Server-Side Validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $title) || !preg_match("/^[a-zA-Z\s]+$/", $area)) {
        $error .= "Title and area can only contain letters and spaces.<br>";
    }

    if ($rooms < 1 || $price < 0.01) {
        $error .= "Rooms and price must be positive numbers.<br>";
    }
    
    // Handle file upload
    $target_dir = "../ErgasiaExaminou/uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
    }

    // Check file size (limit to 5MB)
    if ($_FILES["image"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
    }

    // Allow certain file formats
    $allowed_formats = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_formats)) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }

    // Check if $error is empty to proceed with file upload and database insertion
    if (empty($error)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Insert listing into database
            $sql = "INSERT INTO listings (image, title, area, rooms, price, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdi", $target_file, $title, $area, $rooms, $price, $user_id);

            if ($stmt->execute()) {
                $success = "Listing created successfully.";
            } else {
                $error = "Error: " . $conn->error;
            }

            $stmt->close();
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
// Fetch the user details
$userID = $_SESSION['user_id'] ?? null;

$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing</title>
    <link rel="stylesheet" href="feed.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="nav-container">
            <div class="nav-left">
                <a href="/" class="nav-logo">
                    <img src="dsestate.jpg" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
                </a>
                <a href="#" class="nav-title">
                    DS ESTATE
                </a>

            </div>
            <div class="nav-right">
                <div class="nav-toggle" id="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <div class="nav-menu" id="nav-menu">
                <ul>
                    <li><a href="feed.php">Feed</a></li>
                    <li><a href="create_listing.php">Create Listing</a></li>
                    <?php if ($is_logged_in): ?>
                            <li><a href="logout.php">Logout</a></li>
                            <?php if (isset($_SESSION['first_name'])): ?>
                                <li><span>Welcome, <?php echo $user['first_name']; ?></span></li>
                            <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <h2>Create Listing</h2>
    <!-- Form for creating a new property listing -->
    <form id="listingform" method="post" action="create_listing.php" enctype="multipart/form-data">

    <!-- Display error messages if any -->
        <?php if (!empty($error)): ?>
        <div class="error-messages">
            <ul>
                <?php foreach (explode("<br>", $error) as $error1): ?>
                    <?php if (!empty($error1)): ?>
                        <li><?php echo $error1; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        <br>
        <label for="area">Area:</label>
        <input type="text" id="area" name="area" required>
        <br>
        <label for="rooms">Rooms:</label>
        <input type="number" id="rooms" name="rooms" required>
        <br>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required>
        <br>
        <label for="image">Image:</label>
        <input type="file" id="image" name="image" required>
        <br>
        <button type="submit">Create Listing</button>
    </form>
    <?php if ($success): ?>
        <p class="message" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div>
            <p>Contact Us: <br><br>Telephone: <a href="tel:2104142000">2104142000</a>, <br>E-mail: <a href="mailto:info@dsestate.com">info@dsestate.com</a></p>
        </div>
        <div>
            <p>Location: </p>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d50344.34922445737!2d23.624746007026246!3d37.941599968567616!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14a1bbe5bb8515a1%3A0x3e0dce8e58812705!2zzqDOsc69zrXPgM65z4PPhM6uzrzOuc6_IM6gzrXOuc-BzrHOuc-Oz4I!5e0!3m2!1sel!2sgr!4v1719335541932!5m2!1sel!2sgr" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </footer>

    <script>
        // JavaScript for toggling the mobile menu
        document.getElementById('nav-toggle').addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('active');
        });

        // JavaScript for check the form
        document.getElementById('create-listing-form').addEventListener('submit', function(event) {
        var title = document.getElementById('title').value;
        var area = document.getElementById('area').value;
        var rooms = document.getElementById('rooms').value;
        var price = document.getElementById('price').value;

        // Regular expressions for validation
        var letterRegex = /^[a-zA-Z\s]+$/;
        var positiveNumberRegex = /^\d*\.?\d+$/;  // Accepts positive integers and decimals

        if (!letterRegex.test(title) || !letterRegex.test(area)) {
            alert('Title and area can only contain letters and spaces.');
            event.preventDefault();  // Prevent form submission
        }

        if (!positiveNumberRegex.test(rooms) || !positiveNumberRegex.test(price)) {
            alert('Rooms and price must be positive numbers.');
            event.preventDefault();  // Prevent form submission
        }
        });
</script>

    </script>
</body>
</html>
