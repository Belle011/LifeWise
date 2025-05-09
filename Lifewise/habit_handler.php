<?php
header("Content-Type: application/json");
require_once "./db_connect.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_POST['action'] ?? null;

if (!$action) {
    echo json_encode(["error" => "No action specified"]);
    exit();
}

// SAVE HABIT
if ($action === "save_habit") {
    $habit = $_POST['habit'] ?? '';
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);

    if (empty($habit)) {
        echo json_encode(["error" => "Habit is required"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO habits (habit_name, month, year) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $habit, $month, $year);
    
    if ($stmt->execute()) {
        echo json_encode(["habit_id" => $stmt->insert_id]);
    } else {
        echo json_encode(["error" => "Failed to save habit"]);
    }
    $stmt->close();
}

// SAVE PROGRESS
elseif ($action === "save_progress") {
    $habit_id = intval($_POST['habit_id'] ?? 0);
    $day = intval($_POST['day'] ?? 0);
    $completed = intval($_POST['completed'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);

    if ($habit_id <= 0 || $day <= 0) {
        echo json_encode(["error" => "Missing or invalid values"]);
        exit();
    }

    // Upsert progress (insert or update)
    $stmt = $conn->prepare("INSERT INTO progress (habit_id, day, completed, month, year)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE completed = VALUES(completed)");
    $stmt->bind_param("iiiii", $habit_id, $day, $completed, $month, $year);

    if ($stmt->execute()) {
        echo json_encode(["status" => "progress_saved"]);
    } else {
        echo json_encode(["error" => "Failed to save progress"]);
    }
    $stmt->close();
}

// RESET PROGRESS
elseif ($action === "reset_progress") {
    $habit_id = intval($_POST['habit_id'] ?? 0);
    
    if ($habit_id <= 0) {
        echo json_encode(["error" => "Invalid habit ID"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM progress WHERE habit_id = ?");
    $stmt->bind_param("i", $habit_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "progress_reset"]);
    } else {
        echo json_encode(["error" => "Failed to reset progress"]);
    }
    $stmt->close();
}

else {
    echo json_encode(["error" => "Unknown action"]);
}
?>
