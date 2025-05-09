<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html'); // Redirect to login page if user is not logged in
    exit();
}

// Database connection
require_once('db_connect.php'); // Make sure this defines $conn as your MySQLi connection

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];

// Query to check if any active subscription exists for the logged-in user
$sql = "SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW() AND (status = 'pending' OR status = 'active')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();

// If no active subscription is found, redirect to the subscription page
if (!$subscription) {
    header('Location: subscriptions.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Period Tracker</title>
    <link href="https://fonts.cdnfonts.com/css/kindegarten" rel="stylesheet">
    <style>
        body {
          background-image: url("clouds.jpg");
          background-size: 500px;
          background-color: rgb(255, 227, 227);
          background-blend-mode: lighter;
          text-align: center;
          font-family: "Kindergarten", sans-serif;
        }
        #title {
          margin-top: 100px;
          line-height: 10px;
        }
        #subtitle {
          opacity: 0.7;
          font-size: 18px;
          margin-bottom: 20px;
        }
        #calenderContainer {
          display: flex;
          justify-content: center;
        }
        #calenderDiv {
          background-color: rgb(255, 255, 255);
          border-radius: 10px 10px 20px 20px;
          margin: auto;
          align-content: center;
          text-align: center;
        }
        #calenderHeading {
          display: flex;
          justify-content: space-between;
          border-radius: 10px 10px 0px 0px;
          border: 2px solid black;
          padding: 0px 20px;
          font-size: 20px;
          background-color: pink;
          line-height: 0px;
        }
        #calenderContent {
          padding: 20px;
          border: 2px solid black;
        }
        .days {
          display: flex;
        }
        .day {
          width: 30px;
          margin: 3px;
          padding: 10px;
          font-size: 15px;
          text-align: center;
          border-radius: 20px;
        }
        .back-btn a {
        display: inline-block;
        background-color: pink;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    </style>
</head>
<body>
    <h1 id="title">Month</h1>
    <h2 id="subtitle">My Habit Tracker</h2>

    <div id="calenderContainer">
        <div id="calenderDiv">
            <div id="calenderHeading">
                <p id="habitTitle">My New Habit</p>
                <p id="totalDays">0/31</p>
            </div>
            <div id="calenderContent">
                <div id="tracker">
                    <!-- Repeat for 31 days -->
                    <div class="days">
                        <div class="day">1</div><div class="day">2</div><div class="day">3</div><div class="day">4</div>
                        <div class="day">5</div><div class="day">6</div><div class="day">7</div>
                    </div>
                    <div class="days">
                        <div class="day">8</div><div class="day">9</div><div class="day">10</div><div class="day">11</div>
                        <div class="day">12</div><div class="day">13</div><div class="day">14</div>
                    </div>
                    <div class="days">
                        <div class="day">15</div><div class="day">16</div><div class="day">17</div><div class="day">18</div>
                        <div class="day">19</div><div class="day">20</div><div class="day">21</div>
                    </div>
                    <div class="days">
                        <div class="day">22</div><div class="day">23</div><div class="day">24</div><div class="day">25</div>
                        <div class="day">26</div><div class="day">27</div><div class="day">28</div>
                    </div>
                    <div class="days">
                        <div class="day">29</div><div class="day">30</div><div class="day">31</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button id="resetButton">Reset Button</button>
    <div class="back-btn">
  <a href="./dash.html">Back to dashboard</a>
</div>
  <footer>
    <p>&copy; 2025 LifeWise - Empowering Health Choices</p>
  </footer>

    <script>
        const date = new Date();
        const currentMonth = date.getMonth();
        const currentYear = date.getFullYear();
        const currentDate = date.getDate();

        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const daysInTheMonthList = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        const daysInThisMonth = daysInTheMonthList[currentMonth];

        let habit_id = sessionStorage.getItem("habit_id");
        let daysCompleted = 0;

        const title = document.getElementById("title");
        const habitTitle = document.getElementById("habitTitle");
        const totalDays = document.getElementById("totalDays");
        const dayDivs = document.querySelectorAll(".day");
        const resetButton = document.getElementById("resetButton");

        title.textContent = months[currentMonth];

        async function promptSetHabit(initial = false) {
            const message = initial ? "Please set your habit to start tracking:" : "Update your habit:";
            const habit = prompt(message, habitTitle.textContent);

            if (!habit || habit.trim() === "") {
                habitTitle.textContent = "Click to set your habit";
                sessionStorage.removeItem("habit_id");
                habit_id = null;
                return;
            }

            habitTitle.textContent = habit;

            try {
                const res = await fetch("habit_handler.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=save_habit&habit=${encodeURIComponent(habit)}&month=${currentMonth}&year=${currentYear}`
                });
                const data = await res.json();
                if (data.habit_id) {
                    habit_id = data.habit_id;
                    sessionStorage.setItem("habit_id", habit_id);
                } else {
                    alert("Failed to save habit.");
                }
            } catch (error) {
                console.error("Habit save error:", error);
                alert("Error connecting to server.");
            }
        }

        window.addEventListener("DOMContentLoaded", async () => {
            if (!habit_id) {
                await promptSetHabit(true);
            }

            attachDayClickHandlers();
        });

        habitTitle.onclick = () => promptSetHabit(false);

        function attachDayClickHandlers() {
            dayDivs.forEach(dayDiv => {
                const dayNum = parseInt(dayDiv.textContent);
                if (isNaN(dayNum) || dayNum > daysInThisMonth) {
                    dayDiv.textContent = "";
                    return;
                }

                if (dayNum === currentDate) {
                    dayDiv.style.border = "2px solid black";
                    dayDiv.style.color = "rgb(234, 1, 144)";
                }

                dayDiv.addEventListener("click", async () => {
                    if (!habit_id) {
                        alert("Please set a habit first.");
                        return;
                    }

                    const isCompleted = dayDiv.style.backgroundColor !== "pink";
                    dayDiv.style.backgroundColor = isCompleted ? "pink" : "white";
                    daysCompleted += isCompleted ? 1 : -1;
                    if (daysCompleted < 0) daysCompleted = 0;

                    totalDays.textContent = `${daysCompleted}/${daysInThisMonth}`;

                    try {
                        await fetch("habit_handler.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `action=save_progress&habit_id=${habit_id}&day=${dayNum}&completed=${isCompleted ? 1 : 0}&month=${currentMonth}&year=${currentYear}`
                        });
                    } catch (error) {
                        console.error("Progress save error:", error);
                    }

                    if (daysCompleted === currentDate) {
                        alert("Great progress!");
                    }
                });
            });

            totalDays.textContent = `${daysCompleted}/${daysInThisMonth}`;
        }

        resetButton.onclick = async () => {
            if (!habit_id) {
                alert("Please set a habit first.");
                return;
            }

            try {
                await fetch("habit_handler.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=reset_progress&habit_id=${habit_id}`
                });

                dayDivs.forEach(div => {
                    if (parseInt(div.textContent)) {
                        div.style.backgroundColor = "white";
                    }
                });

                daysCompleted = 0;
                totalDays.textContent = `${daysCompleted}/${daysInThisMonth}`;
            } catch (error) {
                console.error("Reset error:", error);
            }
        };
    </script>
</body>
</html>
