<?php
session_start();
include '../conn.php';


if (!isset($_SESSION['student_id'])) {
    // Not logged in, redirect to login page
    header("Location: Login/login.php");
    exit();
}

if(isset($_SESSION['student_id']) && $_GET['id']) {
    $id = $_SESSION['student_id'];
    $confession = $_GET['id'];

    $sql = 'SELECT * FROM confession 
            WHERE confession_id = ?';
    $stmt = $dbConn->prepare($sql);  
    $stmt->bind_param('s', $confession);   
    $stmt->execute();             
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mediaFiles = json_decode($row['confession_post'], true);
        $title = $row['confession_title'];
        $msg = $row['confession_message'];
        $unformatted_date = $row['confession_date_time'];
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>Unicare</title>
    <link rel="icon" href="../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="style.css" />
    <style>
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

        html, body {
            max-width: var(--max-width) !important;
            width: 100% !important;
            overflow-x: hidden;
        }

        .slider {
            position: relative;
            max-width: 365px;
            max-height: 419px;
            width: 100%;
            aspect-ratio: 365/419;
        }

        .slider input {
            display: none;
        }

        .slides .img {
            width: 100%;
            height: 90%;
            aspect-ratio: 365/400;
            object-fit: cover;
        }

        .slides > .media-wrapper {
            display: none;
        }

        #img1:checked ~ .slides > .m1 { display: block; }
        #img2:checked ~ .slides > .m2 { display: block; }
        #img3:checked ~ .slides > .m3 { display: block; }
        #img4:checked ~ .slides > .m4 { display: block; }

        .dots {
            display: flex;
            justify-content: center;
            margin: 8px 0;
        }

        .dots label {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            background-color: #e9e9e9;
            cursor: pointer;
            margin: 0 5px;
            transition: background 0.2s ease;
        }

        .dots label:hover {
            background-color: #f6dca8;
        }

        /* Active dot */
        #img1:checked ~ .dots label[for="img1"],
        #img2:checked ~ .dots label[for="img2"],
        #img3:checked ~ .dots label[for="img3"],
        #img4:checked ~ .dots label[for="img4"] {
            background-color: #f6dca8;
        }

        .media-wrapper {
            position: relative;
            width: 100%;
        }

        .media-wrapper img,
        .media-wrapper video {
            width: 100%;
            aspect-ratio: 365/400;
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
            position: absolute;
            bottom: 10px;
            left: 10px;
            width: 60%;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
        }

        .comment {
            padding-bottom: 70px;
            max-height: calc(100vh - 120px);
        }

        #addCommentBox {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            padding: 10px;
            z-index: 999;
            box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
        }

        #commentForm {
            display: flex;
            padding: 0 10px;
        }

        #addCommentBox textarea {
            width: 95%;
            resize: none;
            border-radius: 8px;
            padding: 8px;
            font-family: var(--itim);
            font-size: 16px;
            outline: none;
            border: 1px solid #ccc;
            height: 20px;
        }

        #addCommentBox button {
            border: none;
            cursor: pointer;
            background-color: transparent;
        }

        #addCommentBox button img {
            width: 35px;
            height: 35px;
        }

        .addReplyBox {
            padding: 4px 10px;
        }

        .addReplyBox textarea {
            width: 90%;
            resize: none;
            border-radius: 6px;
            padding: 6px;
            font-size: 16px;
            font-family: var(--itim);
            outline: none;
            border: none;
        }

        .addReplyBox button {
            margin-top: 4px;
            padding: 5px 10px;
            font-size: 11px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .addReplyBox button:hover {
            background-color: #b07c5c;
        }

        /* Smooth toggle animations */
        #addCommentBox, .addReplyBox, .reply {
            transition: all 0.25s ease-in-out;
        }

        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 12px;
            position: fixed;
            z-index: 9999;
            left: 50%;
            bottom: 30px;
            font-size: 14px;
            opacity: 0;
            transform: translateX(-50%);
            transition: opacity 0.4s, bottom 0.4s;
        }

        /* Show toast */
        #toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }
    </style>
