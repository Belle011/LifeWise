<?php
include './db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // **Registration Logic**
    if (isset($_POST["register"])) {
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
    }
    
    // **Login Logic**
    elseif (isset($_POST["login"])) {
        $identifier = $_POST["identifier"]; // Username or Email
        $password = $_POST["password"];

        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {
                // Start user session and store details
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["subscribed"] = $user["subscribed"]; // You can store subscription status if needed

                // Redirect to dashboard
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

    
// **Contact Form Logic**
elseif (isset($_POST["contact_submit"])) {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "All fields are required!";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($stmt->execute()) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com';
            $mail->Password = 'your-email-password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($email, $name);
            $mail->addAddress('admin-email@example.com', 'Admin');
            $mail->Subject = "New Contact Form Message: $subject";
            $mail->Body = "You received a message from:\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

            $mail->send();
            echo "Message sent successfully!";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error sending message.";
    }
    $stmt->close();
}
}
$conn->close();
?>