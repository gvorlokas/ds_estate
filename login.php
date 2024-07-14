<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ds_estate";

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and report any errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = '';
$success = '';

// Check if the form has been submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User login process
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare SQL to fetch user details based on the username
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Validate if any user exists with the given username
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if ($password === $user['password']) {
                // Set session variables on successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $first_name = $_SESSION['first_name'] ?? '';
                $last_name = $_SESSION['last_name'] ?? '';
                $email = $_SESSION['email'] ?? '';
                // Redirect to feed page after successful login
                header("Location: feed.php");
                exit();
            } else {
                // Set error message if password doesn't match
                $error = "Invalid username or password.";
            }
        } else {
            // Set error message if username doesn't exist
            $error = "Invalid username or password.";
        }

        // Close statement
        $stmt->close();
        // User registration process
    } elseif (isset($_POST['register'])) {
        $username = $_POST['reg_username'];
        $email = $_POST['reg_email'];
        $password = $_POST['reg_password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];

        // Check if first_name and last_name contain only letters
        if (!ctype_alpha($first_name) || !ctype_alpha($last_name)) {
            $error = "First name and last name must contain only letters.";
        } elseif (strlen($password) < 4 || strlen($password) > 10) {
            $error = "Password must be between 4 and 10 characters.";
        } elseif (!preg_match('/\d/', $password)) {  // Check for at least one digit
            $error = "Password must contain at least one number.";
        } else {
            // Check if username or email already exists
            $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username or email already exists.";
            } else {
                // Insert new user into database
                $sql = "INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $username, $email, $password, $first_name, $last_name);

                if ($stmt->execute()) {
                    $success = "Registration successful. You can now log in.";
                } else {
                    $error = "Error: " . $conn->error;
                }
            }

            $stmt->close();
        }
        // Logout process
    } elseif (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="feed.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="nav-container">
            <!-- Logo and site title -->
            <div class="nav-left">
                <a href="/" class="nav-logo">
                    <img src="dsestate.jpg" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
                </a>
                <a href="#" class="nav-title">
                    DS ESTATE
                </a>

            </div>
            <div class="nav-right">
                <!-- Mobile navigation toggle -->
                <div class="nav-toggle" id="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <!-- Navigation menu for different pages -->
        <div class="nav-menu" id="nav-menu">
                <ul>
                    <li><a href="feed.php">Feed</a></li>
                    <li><a href="create_listing.php">Create Listing</a></li>
                    <?php if (isset($_SESSION['username'])): ?>
                        <li><form method="post" action="login.php"><button type="submit" name="logout">Logout</button></form></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
    </nav>

    <!-- Login form -->
    <h2>Login</h2>
    <form class="login-form" method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="login">Login</button>
    </form>
    <button class="toggle-register-btn" onclick="toggleRegister()">Don't have an account? Register here</button>
    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <p class="message" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="message" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <!-- Registration form -->
    <div id="register-form" style="display: none;">
        <h2>Register</h2>
        <form class="register-form" method="post" action="login.php">
            <label for="reg_username">Username:</label>
            <input type="text" id="reg_username" name="reg_username" required>
            <br>
            <label for="reg_email">Email:</label>
            <input type="email" id="reg_email" name="reg_email" required>
            <br>
            <label for="reg_password">Password:</label>
            <input type="password" id="reg_password" name="reg_password" required>
            <br>
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <br>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <br>
            <button type="submit" name="register">Register</button>
        </form>
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

        // JavaScript for toggling the registration
        function toggleRegister() {
            var form = document.getElementById('register-form');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>