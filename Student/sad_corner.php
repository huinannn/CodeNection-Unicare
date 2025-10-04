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

      .header_right img {
        cursor: pointer;
      }

      .media-wrapper {
          position: relative;
          width: 100%;
          aspect-ratio: 1/1;
          overflow: hidden;
          border-radius: 10px 10px 0 0;
        }

        .media-thumb {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .icon-overlay {
          position: absolute;
          top: 8px;
          right: 8px;
          background: rgba(0,0,0,0.4);
          border-radius: 50%;
          padding: 6px;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .icon-overlay img {
          width: 10px !important;
          height: 10px !important;
          filter: invert(1);
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
                $mediaFiles = json_decode($row['confession_post'], true);
                $firstFile = (is_array($mediaFiles) && count($mediaFiles) > 0) ? $mediaFiles[0] : '';

                // Default values
                $isVideo = false;
                $isAudio = false;
                $filePath = '';
                $fileExtension = '';

                if (!empty($firstFile)) {
                    $filePath = '../image/confessions/sad/' . htmlspecialchars($firstFile);
                    $fileExtension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));

                    // Determine media type
                    $isVideo = in_array($fileExtension, ['mp4', 'mov', 'webm', 'avi', 'mkv', 'gif']);
                    $isAudio = in_array($fileExtension, ['mp3', 'wav', 'ogg', 'm4a']);
                }
        ?>
          <div class="each_content" onclick="window.location.href='sad_corner_post.php?id=<?php echo $row['confession_id'] ?>'">
            <div class="media-wrapper">
              <?php if ($isVideo) { ?>
                <video src="<?php echo $filePath; ?>" muted preload="metadata" class="media-thumb"></video>
                <div class="icon-overlay">
                  <img src="../image/icons/play.png" alt="play icon">
                </div>
              <?php } elseif ($isAudio) { ?>
                <img src="../image/confessions/audio.png" alt="audio placeholder" class="media-thumb">
                <div class="icon-overlay">
                  <img src="../image/icons/audio-play.png" alt="audio icon">
                </div>
              <?php } elseif (!empty($firstFile)) { ?>
                <img src="<?php echo $filePath; ?>" alt="image" class="media-thumb">
              <?php } else { ?>
                <img src="../image/confessions/text.png" alt="">
              <?php } ?>
            </div>
            <p><?php echo htmlspecialchars($row['confession_title']); ?></p>
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