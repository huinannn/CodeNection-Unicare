<?php
session_start();
include '../conn.php';


if (!isset($_SESSION['student_id'])) {
    // Not logged in, redirect to login page
    header("Location: Login/login.php");
    exit();
}

if(isset($_SESSION['student_id'])) {
  $id = $_SESSION['student_id'];

  $sql = 'SELECT * FROM confession 
          WHERE mode = "sad" AND confession_status = "approved"
          ORDER BY confession_date_time DESC';
  $result = $dbConn->query($sql);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Unicare</title>
    <link rel="icon" href="../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="style.css" />
    <style>
      .each_content {
        background-color: #221707 !important; 
      }
      .each_content p {
        color: white !important;
      }

      .header_right .img {
          width: 25px !important;
          height: 25px !important;
          margin-right: 10px;
      }
    </style>
</head>
<body style="background-color: #1e293b;">
    <div class="confessions">
      <div class="header" style="background-color: #1e293b">
        <div class="header_left">
          <img src="../image/icons/moon.png" alt="">
          <h1 style="color: white">Sad Corner</h1>
        </div>
        <div class="spacer"></div>
        <div class="header_right">
          <img class="img" src="../image/icons/add.png" alt="" onclick="window.location.href='sad_add.php';">
          <img src="../image/icons/user.png" alt="" onclick="window.location.href='sad_profile.php'">
          <div class="dark_light">
            <input type="checkbox" onchange="window.location.href='happy_zone.php'" checked>
          </div>
        </div>
      </div>
      <div class="content">
        <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
        ?>
        <div class="each_content" onclick="window.location.href='sad_corner_post.php?id=<?php echo $row['confession_id'] ?>'">
          <img src="../image/confessions/sad/<?php echo $row['confession_post'] ?>" alt="">
          <p><?php echo $row['confession_title'] ?></p>
        </div>
        <?php
                }
            }
        ?>
      </div>
      <div class="end">
        <div class="line"></div>
        <!-- <br> <span onclick="" style="color: lightblue;">Refresh!</span> -->
        <p style="color: white;">Nothing new to see </p>
        <div class="line"></div>
      </div>
    </div>
    <?php include 'navigation_dark.php'; ?>
</body>
</html>