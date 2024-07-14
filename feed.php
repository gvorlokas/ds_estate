<?php
// Start or resume a session
session_start();

// Set database connection credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ds_estate";

// Establish connection to the MySQL server
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if database connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch all listings from the database
$sql = "SELECT id, image, title, area, rooms, price FROM listings";
$result = $conn->query($sql);

// Initialize an array to hold listings data
$listings = [];
if ($result->num_rows > 0) {
    // Retrieve each row and add to listings array
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}


// Check if a user is logged in by checking session variable
$is_logged_in = isset($_SESSION['username']);

// Fetch user details if logged in
$userID = $_SESSION['user_id'] ?? null;

// Prepare SQL statement to fetch user details from database
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Close the prepared statement and database connection
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="feed.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <!-- Logo and navigation link to home -->
            <div class="nav-left">
                <a href="/" class="nav-logo">
                    <img src="dsestate.jpg" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
                </a>
                <a href="#" class="nav-title">
                    DS ESTATE
                </a>

            </div>
            <!-- Mobile menu toggle button -->
            <div class="nav-right">
                <div class="nav-toggle" id="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <!-- Navigation menu -->
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

    <main id="feed">
        <!-- Display each listing -->
        <?php foreach ($listings as $listing): ?>
            <div class="listing">
                <img src="<?php echo htmlspecialchars($listing['image']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                <div class="info">
                    <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
                    <p>Area: <?php echo htmlspecialchars($listing['area']); ?></p>
                    <p>Rooms: <?php echo htmlspecialchars($listing['rooms']); ?></p>
                    <p>Price per night: $<?php echo htmlspecialchars($listing['price']); ?></p>
                    <form id="feedform" method="GET" action="book.php">
                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                        <button type="submit">Book</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

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