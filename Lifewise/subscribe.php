<?php
include 'db_connect.php';
include 'config.php';
session_start();

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You must be logged in to subscribe.');
        window.location.href = 'index.html';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // ✅ Logged-in user
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $plan = strtolower(mysqli_real_escape_string($conn, $_POST['plan']));

    // ✅ Normalize phone number
    $phone = preg_replace('/\D/', '', $phone);
    if (preg_match('/^(0(1|7)\d{8})$/', $phone)) {
        $phone = '254' . substr($phone, 1);
    } elseif (preg_match('/^(1|7)\d{8}$/', $phone)) {
        $phone = '254' . $phone;
    } elseif (!preg_match('/^254(1|7)\d{8}$/', $phone)) {
        echo "<script>
            alert('Invalid phone number format.');
            window.history.back();
        </script>";
        exit;
    }

    // Plans
    $plans = [
        'daily' => ['amount' => 10, 'days' => 1],
        'weekly' => ['amount' => 50, 'days' => 7],
        'monthly' => ['amount' => 150, 'days' => 30],
        'annually' => ['amount' => 1500, 'days' => 365]
    ];

    if (!isset($plans[$plan])) {
        echo "<script>alert('Invalid plan'); window.history.back();</script>";
        exit;
    }

    $amount = $plans[$plan]['amount'];
    $expiry = date('Y-m-d H:i:s', strtotime("+{$plans[$plan]['days']} days"));
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    // Get M-Pesa access token
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    $headers = ['Authorization: Basic ' . $credentials];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    $access_token = json_decode($response)->access_token;

    // STK Push
    $stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $transaction_id = uniqid('TXN_');
    $stk_payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => 'LifeWise',
        'TransactionDesc' => ucfirst($plan) . ' Subscription'
    ];

    $stk_headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $stk_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stk_headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stk_payload));
    $stk_response = curl_exec($curl);
    curl_close($curl);
    $responseData = json_decode($stk_response, true);

    if (isset($responseData['MerchantRequestID'])) {
        $status = 'pending';
        $status_message = 'STK push sent';
        $payment_date = date('Y-m-d H:i:s');

        // ✅ Add user_id to the INSERT
        $sql = "INSERT INTO subscriptions (user_id, phone_number, amount, transaction_id, payment_date, expiry_date, status, status_message, plan)
                VALUES ('$user_id', '$phone', $amount, '$transaction_id', '$payment_date', '$expiry', '$status', '$status_message', '$plan')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>
                alert('STK push sent. Please check your phone.');
                window.location.href = 'dash.html';
            </script>";
        } else {
            $error = mysqli_error($conn);
            echo "<script>
                alert('Error storing subscription: $error');
                window.history.back();
            </script>";
        }
    } else {
        $errorMessage = $responseData['errorMessage'] ?? 'STK push failed.';
        echo "<script>
            alert('M-Pesa STK Push Error: $errorMessage');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request method.');
        window.history.back();
    </script>";
}
?>
