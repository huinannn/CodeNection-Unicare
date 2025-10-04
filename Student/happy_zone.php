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
          WHERE mode = "happy" AND confession_status = "approved"
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
        .header_right .img {
            width: 25px !important;
            height: 25px !important;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="confessions">
      <div class="header">
        <div class="header_left">
          <img src="../image/icons/sun.png" alt="">
          <h1>Happy Zone</h1>
        </div>
        <div class="spacer"></div>
        <div class="header_right">
          <img class="img" src="../image/icons/add.png" alt="" onclick="window.location.href='happy_add.php';">
          <img src="../image/icons/user.png" alt="" onclick="window.location.href='happy_profile.php'">
          <div class="dark_light">
            <input type="checkbox" onchange="goSadCorner(this)">
          </div>
        </div>
      </div>
      <div class="content">
        <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
        ?>
        <div class="each_content" onclick="window.location.href='happy_zone_post.php?id=<?php echo $row['confession_id'] ?>'">
          <img src="../image/confessions/happy/<?php echo $row['confession_post'] ?>" alt="">
          <p><?php echo $row['confession_title'] ?></p>
        </div>
        <?php
                }
            }
        ?>
      </div>
      <div class="end">
        <div class="line"></div>
        <!-- <br> <span onclick="">Refresh!</span> -->
        <p>Nothing new to see</p>
        <div class="line"></div>
      </div>
    </div>
    <!-- Warning for Sad Corner -->
     <div class="overlay" id="overlay">
      <div class="popup">
        <p style="font-size: 15px;">This space is for sharing struggles. It may feel heavy!</p>
        <p style="font-size: 15px;">Are you feeling ready to continue?</p>
        <button class="confirm" id="confirmBtn"><div class="spacer"></div>Yes, I'm ready!</button> 
        <button id="cancelBtn" style="color: black; padding-bottom: 10px;"><div class="spacer"></div>No, I'll stay positive</button>
      </div>
     </div>
    <script>
      const overlay = document.getElementById("overlay");
      const confirmBtn = document.getElementById("confirmBtn");
      const cancelBtn = document.getElementById("cancelBtn");
      function goSadCorner(checkbox) {
        if (checkbox.checked) {
          overlay.style.display = "flex";
        }
      }

      confirmBtn.addEventListener("click", function() {
        window.location.href = "sad_corner.php";
      });

      cancelBtn.addEventListener("click", function() {
        overlay.style.display = "none";
        document.querySelector('input[type="checkbox"]').checked = false;
      })

      document.querySelector('input[type="checkbox"]').checked = false;
    </script>
    <?php include 'navigation.php'; ?>
</body>
</html>