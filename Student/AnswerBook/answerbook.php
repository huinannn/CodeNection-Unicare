<?php
    session_start();
    include '../../conn.php';

    if (!isset($_SESSION['student_id'])) {
        header("Location: ../Login/login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Unicare</title>
    <link rel="icon" href="../../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="../style.css"/>
    <link rel="stylesheet" href="answerbook.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
   <div class="header">
        <i class="fa-solid fa-book book-icon"></i>
        <h1>Answer Book</h1>
    </div>

    <div class="answerbook-container">
        <div class="answer-card">
            <div class="center-text">Book Of Answers</div>
            <i class="fa-regular fa-heart icon icon1"></i>
            <i class="fa-regular fa-moon icon icon2"></i>
            <i class="fa-regular fa-star icon icon3"></i>
            <i class="fa-solid fa-percent icon icon4"></i>
            <i class="fa-regular fa-sun icon icon5"></i>
        </div>
        <p>Think of a question in your mind</p>
        <button class="get-answers-btn">Get Answers</button>
    </div>

    <div id="reminder-overlay">
        <div class="reminder-backdrop"></div>
        <div class="reminder-sheet">
            <button class="overlay-close-btn" onclick="window.location.href='../dashboard.php'">×</button>
            <i class="fa-regular fa-message reminder-icon"></i>
            <div class="reminder-title">Gentle Reminder</div>
            <div class="reminder-line"></div>
            <div class="reminder-text">
                This space is for light, safe sharing. If you're going through something heavy, 
                please reach out to someone you trust.<br/><br/>
                You don't have to go through it alone 💙.
            </div>
            <div class="reminder-line"></div>
            <div class="btn-row">
                <a href="../counseling.php" class="btn btn-1">Get Counselling</a>
                <button class="btn btn-2" id="continueBtn">Continue</button>
            </div>
        </div>
    </div>

    <div class="bottom_nav">
        <div class="nav">
            <div class="each_nav" onclick="window.location.href='answerbook.php'">
                <img src="../../image/icons/answers.png" alt="">
                <p>Answers</p>
            </div>

            <div class="each_nav" onclick="window.location.href='../happy_zone.php'">
                <img src="../../image/icons/confessions.png" alt="">
                <p>Confessions</p>
            </div>

            <div class="each_nav" onclick="window.location.href='../dashboard.php'">
                <img src="../../image/icons/home.png" alt="">
                <p>Home</p>
            </div>

            <div class="each_nav" onclick="window.location.href='../leisure.php'">
                <img src="../../image/icons/leisure.png" alt="">
                <p>Leisure</p>
            </div>

            <div class="each_nav" onclick="window.location.href='../counseling.php'">
                <img src="../../image/icons/counseling.png" alt="">
                <p>Counseling</p>
            </div>
        </div>
    </div>
    <script src="answerbook.js"></script>
</body>
</html>