</head>
<body>
    <div class="confessions">
        <div class="back">
            <img src="../image/icons/back.png" alt="" onclick="window.location.href='happy_zone.php'">
        </div>
        <?php if (!empty($mediaFiles) && is_array($mediaFiles)) { ?>
        <div class="media">
                <?php if (count($mediaFiles) === 1) { ?>
                    <?php 
                        $file = $mediaFiles[0];
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $isVideo = in_array($extension, ['mp4', 'mov','MP4','MOV']);
                        $isAudio = in_array($extension, ['mp3', 'm4a','MP3','M4A']);
                        $isGif   = $extension === 'gif';
                    ?>
                    <div class="media-wrapper">
                        <?php if ($isVideo) { ?>
                            <?php $videoMimeType = ($extension === 'mov' || $extension === 'MOV') ? 'video/mp4' : 'video/mp4'; ?>
                            <video controls playsinline webkit-playsinline preload="metadata" muted>
                                <source src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" type="<?php echo $videoMimeType; ?>">
                                Your browser does not support this video.
                            </video>
                        <?php } elseif ($isAudio) { ?>
                            <?php $audioMimeType = ($extension === 'm4a' || $extension === 'M4A') ? 'audio/mp4' : 'audio/mpeg';?>
                            <img src="../image/confessions/audio.png" alt="audio" class="audio-img">
                            <audio controls class="audio-player">
                                <source src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" type="<?php echo $audioMimeType ?>">
                                Your browser does not support the audio element.
                            </audio>
                        <?php } else { ?>
                            <img class="img" src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" alt="media">
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <!-- Multiple files (slider) -->
                    <div class="slider">
                        <?php foreach ($mediaFiles as $index => $file) { ?>
                            <input type="radio" name="slide" id="img<?php echo $index+1; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                        <?php } ?>

                        <div class="slides">
                            <?php foreach ($mediaFiles as $index => $file) { 
                                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $isVideo = in_array($extension, ['mp4', 'mov','MP4','MOV']);
                                $isAudio = in_array($extension, ['mp3', 'm4a','MP3','M4A']);
                                $isGif   = $extension === 'gif';
                            ?>
                            <div class="media-wrapper m<?php echo $index+1; ?>">
                                <?php if ($isVideo) { ?>
                                    <?php $videoMimeType = ($extension === 'mov'|| $extension === 'MOV') ? 'video/mp4' : 'video/mp4'; ?>
                                    <video controls playsinline webkit-playsinline preload="metadata" muted>
                                        <source src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" type="<?php echo $videoMimeType; ?>">
                                        Your browser does not support this video.
                                    </video>
                                <?php } elseif ($isAudio) { ?>
                                    <?php $audioMimeType = ($extension === 'm4a' || $extension === 'M4A') ? 'audio/mp4' : 'audio/mpeg';?>
                                    <img src="../image/confessions/audio.png" alt="audio" class="audio-img">
                                    <audio controls class="audio-player">
                                        <source src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" type="<?php echo $audioMimeType ?>">
                                        Your browser does not support the audio element.
                                    </audio>
                                <?php } else { ?>
                                    <img class="img" src="../image/confessions/happy/<?php echo htmlspecialchars($file); ?>" alt="media">
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="dots">
                            <?php foreach ($mediaFiles as $index => $file) { ?>
                                <label for="img<?php echo $index+1; ?>"></label>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
        </div>
        <?php } else { ?>
            <div style="margin-top: 50px;"></div>
        <?php } ?>
        <div class="post">
            <div class="title">
                <h1><?php echo $title ?></h1>
            </div>
            <div class="description">
                <p><?php echo $msg ?></p>
            </div>
        </div>
        <div class="date">
            <?php $date = date("Y-n-j hi A", strtotime($unformatted_date));?>
            <p><?php echo $date ?></p>
            <div class="horizontal"></div>
        </div>
        <div class="all_comments">
            <div class="comment_title">
                <img src="../image/icons/comments.png" alt="">
                <h1>Comments</h1>
            </div>
            <div class="comment" id="commentList">
                <?php
                    $sqlComments = 'SELECT * FROM comment 
                                    WHERE confession_id = ? 
                                    AND comment_status = "approved"
                                    ORDER BY comment_id ASC';
                    $stmtComments = $dbConn->prepare($sqlComments);
                    $stmtComments->bind_param('s', $confession);
                    $stmtComments->execute();
                    $resultComments = $stmtComments->get_result();

                    if ($resultComments->num_rows > 0) {
                        while ($comment = $resultComments->fetch_assoc()) {
                ?>
                <div class="each_comment">
                    <p><?php echo $comment['comment_message'] ?></p>
                    <div class="comment_footer">
                        <p onclick="toggleReply(this);"><img src="../image/icons/reply.png" alt="" width="7px" height="7px"> Reply</p>
                        <div class="spacer"></div>
                        <?php 
                            $unformatted_comment_date = $comment['comment_date_time'];
                            $comment_date =  date("Y-n-j hi A", strtotime($unformatted_comment_date));
                        ?>
                        <p><?php echo $comment_date ?></p>
                    </div>
                    <div class="reply" style="display: none;">
                        <div class="horizontal" style="margin: 0 auto 5px auto; width: 85%; background-color: #B8B8B8;"></div>
                        <?php
                            $comment_id = $comment['comment_id'];
                            // Fetch replies for this comment
                            $sqlReplies = 'SELECT * FROM reply 
                                        WHERE comment_id = ? 
                                        AND reply_status = "approved"
                                        ORDER BY reply_id DESC';
                            $stmtReplies = $dbConn->prepare($sqlReplies);
                            $stmtReplies->bind_param('s', $comment_id);
                            $stmtReplies->execute();
                            $resultReplies = $stmtReplies->get_result();

                            if ($resultReplies->num_rows > 0) {
                                while ($reply = $resultReplies->fetch_assoc()) {
                                
                        ?>
                        <div class="each_reply">
                            <p class="rply_msg"><?php echo $reply['reply_message'] ?></p>
                            <?php 
                                $unformatted_reply_date = $reply['reply_date_time'];
                                $reply_date =  date("Y-n-j hi A", strtotime($unformatted_reply_date));
                            ?>
                            <p class="date"><?php echo $reply_date ?></p>
                        </div>
                        <!-- Add reply form -->
                        <form method="post" class="addReplyBox" style="display:none;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
                            <input type="hidden" name="type" value="reply">
                            <textarea name="reply_message" placeholder="Write your reply..." required></textarea>
                            <button type="submit">Reply!</button>
                        </form>
                        <?php
                                }
                        ?>
                    </div>
                </div>
                <?php
                        } else {
                ?>
                <div class="each_reply">
                    <p style="text-align: center; color: grey;">No replies yet!</p>
                </div>
                <!-- Add reply form -->
                <form method="post" class="addReplyBox" style="margin-top:10px; display:none;">
                    <input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
                    <input type="hidden" name="type" value="reply">  <!-- ✅ ADD THIS -->
                    <textarea name="reply_message" placeholder="Write your reply..." required style="height:50px;"></textarea>
                    <button type="submit">Post Reply</button>
                </form>
                <?php
                        }
                    }
                } else {
                ?>
                <p style="text-align: center; color: grey;">No comments yet!</p>
                <?php
                }
                ?>
            </div>
            <div id="toast"></div>
        </div>
        <div id="addCommentBox">
            <form method="post" id="commentForm">
                <input type="hidden" name="confession_id" value="<?php echo $confession ?>">
                <input type="hidden" name="type" value="comment">
                <textarea name="comment_message" placeholder="Write your comment..." required></textarea>
                <button type="submit"><img src="../image/icons/send.png" alt=""></button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            /* ---------------- TOAST ---------------- */
            const showToast = (msg) => {
                const toast = document.getElementById("toast");
                if (!toast) return;
                toast.textContent = msg;
                toast.classList.add("show");
                setTimeout(() => toast.classList.remove("show"), 3000);
            };

            /* ---------------- SCROLL ---------------- */
            const scrollToBottom = () => {
                const list = document.getElementById("commentList");
                if (!list) return;
                let tries = 0;
                const timer = setInterval(() => {
                list.scrollTo({ top: list.scrollHeight, behavior: "smooth" });
                if (++tries > 5) clearInterval(timer);
                }, 100);
            };

            /* ---------------- MEDIA HANDLING ---------------- */
            const getAllMedia = () => [...document.querySelectorAll("video, audio")];

            // Pause all media (for swipe or when switching slides)
            // const stopAllMedia = () => {
            //     getAllMedia().forEach(m => {
            //     try { if (!m.paused) m.pause(); } catch (e) {}
            //     });
            // };

            // When user manually plays something, pause others
            getAllMedia().forEach(m => {
                m.addEventListener("play", () => {
                getAllMedia().forEach(o => { if (o !== m) o.pause(); });
                });
            });

            /* ---------------- SLIDER SWIPE ---------------- */
            const slider = document.querySelector(".slider");
            if (slider) {
                const radios = [...slider.querySelectorAll('input[name="slide"]')];
                const SWIPE_THRESHOLD = 50;
                let startX = 0, dragging = false;

                const moveToSlide = (dir) => {
                const idx = radios.findIndex(r => r.checked);
                if (dir === "next" && idx < radios.length - 1) radios[idx + 1].checked = true;
                else if (dir === "prev" && idx > 0) radios[idx - 1].checked = true;
                };

                // Touch (mobile)
                slider.addEventListener("touchstart", (e) => {
                const t = e.touches[0];
                startX = t.clientX;
                dragging = true;
                // stopAllMedia();
                }, { passive: true });

                slider.addEventListener("touchend", (e) => {
                if (!dragging) return;
                dragging = false;
                const endX = e.changedTouches[0].clientX;
                const diff = startX - endX;
                if (diff > SWIPE_THRESHOLD) moveToSlide("next");
                else if (diff < -SWIPE_THRESHOLD) moveToSlide("prev");
                }, { passive: true });

                // Mouse (desktop)
                slider.addEventListener("mousedown", e => { startX = e.clientX; dragging = true; });
                document.addEventListener("mouseup", e => {
                if (!dragging) return;
                dragging = false;
                const diff = startX - e.clientX;
                if (diff > SWIPE_THRESHOLD) moveToSlide("next");
                else if (diff < -SWIPE_THRESHOLD) moveToSlide("prev");
                });

                // Prevent dragging images from interrupting swipe
                slider.querySelectorAll("img").forEach(img =>
                img.addEventListener("dragstart", e => e.preventDefault())
                );
            }

            /* ---------------- COMMENT SUBMIT ---------------- */
            const commentForm = document.getElementById("commentForm");
            if (commentForm) {
                commentForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                try {
                    const res = await fetch("save.php", { method: "POST", body: new FormData(commentForm) });
                    const data = await res.json();
                    if (data.status === "success") {
                    showToast("✅ Your comment has been submitted and is awaiting admin approval.");
                    commentForm.querySelector("textarea").value = "";
                    } else showToast("⚠️ Error submitting comment.");
                } catch {
                    showToast("❌ Failed to send comment.");
                }
                });

                commentForm.querySelector("textarea")?.addEventListener("focus", () => {
                setTimeout(scrollToBottom, 300);
                });
            }

            /* ---------------- REPLY SUBMIT ---------------- */
            document.querySelectorAll(".addReplyBox").forEach(form => {
                form.addEventListener("submit", async (e) => {
                e.preventDefault();
                try {
                    const res = await fetch("save.php", { method: "POST", body: new FormData(form) });
                    const data = await res.json();
                    if (data.status === "success") {
                    showToast("✅ " + (data.message || "Reply submitted."));
                    form.querySelector("textarea").value = "";
                    form.style.display = "none";
                    } else showToast("⚠️ Error submitting reply.");
                } catch {
                    showToast("❌ Failed to send reply.");
                }
                });
            });

            /* ---------------- TOGGLE REPLY BOX ---------------- */
            window.toggleReply = (el) => {
                const comment = el.closest(".each_comment");
                const replyBox = comment?.querySelector(".reply");
                const addBox = comment?.querySelector(".addReplyBox");
                if (!replyBox) return;
                const show = replyBox.style.display === "none" || replyBox.style.display === "";
                replyBox.style.display = show ? "block" : "none";
                if (addBox) addBox.style.display = show ? "block" : "none";
            };

            /* ---------------- INIT ---------------- */
            window.addEventListener("load", scrollToBottom);
            getAllMedia().forEach(m => { m.autoplay = false; m.muted = false; });

        });

        // let iosUnlocked = false;
        // document.body.addEventListener("touchstart", () => {
        //     if (!iosUnlocked) {
        //         iosUnlocked = true;
        //         getAllMedia().forEach(m => {
        //         const playPromise = m.play();
        //         if (playPromise) {
        //             playPromise.then(() => m.pause()).catch(() => {});
        //         } else {
        //             try { m.play(); m.pause(); } catch(e) {}
        //         }
        //         });
        //         console.log("iOS media playback unlocked ✅");
        //     }
        // }, { once: true, passive: true });
    </script>
</body>
</html>