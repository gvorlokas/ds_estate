<?php
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
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

// Υποβολή της κράτησης και αποθήκευση στη βάση δεδομένων
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finalize_booking'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $user_id = $_SESSION['user_id'];
    $listing_id = $_GET['listing_id'];
    $check_in = $_SESSION['check_in'];
    $check_out = $_SESSION['check_out'];
    $total_price = $_SESSION['total_price'];

    $sql = "INSERT INTO reservations (user_id, listing_id, first_name, last_name, email, check_in, check_out, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssd", $user_id, $listing_id, $first_name, $last_name, $email, $check_in, $check_out, $total_price);
    if ($stmt->execute()) {
        $success = "Your reservation has been successfully made!";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $error = "Invalid request. Please complete the form to book the property.";
}
// Check if user is logged in
$is_logged_in = isset($_SESSION['username']);

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
    <title>Reservation Confirmation</title>
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
    </nav>
    
    <!-- Message for booking confirmation -->
    <div class="confirmation-container">               
        <h1 style="text-align: center; color: white; background-color: #005792; padding: 20px; margin: 20px; border-radius: 10px;">Reservation Confirmation</h1>
        <?php if (isset($success)): ?>
            <p style="text-align: center; color: white;"><?php echo $success; ?></p>
            <a href="feed.php" class="return-to-feed" style="display: block; text-align: center; color: white;">Return to Feed</a>
        <?php else: ?>
            <p style="text-align: center; color: white;"><?php echo $error; ?></p>
            <a href="book.php?listing_id=<?php echo $listing_id; ?>" style="display: block; text-align: center; color: white;">Return to Booking Form</a>
        <?php endif; ?>
    </div>

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
    </script>
</body>
</html>
