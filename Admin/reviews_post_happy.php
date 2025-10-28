<?php
    session_start();
    include '../conn.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../index.php");
        exit();
    }

    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
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
            overflow: hidden !important;
        }

        a[x-apple-data-detectors],
        a[x-apple-data-detectors] *,
        a[data-detectable="true"],
        a[href^="tel"],
        a[href^="mailto"],
        a[href^="date"] {
            color: inherit !important;
            text-decoration: none !important;
            cursor: default !important;
        }

        .slider {
            position: relative;
        }

        .slider input {
            display: none;
        }

        .slides .img {
            width: 100%;
            aspect-ratio: 100/110;
            object-fit: cover;
        }

        .slides > .media-wrapper {
            display: none;
        }

        .slider input[type="radio"]:checked:nth-of-type(1) ~ .slides .media-wrapper:nth-child(1),
        .slider input[type="radio"]:checked:nth-of-type(2) ~ .slides .media-wrapper:nth-child(2),
        .slider input[type="radio"]:checked:nth-of-type(3) ~ .slides .media-wrapper:nth-child(3),
        .slider input[type="radio"]:checked:nth-of-type(4) ~ .slides .media-wrapper:nth-child(4),
        .slider input[type="radio"]:checked:nth-of-type(5) ~ .slides .media-wrapper:nth-child(5),
        .slider input[type="radio"]:checked:nth-of-type(6) ~ .slides .media-wrapper:nth-child(6)
        {
            display: block;
        }

        .slider .arrows {
            position: absolute;
            top: 60px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none; /* so arrows don’t block clicks */
        }

        .slider .arrows button {
            pointer-events: auto;
            background: rgba(0,0,0,0.3);
            border: none;
            color: white;
            font-size: 24px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
        }

        .dots {
            display: flex;
            justify-content: center;
            margin: 8px 0;
        }

        .dots label {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            background-color: #aaaaaaff;
            cursor: pointer;
            margin: 0 5px;
            transition: background 0.2s ease;
        }

        .dots label:hover {
            background-color: #f6dca8;
        }

        /* Active dot */
        .slider input[type="radio"]:checked:nth-of-type(1) ~ .dots label:nth-of-type(1),
        .slider input[type="radio"]:checked:nth-of-type(2) ~ .dots label:nth-of-type(2),
        .slider input[type="radio"]:checked:nth-of-type(3) ~ .dots label:nth-of-type(3),
        .slider input[type="radio"]:checked:nth-of-type(4) ~ .dots label:nth-of-type(4),
        .slider input[type="radio"]:checked:nth-of-type(5) ~ .dots label:nth-of-type(5),
        .slider input[type="radio"]:checked:nth-of-type(6) ~ .dots label:nth-of-type(6)
        {
            background-color: #f6dca8;
        }

        .media-wrapper {
            position: relative;
            width: 30%;
        }

        .media-wrapper img,
        .media-wrapper video {
            width: 100px;
            aspect-ratio: 100/110;
            border-radius: 10px;
            object-fit: contain;
        }

        .audio-img {
            width: 100%;
            aspect-ratio: 365/400;
            object-fit: cover;
            border-radius: 10px;
            background-color: #f0f0f0;
        }

        .audio-wrapper {
            position: relative;
            display: inline-block;
        }

        .audio-player {
            width: 100%;
            border-radius: 8px;
        }

        .each_sort {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
        }

        .each_sort .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99;
        }

        .each_data p {
            font-size: 12px;
            font-weight: 500;
        }

        .no_data p {
            font-family: 'Itim', cursive;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <?php include 'navigation.php' ?>
    <div class="page">
        <div class="head">
            <div class="title">
                <h1>Reviews - Happy Zone</h1>
            </div>
            <div class="subtitle">
                <h1 class="active">Posts</h1>
                <div class="vertical"></div>
                <h1 onclick="window.location.href='reviews_comment_happy.php'">Comments</h1>
                <div class="vertical"></div>
                <h1 onclick="window.location.href='reviews_reply_happy.php'">Replies</h1>
                <div class="spacer"></div>
                <div class="dark_light">
                    <input type="checkbox" onchange="window.location.href='reviews_post_sad.php'">
                </div>
            </div>
        </div>
        <div class="sort">
            <div class="each_sort active" data-filter="all"><p>All</p></div>
            <div class="each_sort" data-filter="pending"><p>Pending</p><span class="badge" id="pendingCount"></span></div>
            <div class="each_sort" data-filter="approved"><p>Approved</p></div>
            <div class="each_sort" data-filter="rejected"><p>Rejected</p></div>
        </div>
        <div class="table">
            <div class="table_header">
                <h2>Post Content</h2>
                <h2>Time Stamp</h2>
                <h2>Action</h2>
            </div>
            <div class="table_data" data-status="all">
                <?php
                    $all_sql = "SELECT c.*, s.student_name
                                FROM confession c
                                JOIN student s 
                                ON c.student_id = s.student_id
                                JOIN school sc 
                                ON s.school_id = sc.school_id
                                JOIN admin a 
                                ON a.school_id = sc.school_id
                                WHERE c.mode = 'happy' AND a.admin_id = ?
                                ORDER BY c.confession_date_time DESC";
                    $all_stmt = $dbConn->prepare($all_sql);
                    $all_stmt->bind_param('s', $admin_id);
                    $all_stmt->execute();
                    $all_result = $all_stmt->get_result();

                    if ($all_result->num_rows > 0) {
                        while ($all_row = $all_result->fetch_assoc()) {
                ?>
                <div class="horizontal"></div>
                <div class="each_data" data-id="<?php echo $all_row['confession_id']; ?>" data-time="<?php echo $all_row['confession_date_time']; ?>">
                    <div class="content">
                        <div class="each_content">
                            <?php
                                $raw = trim($all_row['confession_post'] ?? '');
                                $files = [];

                                // 🔹 Case 1: JSON array format
                                if (str_starts_with($raw, '[')) {
                                    $decoded = json_decode($raw, true);
                                    if (is_array($decoded)) {
                                        $files = array_map('trim', $decoded);
                                    }
                                }
                                // 🔹 Case 2: Single string
                                elseif (!empty($raw)) {
                                    $files = [trim($raw)];
                                }

                                // Remove empty entries
                                $files = array_filter($files);
                                $count = count($files);
                                ?>

                                <?php if ($count > 0): ?>

                                    <?php if ($count > 1): ?>
                                        <div class="slider" id="slider-<?php echo $all_row['confession_id']; ?>">
                                            <?php for ($i = 1; $i <= $count; $i++): ?>
                                                <input type="radio" name="slide-<?php echo $all_row['confession_id']; ?>" id="img<?php echo $i; ?>-<?php echo $all_row['confession_id']; ?>" <?php if($i===1) echo 'checked'; ?>>
                                            <?php endfor; ?>

                                            <div class="slides">
                                                <?php foreach ($files as $file): 
                                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                    $mediaPath = "../image/confessions/happy/" . $file;
                                                ?>
                                                <div class="media-wrapper">
                                                    <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                        <img src="<?php echo $mediaPath; ?>" alt="Image">
                                                    <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                        <video controls playsinline webkit-playsinline muted>
                                                            <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                        <div class="audio-wrapper">
                                                            <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                            <audio controls class="audio-player">
                                                                <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                                Your browser does not support the audio tag.
                                                            </audio>
                                                        </div>
                                                    <?php else: ?>
                                                        <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="arrows">
                                                <button class="prev">&#10094;</button>
                                                <button class="next">&#10095;</button>
                                            </div>
                                            <div class="dots">
                                                <?php for ($i = 1; $i <= $count; $i++): ?>
                                                    <label for="img<?php echo $i; ?>-<?php echo $all_row['confession_id']; ?>"></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                            $file = $files[0];
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $mediaPath = "../image/confessions/happy/" . $file;
                                        ?>
                                        <div class="media-wrapper">
                                            <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?php echo $mediaPath; ?>" alt="Image">
                                            <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                <video controls playsinline webkit-playsinline muted>
                                                    <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                <div class="audio-wrapper">
                                                    <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                        Your browser does not support the audio tag.
                                                    </audio>
                                                </div>
                                            <?php else: ?>
                                                <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                            <?php else: ?>
                                <img src="../image/confessions/text.png" alt="">
                            <?php endif; ?>
                            <div class="description">
                                <h1><?php echo $all_row['confession_title'] ?></h1>
                                <p><?php echo $all_row['confession_message'] ?></p>
                            </div>
                        </div>
                        <div class="each_content">
                            <?php
                                $all_unformat_date = $all_row['confession_date_time'];
                                $all_format_date = date("Y-n-j hi A", strtotime($all_unformat_date));
                            ?>
                            <h3><?php echo $all_format_date ?></h3>
                        </div>
                        <?php
                            if ($all_row['confession_status'] === "pending") {
                        ?>
                        <div class="each_content button">
                            <button class="btn approve-btn" data-type="confession" data-id="<?php echo $all_row['confession_id']; ?>">Approve</button>
                            <div style="height:20px;"></div>
                            <button class="btn reject-btn" data-type="confession" data-id="<?php echo $all_row['confession_id']; ?>" id="reject">Reject</button>
                        </div>
                        <?php
                            } elseif ($all_row['confession_status'] === "approved") {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Approved</button>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Rejected</button>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                    <p>Post By: <?php echo $all_row['student_name'] ?></p>
                </div>
                <?php
                        }
                    }
                ?>
            </div>
            <div class="table_data" data-status="pending" style="display: none;">
                <?php
                    $pending_sql = "SELECT c.*, s.student_name
                                FROM confession c
                                JOIN student s 
                                ON c.student_id = s.student_id
                                JOIN school sc 
                                ON s.school_id = sc.school_id
                                JOIN admin a 
                                ON a.school_id = sc.school_id
                                WHERE c.mode = 'happy' AND c.confession_status = 'pending' AND a.admin_id = ?
                                ORDER BY c.confession_date_time DESC";
                    $pending_stmt = $dbConn->prepare($pending_sql);
                    $pending_stmt->bind_param('s', $admin_id);
                    $pending_stmt->execute();
                    $pending_result = $pending_stmt->get_result();

                    if ($pending_result->num_rows > 0) {
                        while ($pending_row = $pending_result->fetch_assoc()) {
                ?>
                <div class="horizontal"></div>
                <div class="each_data" data-id="<?php echo $pending_row['confession_id']; ?>" data-time="<?php echo $pending_row['confession_date_time']; ?>">
                    <div class="content">
                        <div class="each_content">
                            <?php
                                $raw = trim($pending_row['confession_post'] ?? '');
                                $files = [];

                                // 🔹 Case 1: JSON array format
                                if (str_starts_with($raw, '[')) {
                                    $decoded = json_decode($raw, true);
                                    if (is_array($decoded)) {
                                        $files = array_map('trim', $decoded);
                                    }
                                }
                                // 🔹 Case 2: Single string
                                elseif (!empty($raw)) {
                                    $files = [trim($raw)];
                                }

                                // Remove empty entries
                                $files = array_filter($files);
                                $count = count($files);
                                ?>

                                <?php if ($count > 0): ?>

                                    <?php if ($count > 1): ?>
                                        <div class="slider" id="slider-<?php echo $pending_row['confession_id']; ?>">
                                            <?php for ($i = 1; $i <= $count; $i++): ?>
                                                <input type="radio" name="slide-<?php echo $pending_row['confession_id']; ?>" id="img<?php echo $i; ?>-<?php echo $pending_row['confession_id']; ?>" <?php if($i===1) echo 'checked'; ?>>
                                            <?php endfor; ?>

                                            <div class="slides">
                                                <?php foreach ($files as $file): 
                                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                    $mediaPath = "../image/confessions/happy/" . $file;
                                                ?>
                                                <div class="media-wrapper">
                                                    <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                        <img src="<?php echo $mediaPath; ?>" alt="Image">
                                                    <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                        <video controls playsinline webkit-playsinline muted>
                                                            <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                        <div class="audio-wrapper">
                                                            <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                            <audio controls class="audio-player">
                                                                <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                                Your browser does not support the audio tag.
                                                            </audio>
                                                        </div>
                                                    <?php else: ?>
                                                        <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="arrows">
                                                <button class="prev">&#10094;</button>
                                                <button class="next">&#10095;</button>
                                            </div>
                                            <div class="dots">
                                                <?php for ($i = 1; $i <= $count; $i++): ?>
                                                    <label for="img<?php echo $i; ?>-<?php echo $pending_row['confession_id']; ?>"></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                            $file = $files[0];
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $mediaPath = "../image/confessions/happy/" . $file;
                                        ?>
                                        <div class="media-wrapper">
                                            <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?php echo $mediaPath; ?>" alt="Image">
                                            <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                <video controls playsinline webkit-playsinline muted>
                                                    <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                <div class="audio-wrapper">
                                                    <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                        Your browser does not support the audio tag.
                                                    </audio>
                                                </div>
                                            <?php else: ?>
                                                <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                            <?php else: ?>
                                <img src="../image/confessions/text.png" alt="">
                            <?php endif; ?>
                            <div class="description">
                                <h1><?php echo $pending_row['confession_title'] ?></h1>
                                <p><?php echo $pending_row['confession_message'] ?></p>
                            </div>
                        </div>
                        <div class="each_content">
                            <?php
                                $pending_unformat_date = $pending_row['confession_date_time'];
                                $pending_format_date = date("Y-n-j hi A", strtotime($pending_unformat_date));
                            ?>
                            <h3><?php echo $pending_format_date ?></h3>
                        </div>
                        <?php
                            if ($pending_row['confession_status'] === "pending") {
                        ?>
                        <div class="each_content button">
                            <button class="btn approve-btn" data-type="confession" data-id="<?php echo $pending_row['confession_id']; ?>">Approve</button>
                            <div style="height:20px;"></div>
                            <button class="btn reject-btn" data-type="confession" data-id="<?php echo $pending_row['confession_id']; ?>" id="reject">Reject</button>
                        </div>
                        <?php
                            } elseif ($pending_row['confession_status'] === "approved") {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Approved</button>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Rejected</button>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                    <p>Post By: <?php echo $pending_row['student_name'] ?></p>
                </div>
                <?php
                        }
                    } else {
                ?>
                <div class="horizontal"></div>
                <div class="no_data">
                    <p>No Pending Posts Yet!</p>
                </div>
                <?php
                    }
                ?>
            </div>
            <div class="table_data" data-status="approved" style="display: none;">
                <?php
                    $approved_sql = "SELECT c.*, s.student_name
                                FROM confession c
                                JOIN student s 
                                ON c.student_id = s.student_id
                                JOIN school sc 
                                ON s.school_id = sc.school_id
                                JOIN admin a 
                                ON a.school_id = sc.school_id
                                WHERE c.mode = 'happy' AND c.confession_status = 'approved' AND a.admin_id = ?
                                ORDER BY c.confession_date_time DESC";
                    $approved_stmt = $dbConn->prepare($approved_sql);
                    $approved_stmt->bind_param('s', $admin_id);
                    $approved_stmt->execute();
                    $approved_result = $approved_stmt->get_result();

                    if ($approved_result->num_rows > 0) {
                        while ($approved_row = $approved_result->fetch_assoc()) {
                ?>
                <div class="horizontal"></div>
                <div class="each_data" data-id="<?php echo $approved_row['confession_id']; ?>" data-time="<?php echo $approved_row['confession_date_time']; ?>">
                    <div class="content">
                        <div class="each_content">
                            <?php
                                $raw = trim($approved_row['confession_post'] ?? '');
                                $files = [];

                                // 🔹 Case 1: JSON array format
                                if (str_starts_with($raw, '[')) {
                                    $decoded = json_decode($raw, true);
                                    if (is_array($decoded)) {
                                        $files = array_map('trim', $decoded);
                                    }
                                }
                                // 🔹 Case 2: Single string
                                elseif (!empty($raw)) {
                                    $files = [trim($raw)];
                                }

                                // Remove empty entries
                                $files = array_filter($files);
                                $count = count($files);
                                ?>

                                <?php if ($count > 0): ?>

                                    <?php if ($count > 1): ?>
                                        <div class="slider" id="slider-<?php echo $approved_row['confession_id']; ?>">
                                            <?php for ($i = 1; $i <= $count; $i++): ?>
                                                <input type="radio" name="slide-<?php echo $approved_row['confession_id']; ?>" id="img<?php echo $i; ?>-<?php echo $approved_row['confession_id']; ?>" <?php if($i===1) echo 'checked'; ?>>
                                            <?php endfor; ?>

                                            <div class="slides">
                                                <?php foreach ($files as $file): 
                                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                    $mediaPath = "../image/confessions/happy/" . $file;
                                                ?>
                                                <div class="media-wrapper">
                                                    <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                        <img src="<?php echo $mediaPath; ?>" alt="Image">
                                                    <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                        <video controls playsinline webkit-playsinline muted>
                                                            <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                        <div class="audio-wrapper">
                                                            <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                            <audio controls class="audio-player">
                                                                <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                                Your browser does not support the audio tag.
                                                            </audio>
                                                        </div>
                                                    <?php else: ?>
                                                        <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="arrows">
                                                <button class="prev">&#10094;</button>
                                                <button class="next">&#10095;</button>
                                            </div>
                                            <div class="dots">
                                                <?php for ($i = 1; $i <= $count; $i++): ?>
                                                    <label for="img<?php echo $i; ?>-<?php echo $approved_row['confession_id']; ?>"></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                            $file = $files[0];
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $mediaPath = "../image/confessions/happy/" . $file;
                                        ?>
                                        <div class="media-wrapper">
                                            <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?php echo $mediaPath; ?>" alt="Image">
                                            <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                <video controls playsinline webkit-playsinline muted>
                                                    <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                <div class="audio-wrapper">
                                                    <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                        Your browser does not support the audio tag.
                                                    </audio>
                                                </div>
                                            <?php else: ?>
                                                <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                            <?php else: ?>
                                <img src="../image/confessions/text.png" alt="">
                            <?php endif; ?>
                            <div class="description">
                                <h1><?php echo $approved_row['confession_title'] ?></h1>
                                <p><?php echo $approved_row['confession_message'] ?></p>
                            </div>
                        </div>
                        <div class="each_content">
                            <?php
                                $approved_unformat_date = $approved_row['confession_date_time'];
                                $approved_format_date = date("Y-n-j hi A", strtotime($approved_unformat_date));
                            ?>
                            <h3><?php echo $approved_format_date ?></h3>
                        </div>
                        <?php
                            if ($approved_row['confession_status'] === "pending") {
                        ?>
                        <div class="each_content button">
                            <button class="btn approve-btn" data-type="confession" data-id="<?php echo $approved_row['confession_id']; ?>">Approve</button>
                            <div style="height:20px;"></div>
                            <button class="btn reject-btn" data-type="confession" data-id="<?php echo $approved_row['confession_id']; ?>" id="reject">Reject</button>
                        </div>
                        <?php
                            } elseif ($approved_row['confession_status'] === "approved") {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Approved</button>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Rejected</button>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                    <p>Post By: <?php echo $approved_row['student_name'] ?></p>
                </div>
                <?php
                        }
                    } else {
                ?>
                <div class="horizontal"></div>
                <div class="no_data">
                    <p>No Approved Posts Yet!</p>
                </div>
                <?php
                    }
                ?>
            </div>
            <div class="table_data" data-status="rejected" style="display: none;">
                <?php
                    $rejected_sql = "SELECT c.*, s.student_name
                                FROM confession c
                                JOIN student s 
                                ON c.student_id = s.student_id
                                JOIN school sc 
                                ON s.school_id = sc.school_id
                                JOIN admin a 
                                ON a.school_id = sc.school_id
                                WHERE c.mode = 'happy' AND c.confession_status = 'rejected' AND a.admin_id = ?
                                ORDER BY c.confession_date_time DESC";
                    $rejected_stmt = $dbConn->prepare($rejected_sql);
                    $rejected_stmt->bind_param('s', $admin_id);
                    $rejected_stmt->execute();
                    $rejected_result = $rejected_stmt->get_result();

                    if ($rejected_result->num_rows > 0) {
                        while ($rejected_row = $rejected_result->fetch_assoc()) {
                ?>
                <div class="horizontal"></div>
                <div class="each_data" data-id="<?php echo $rejected_row['confession_id']; ?>" data-time="<?php echo $rejected_row['confession_date_time']; ?>">
                    <div class="content">
                        <div class="each_content">
                            <?php
                                $raw = trim($rejected_row['confession_post'] ?? '');
                                $files = [];

                                // 🔹 Case 1: JSON array format
                                if (str_starts_with($raw, '[')) {
                                    $decoded = json_decode($raw, true);
                                    if (is_array($decoded)) {
                                        $files = array_map('trim', $decoded);
                                    }
                                }
                                // 🔹 Case 2: Single string
                                elseif (!empty($raw)) {
                                    $files = [trim($raw)];
                                }

                                // Remove empty entries
                                $files = array_filter($files);
                                $count = count($files);
                                ?>

                                <?php if ($count > 0): ?>

                                    <?php if ($count > 1): ?>
                                        <div class="slider" id="slider-<?php echo $rejected_row['confession_id']; ?>">
                                            <?php for ($i = 1; $i <= $count; $i++): ?>
                                                <input type="radio" name="slide-<?php echo $rejected_row['confession_id']; ?>" id="img<?php echo $i; ?>-<?php echo $rejected_row['confession_id']; ?>" <?php if($i===1) echo 'checked'; ?>>
                                            <?php endfor; ?>

                                            <div class="slides">
                                                <?php foreach ($files as $file): 
                                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                    $mediaPath = "../image/confessions/happy/" . $file;
                                                ?>
                                                <div class="media-wrapper">
                                                    <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                        <img src="<?php echo $mediaPath; ?>" alt="Image">
                                                    <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                        <video controls playsinline webkit-playsinline muted>
                                                            <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                        <div class="audio-wrapper">
                                                            <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                            <audio controls class="audio-player">
                                                                <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                                Your browser does not support the audio tag.
                                                            </audio>
                                                        </div>
                                                    <?php else: ?>
                                                        <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="arrows">
                                                <button class="prev">&#10094;</button>
                                                <button class="next">&#10095;</button>
                                            </div>
                                            <div class="dots">
                                                <?php for ($i = 1; $i <= $count; $i++): ?>
                                                    <label for="img<?php echo $i; ?>-<?php echo $rejected_row['confession_id']; ?>"></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                            $file = $files[0];
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $mediaPath = "../image/confessions/happy/" . $file;
                                        ?>
                                        <div class="media-wrapper">
                                            <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?php echo $mediaPath; ?>" alt="Image">
                                            <?php elseif (in_array($ext, ['mp4','mov'])): ?>
                                                <video controls playsinline webkit-playsinline muted>
                                                    <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (in_array($ext, ['mp3','m4a'])): ?>
                                                <div class="audio-wrapper">
                                                    <img src="../image/confessions/audio.png" alt="Audio" class="audio-img">
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $mediaPath; ?>" type="<?php echo $ext==='m4a'?'audio/mp4':'audio/mpeg'; ?>">
                                                        Your browser does not support the audio tag.
                                                    </audio>
                                                </div>
                                            <?php else: ?>
                                                <p>Unsupported file format: <?php echo htmlspecialchars($ext); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                            <?php else: ?>
                                <img src="../image/confessions/text.png" alt="">
                            <?php endif; ?>
                            <div class="description">
                                <h1><?php echo $rejected_row['confession_title'] ?></h1>
                                <p><?php echo $rejected_row['confession_message'] ?></p>
                            </div>
                        </div>
                        <div class="each_content">
                            <?php
                                $rejected_unformat_date = $rejected_row['confession_date_time'];
                                $rejected_format_date = date("Y-n-j hi A", strtotime($rejected_unformat_date));
                            ?>
                            <h3><?php echo $rejected_format_date ?></h3>
                        </div>
                        <?php
                            if ($rejected_row['confession_status'] === "pending") {
                        ?>
                        <div class="each_content button">
                            <button class="btn approve-btn" data-type="confession" data-id="<?php echo $rejected_row['confession_id']; ?>">Approve</button>
                            <div style="height:20px;"></div>
                            <button class="btn reject-btn" data-type="confession" data-id="<?php echo $rejected_row['confession_id']; ?>" id="reject">Reject</button>
                        </div>
                        <?php
                            } elseif ($rejected_row['confession_status'] === "approved") {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Approved</button>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="each_content button">
                            <button class="btn done">Rejected</button>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                    <p>Post By: <?php echo $rejected_row['student_name'] ?></p>
                </div>
                <?php
                        }
                    } else {
                ?>
                <div class="horizontal"></div>
                <div class="no_data">
                    <p>No Rejected Posts Yet!</p>
                </div>
                <?php
                    }
                ?>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.each_sort').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.each_sort').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                let filter = this.getAttribute('data-filter');
                document.querySelectorAll('.table_data').forEach(section => {
                    section.style.display = (filter === 'all' && section.getAttribute('data-status') === 'all') 
                        || section.getAttribute('data-status') === filter
                        ? 'block' : 'none';
                });
            });
        });

        function updatePendingCount() {
            let count = document.querySelectorAll('.table_data[data-status="pending"] .each_data').length;
            let badge = document.getElementById('pendingCount');
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'flex';
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        }

        function updateNoData(section) {
            if (section.querySelectorAll('.each_data').length === 0) {
                if (!section.querySelector('.no_data')) {
                    const statusLabel = section.getAttribute('data-status') || 'items';
                    const labelCapitalized = statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1);
                    const noDiv = document.createElement('div');
                    noDiv.className = 'no_data';
                    noDiv.innerHTML = `<p>No ${labelCapitalized} Posts Yet!</p>`;
                    section.appendChild(noDiv);
                }
            }
        }

        function sortSection(section) {
            let items = [...section.querySelectorAll('.each_data')];

            // Sort newest first
            items.sort((a, b) => {
                let timeA = new Date(a.dataset.time);
                let timeB = new Date(b.dataset.time);
                return timeB - timeA;
            });

            // Remove existing items + dividers
            section.querySelectorAll('.each_data, .horizontal').forEach(el => el.remove());

            // Re-append with dividers
            items.forEach((item, i) => {
                if (i > 0) {
                    const divider = document.createElement('div');
                    divider.className = 'horizontal';
                    section.appendChild(divider);
                }
                section.appendChild(item);
            });
        }


        // 🔥 New: update UI in all tabs at once
        function updateConfessionUI(id, newStatus) {
            const statusLower = newStatus.toLowerCase();

            ['all','pending','approved','rejected'].forEach(tab => {
                const section = document.querySelector(`.table_data[data-status="${tab}"]`);
                if (!section) return;

                // find confession in this section
                const dataBlock = [...section.querySelectorAll('.each_data')].find(el => el.dataset.id == id);

                if (tab === 'pending' && dataBlock) {
                    // remove from pending
                    let hr = dataBlock.previousElementSibling;
                    if (hr && hr.classList.contains('horizontal')) hr.remove();
                    dataBlock.remove();
                    updateNoData(section);

                } else if (tab === 'all' && dataBlock) {
                    // just update button in All tab
                    const actionContainer = dataBlock.querySelector('.each_content.button');
                    if (actionContainer) {
                        actionContainer.innerHTML = `<button class="btn done">${newStatus}</button>`;
                    }

                } else if ((tab === 'approved' && statusLower === 'approved') ||
                        (tab === 'rejected' && statusLower === 'rejected')) {

                    if (dataBlock) {
                        // Case 1: Already exists → update its button
                        const actionContainer = dataBlock.querySelector('.each_content.button');
                        if (actionContainer) {
                            actionContainer.innerHTML = `<button class="btn done">${newStatus}</button>`;
                        }
                    } else {
                        // Case 2: Not there → clone from All tab
                        const allTab = document.querySelector('.table_data[data-status="all"]');
                        const original = allTab.querySelector(`.each_data[data-id="${id}"]`);
                        if (original) {
                            const clone = original.cloneNode(true);

                            // update button
                            const actionContainer = clone.querySelector('.each_content.button');
                            if (actionContainer) {
                                actionContainer.innerHTML = `<button class="btn done">${newStatus}</button>`;
                            }

                            // remove no_data if exists
                            const noData = section.querySelector('.no_data');
                            if (noData) noData.remove();

                            // add divider then append
                            const divider = document.createElement('div');
                            divider.className = 'horizontal';
                            section.appendChild(divider);
                            section.appendChild(clone);
                            sortSection(section);
                        }
                    }
                }

            });

            updatePendingCount();
        }


        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                if (e.target.classList.contains('approve-btn') || e.target.classList.contains('reject-btn')) {
                    const id = e.target.dataset.id;
                    const status = e.target.classList.contains('approve-btn') ? 'Approved' : 'Rejected';
                    const type = e.target.dataset.type;

                    fetch('update_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}&status=${encodeURIComponent(status.toLowerCase())}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateConfessionUI(id, status);
                        } else {
                            alert(data.message || 'Error updating status');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('AJAX request failed');
                    });
                }
            });

            function initSlider(slider) {
                const slides = slider.querySelectorAll(".media-wrapper");
                const dots = slider.querySelectorAll(".dots label");
                const prevBtn = slider.querySelector(".prev");
                const nextBtn = slider.querySelector(".next");

                if (slides.length === 0) return;

                let currentIndex = 0;

                function showSlide(index) {
                    currentIndex = index;
                    slides.forEach((slide, i) => slide.style.display = i === index ? "block" : "none");
                    dots.forEach((dot, i) => dot.style.backgroundColor = i === index ? "#f6dca8" : "#aaaaaaff");
                }

                showSlide(0);

                // Dots click
                dots.forEach((dot, i) => {
                    dot.addEventListener("click", () => showSlide(i));
                });

                // Arrow clicks
                if (prevBtn) prevBtn.addEventListener("click", () => {
                    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                    showSlide(currentIndex);
                });

                if (nextBtn) nextBtn.addEventListener("click", () => {
                    currentIndex = (currentIndex + 1) % slides.length;
                    showSlide(currentIndex);
                });
            }

            document.querySelectorAll(".slider").forEach(initSlider);

            // Tabs logic
            const tabButtons = document.querySelectorAll(".each_sort");
            tabButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const status = button.dataset.filter;
                    document.querySelectorAll(".table_data").forEach(td => {
                        td.style.display = td.dataset.status === status ? "block" : "none";
                    });
                    document.querySelectorAll(`.table_data[data-status="${status}"] .slider`).forEach(initSlider);
                    tabButtons.forEach(b => b.classList.remove("active"));
                    button.classList.add("active");
                });
            });

            updatePendingCount();
        });

    </script>
</body>
</html>