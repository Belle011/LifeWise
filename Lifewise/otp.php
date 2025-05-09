<?php
class phpmailer
session_start();
require_once 'db_connect.php';

// PHPMailer files
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- FORGOT PASSWORD ---
if  ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Send OTP
    if (isset($_POST["forgot_password"])) {
        $email = $_POST["email"];

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];

            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+2 minutes"));

            // Save OTP in the database
            $stmt = $conn->prepare("INSERT INTO otp_requests (user_id, email, otp_code, otp_expiry) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE otp_code=?, otp_expiry=?");
            $stmt->bind_param("isssss", $user_id, $email, $otp, $expiry, $otp, $expiry);
            $stmt->execute();

            // Send OTP via PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'rosebellawandere@gmail.com'; // Use your Gmail email here
                $mail->Password   = 'your_app_password'; // Use your app-specific password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Sender and recipient details
                $mail->setFrom('rosebellawandere@gmail.com', 'LifeWise System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP';
                $mail->Body    = "Your OTP is: <b>$otp</b>. It is valid for 2 minutes.";

                // Send the email
                $mail->send();
                echo "OTP sent to your email. <a href='auth.php?reset=true'>Enter OTP</a>";
            } catch (Exception $e) {
                echo "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "No account found with this email.";
        }
        $stmt->close();
    }

    // Verify OTP
    elseif (isset($_POST["verify_otp"])) {
        $email = $_POST["email"];
        $entered_otp = $_POST["otp"];

        $stmt = $conn->prepare("SELECT * FROM otp_requests WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
        $stmt->bind_param("ss", $email, $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['verified_email'] = $email;

            // Delete OTP after successful verification
            $stmt = $conn->prepare("DELETE FROM otp_requests WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            echo "OTP Verified! <a href='auth.php?reset=true'>Reset Password</a>";
        } else {
            echo "Invalid or expired OTP.";
        }
        $stmt->close();
    }

    // Reset Password
    elseif (isset($_POST["reset_password"])) {
        if (!isset($_SESSION['verified_email'])) {
            die("Unauthorized access.");
        }

        $email = $_SESSION['verified_email'];
        $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();

        echo "Password reset successful. You can now <a href='index.html'>Login</a>";

        // Remove session variables after reset
        session_unset();
        session_destroy();
    }
}

// Display Reset Password Form if ?reset=true
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    echo '<form method="POST" action="auth.php">
            <input type="password" name="new_password" placeholder="Enter New Password" required>
            <button type="submit" name="reset_password">Reset Password</button>
          </form>';
}
?>
