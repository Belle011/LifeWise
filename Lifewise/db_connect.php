<?php
$host = "localhost"; // Change if using a remote database
$user = "root"; // Change if using another database user
$password = ""; // Change if you have a database password
$database = "lifewise"; // Ensure this matches your database name

// Enable error reporting (for debugging, remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create connection
$conn = new mysqli("localhost", "root", "", "lifewise", 3306);


// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}
?>
