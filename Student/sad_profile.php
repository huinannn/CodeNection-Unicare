<?php
session_start();
include '../conn.php';


if (!isset($_SESSION['student_id'])) {
    // Not logged in, redirect to login page
    header("Location: Login/login.php");
    exit();
}

$student_id = "";
$student_name = "";
$confessions = [];

if (isset($_SESSION['student_id'])) {
    $id = $_SESSION['student_id'];

    $sql = 'SELECT c.*, s.student_name
            FROM confession c
            JOIN student s ON c.student_id = s.student_id
            WHERE c.mode = "sad" 
              AND c.confession_status = "approved" 
              AND c.student_id = ?
            ORDER BY c.confession_date_time DESC;';
    $stmt = $dbConn->prepare($sql);  
    $stmt->bind_param('s', $id);   
    $stmt->execute();             
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // fetch first row to extract student info
        $firstRow = $result->fetch_assoc();
        $student_id = $firstRow['student_id'];
        $student_name = $firstRow['student_name'];

        // put the first row into confessions array
        $confessions[] = $firstRow;

        // fetch rest of confessions
        while ($row = $result->fetch_assoc()) {
            $confessions[] = $row;
        }
    } else {
        $student_sql = 'SELECT student_name
                FROM student
                WHERE student_id = ?';
        $student_stmt = $dbConn->prepare($student_sql);
        $student_stmt->bind_param('s', $id);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();

        if ($student_result->num_rows > 0) {
            $row = $student_result->fetch_assoc();
            $student_id = $id;
            $student_name = $row['student_name'];
        }

    }
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
        body {
            background-color: #1e293b;
        }

        .img {
            background-color: #F6DCA8;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;              
            justify-content: center;  
            align-items: center; 
        }

        .img img {
            width: 50px !important;
            height: 50px !important;
        }

        .header_left, .header_right {
            margin-top: 50px;
        }

        .header_right {
            column-gap: 10px;
        }

        .vertical {
            margin: 0 10px;
            width: 3px;
            height: 50px;
            background-color: #C96319B3;
        }
        
        .profile p {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .header_right img {
            width: 25px !important;
            height: 25px !important;
            margin-right: 10px;
        }

        .content {
            margin-top: 180px !important;
        }

        .each_content {
            background-color: #221707 !important; 
        }
        .each_content p {
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="confessions" style="background-color: #1e293b;">
        <div class="header" style="background-color: #1e293b;">
            <div class="back" style="background-color: #1e293b;">
                <img src="../image/icons/back_white.png" alt="" onclick="window.location.href='sad_corner.php'">
            </div>
            <div class="header_left">
                <div class="img">
                    <img src="../image/icons/profile.png" alt="">
                </div>
                <div class="vertical"></div>
                <div class="profile">
                    <p><?php echo $student_id ?></p>
                    <p><?php echo $student_name ?></p>
                </div>
            </div>
            <div class="spacer"></div>
            <div class="header_right">
                <img src="../image/icons/add.png" alt="" onclick="window.location.href='sad_add.php';">
            </div>
        </div>
        <div class="content">
            <?php
                if (!empty($confessions)) {
                    foreach ($confessions as $row) {
                        // decode JSON into array
                        $mediaFiles = json_decode($row['confession_post'], true);

                        // make sure it's a valid array
                        $firstFile = "";
                        if (is_array($mediaFiles) && count($mediaFiles) > 0) {
                            $firstFile = $mediaFiles[0]; // first file only
                        }
            ?>
            <div class="each_content" onclick="window.location.href='sad_corner_post.php?id=<?php echo $row['confession_id'] ?>'">
                <?php if (!empty($firstFile)) { ?>
                <img src="../image/confessions/sad/<?php echo htmlspecialchars($firstFile); ?>" alt="">
                <?php } else { ?>
                <p style="text-align:center;color:grey;">No image</p>
                <?php } ?>
                <p><?php echo htmlspecialchars($row['confession_title']); ?></p>
            </div>
            <?php
                    }
                }
            ?>
        </div>
        <div class="end">
            <div class="line"></div>
            <!-- <br> <span onclick="">Refresh!</span> -->
            <p style="color: white;">End</p>
            <div class="line"></div>
        </div>
    </div>
</body>
</html>