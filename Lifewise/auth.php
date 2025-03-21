<?php
include './db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["register"])) {
        // Registration Logic
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            echo "Registration successful! <a href='index.html'>Login Here</a>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST["login"])) {
        // Login Logic
        $identifier = $_POST["identifier"]; // Username or Email
        $password = $_POST["password"];

        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["username"] = $user["username"];
                header("Location: dash.html"); // Redirect to Dashboard
                exit();
            } else {
                echo "Invalid password!";
            }
        } else {
            echo "User not found!";
        }

        $stmt->close();
    }
}

$conn->close();
?>