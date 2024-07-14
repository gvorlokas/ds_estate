<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ds_estate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch listing details
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$listing = null;

if ($listing_id > 0) {
    $sql = "SELECT * FROM listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $listing = $result->fetch_assoc();
    } else {
        die("Listing not found.");
    }
    $stmt->close();
}

$available = false;
$total_price = 0;

// Check availability
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_availability'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    if($check_in>=$check_out) {
        $error = "Check out must be later than check in.";
    } else {
        $sql = "SELECT * FROM reservations WHERE listing_id = ? AND (check_out > ? AND check_in < ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $listing_id, $check_in, $check_out);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "The listing is not available for the selected dates.";
        } else {
            $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
            $initial_price = $listing['price'] * $nights;
            $discount = rand(10, 30) / 100;
            $total_price = $initial_price - ($initial_price * $discount);
            $_SESSION['total_price'] = $total_price;
            $_SESSION['check_in'] = $check_in;
            $_SESSION['check_out'] = $check_out;
            $available = true;
        }
        $stmt->close();
    }

    
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
    <title>Book Listing</title>
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
    
    <!-- Main content area for booking a listing -->
    <h2>Booking</h2>
    <div class="booking-container">
        <div class="property-details">
            <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
            <p>Price per night: €<?php echo htmlspecialchars($listing['price']); ?></p>
            <p>Number of rooms: <?php echo htmlspecialchars($listing['rooms']); ?></p>
            <div class="property-image">
                <img src="<?php echo htmlspecialchars($listing['image']); ?>" alt="Image of the property">
            </div>
        </div>

        <div class="booking-form">
            <?php if (isset($error)): ?>
                <p style="color: red; text-align:center;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <!-- Display the form for checking availability if the listing is not yet booked -->
            <?php if (!$available): ?>
                <form class="right-form" method="post">
                    <label for="check_in">Check-in Date:</label>
                    <input type="date" id="check_in" name="check_in" min="<?php echo date('Y-m-d'); ?>" required>
                    <br>
                    <label for="check_out">Check-out Date:</label>
                    <input type="date" id="check_out" name="check_out" min="<?php echo date('Y-m-d'); ?>" required>
                    <br>
                    <button type="submit" name="check_availability">Check Availability</button>
                </form>
            <?php else: ?>
                <!-- Display the booking form with user details and total price if available -->
                <form action="confirm_booking.php?listing_id=<?php echo $listing_id; ?>" method="post">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    <p>Total Price: €<?php echo number_format($total_price, 2); ?></p>
                    <button type="submit" name="finalize_booking">Book Property</button>
                </form>
            <?php endif; ?>
        </div>
        
    </div>

    <!-- Footer section -->
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
