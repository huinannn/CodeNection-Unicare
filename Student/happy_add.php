<?php
session_start();
include '../conn.php';


if (!isset($_SESSION['student_id'])) {
    // Not logged in, redirect to login page
    header("Location: Login/login.php");
    exit();
}

$student_id = "";

if (isset($_SESSION['student_id'])) {
    $id = $_SESSION['student_id'];

    
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
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative; 
            padding: 10px;
        }
        .header_left, .header_right, .header_center {
            margin-top: 10px !important;
        }

        .header_left {
            flex: 0;

        }

        .header_right {
            flex: 0;
            margin-left: auto;
        }
        .header_center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .header_left img {
            width: 20px !important;
            height: 20px !important;
        }

        .header_center h1 {
            font-family: 'Itim', cursive;
            font-size: 25px;
            font-weight: 400;
            color: #E8B45E;
        }

        .header .btn {
            border: none;
            background-color: #D9D9D9;
            color: #666666;
            padding: 5px 20px;
            border-radius: 20px;
            font-weight: 600;
            margin-right: 10px;
            cursor: not-allowed;
        }

        .header .btn.active {
            background-color: #08C97E !important;
            color: white !important;
            cursor: pointer !important;
        }

        .post input {
            max-width: 365px;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: none;
            box-sizing: border-box;
        }

        .post input:focus, .post textarea:focus {
            background-color: #D9D9D9;
            border: none;
            outline: none;
            border-radius: 10px;
        }

        .post textarea {
            width: 100%;
            height: 300px;
            font-size: 16px;
            border: none;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 10px;
        }

        .large {
            width: 100%;
            max-width: 480px;
            height: 229px;
            position: fixed;
            bottom: 0;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5); 
        }

        .large .horizontal {
            margin: 20px auto;
            width: 80px;
        }

        .large .all_attach_tools {
            display: grid;
            grid-template-columns: repeat(2, 160px);
            gap: 20px;
            justify-content: center;
        }

        .large .each_attach {
            background-color: #FBF3E2;
            padding: 10px;
            border-radius: 10px;
        }

        .large .each_attach {
            display: flex;
            flex-direction: row;
        }

        .large .each_attach .left img {
            width: 30px;
            height: 30px;
        }

        .large .each_attach .right img {
            width: 15px;
            height: 15px;
        }

        .large .each_attach p {
            margin: 0;
            font-weight: 500;
            font-size: 11px;
        }

        .small {
            width: 100%;
            max-width: 480px;
            height: 90px;
            position: fixed;
            bottom: 0;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5); 
            display: flex;
            flex-direction: row;
            gap: 40px;
            align-items: center;
            justify-content: center;
        }

        .small .each_attach .more {
            width: 17px;
            height: 17px;
        }

        .small .each_attach img {
            width: 35px;
            height: 35px;
        }

        .preview-container {
            margin-top: 10px;
            margin-bottom: 100px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .preview-container img {
            max-width: 100px;
            border-radius: 6px;
        }

        .preview-container video,
        .preview-container audio {
            max-width: 300px;
            border-radius: 6px;
        }

        .preview-wrapper {
            position: relative;
            display: inline-block;
        }

        .preview-wrapper span {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.6);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            cursor: pointer;
            font-size: 14px;
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
    <div class="confessions" style="padding-bottom: 0 !important;">
        <div class="header">
            <div class="header_left">
                <img src="../image/icons/close.png" alt="" onclick="window.location.href='happy_profile.php'">
            </div>
            <div class="header_center">
                <h1>New Post</h1>
            </div>
            <div class="header_right">
                <button class="btn">Post</button>
            </div>
        </div>
        <div class="content" style="margin-top: 70px; display: block;">
            <div class="post">
                <form id="postForm" enctype="multipart/form-data">
                    <div class="title">
                        <input type="text" name="title" placeholder="Title" required>
                    </div>
                    <div class="textarea-wrapper">
                        <textarea name="post_input" placeholder="What do you want to confess about?" required></textarea>
                        <div class="preview-container" id="previewArea"></div>
                    </div>
                    <input type="hidden" name="mode" id="postMode" value="happy">
                </form>
            </div>
        </div>
        <div class="attach">
            <!-- Large panel -->
            <div class="large" style="display: none;" id="dragHandle">
                <div class="change_small">
                    <div class="horizontal"></div>
                </div>
                <div class="all_attach_tools">
                    <div class="each_attach" onclick="this.querySelector('input').click()">
                        <input type="file" id="photoPickerLarge" accept="image/*,video/*" style="display:none;" multiple>
                        <div class="left">
                            <img src="../image/icons/image.png" alt="">
                            <p>Photo/Video</p>
                        </div>
                        <div class="spacer"></div>
                        <div class="right">
                            <img src="../image/icons/add_black.png" alt="">
                        </div>
                    </div>
                    <div class="each_attach" onclick="this.querySelector('input').click()">
                        <input type="file" id="gifPickerLarge" accept="image/gif" style="display:none;" multiple>
                        <div class="left">
                            <img src="../image/icons/gif.png" alt="">
                            <p>Gif</p>
                        </div>
                        <div class="spacer"></div>
                        <div class="right">
                            <img src="../image/icons/add_black.png" alt="">
                        </div>
                    </div>
                    <div class="each_attach" onclick="this.querySelector('input').click()">
                        <input type="file" id="cameraPickerLarge" accept="image/*,video/*;capture=camera" capture style="display:none;" multiple>
                        <div class="left">
                            <img src="../image/icons/camera.png" alt="">
                            <p>Camera</p>
                        </div>
                        <div class="spacer"></div>
                        <div class="right">
                            <img src="../image/icons/add_black.png" alt="">
                        </div>
                    </div>
                    <div class="each_attach" onclick="this.querySelector('input').click()">
                        <input type="file" id="audioPickerLarge" accept="*/*" onchange="checkFileType(this)"  style="display:none;" multiple>
                        <div class="left">
                            <img src="../image/icons/audio.png" alt="">
                            <p>Audio</p>
                        </div>
                        <div class="spacer"></div>
                        <div class="right">
                            <img src="../image/icons/add_black.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Small Panel -->
            <div class="small">
                <div class="each_attach" onclick="this.querySelector('input').click()">
                    <input type="file" id="photoPickerSmall" accept="image/*,video/*" style="display:none;" multiple>
                    <img src="../image/icons/image.png" alt="">
                </div>
                <div class="each_attach" onclick="this.querySelector('input').click()">
                    <input type="file" id="gifPickerSmall" accept="image/gif" style="display:none;" multiple>
                    <img src="../image/icons/gif.png" alt="">
                </div>
                <div class="each_attach" onclick="this.querySelector('input').click()">
                    <input type="file" id="cameraPickerSmall" accept="image/*,video/*;capture=camera" capture (change)="getFile($event)" style="display:none;" multiple>
                    <img src="../image/icons/camera.png" alt="">
                </div>
                <div class="each_attach" onclick="this.querySelector('input').click()">
                    <input type="file" id="audioPickerSmall" accept="*/*" onchange="checkFileType(this)"  style="display:none;" multiple>
                    <img src="../image/icons/audio.png" alt="">
                </div>
                <div class="each_attach">
                    <img class="more" src="../image/icons/more.png" alt="" onclick="changeToBig()">
                </div>
            </div>
            <div id="toast"></div>
        </div>
    </div>
    <script>
        const large = document.querySelector(".large");
        const small = document.querySelector(".small");
        const handle = document.getElementById("dragHandle");
        const inputs = document.querySelectorAll("input, textarea");

        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        let inputFocused = false;

        // Switch panels
        function changeToBig() {
            if (inputFocused) return;
            large.style.display = "block";
            small.style.display = "none";
        }
        function changeToSmall() {
            large.style.display = "none";
            small.style.display = "flex";
        }

        // Dragging behavior
        function startDrag(y) {
            if (inputFocused) return;
            isDragging = true;
            startY = y;
            currentY = y;
            large.style.transition = "none";
        }
        function duringDrag(y) {
            if (!isDragging || inputFocused) return;
            currentY = y;
            let diff = currentY - startY;
            if (diff > 0) {
                large.style.transform = `translateY(${diff}px)`;
            }
        }
        function stopDrag() {
            if (!isDragging || inputFocused) return;
            isDragging = false;
            let diff = currentY - startY;
            large.style.transition = "transform 0.3s ease";

            if (diff > 100) {
                changeToSmall();
            }
            large.style.transform = "translateY(0)";
        }

        // Block large panel while typing
        inputs.forEach(el => {
            el.addEventListener("focus", () => {
                inputFocused = true;
                changeToSmall();
            });
            el.addEventListener("blur", () => {
                inputFocused = false;
            });
        });

        // Touch events
        handle.addEventListener("touchstart", e => startDrag(e.touches[0].clientY));
        handle.addEventListener("touchmove", e => duringDrag(e.touches[0].clientY));
        handle.addEventListener("touchend", stopDrag);

        // Mouse events
        handle.addEventListener("mousedown", e => startDrag(e.clientY));
        document.addEventListener("mousemove", e => duringDrag(e.clientY));
        document.addEventListener("mouseup", stopDrag);

        // --- Post Validation ---
        const postBtn = document.querySelector(".btn");
        const titleInput = document.querySelector("input[name='title']");
        const textArea = document.querySelector("textarea[name='post_input']");
        const previewArea = document.getElementById("previewArea");
        let allFiles = [];

        function validateForm() {
            const hasTitle = titleInput.value.trim().length > 0;
            const hasText = textArea.value.trim().length > 0;
            const hasMedia = previewArea.children.length > 0;

            if (hasTitle && (hasText || hasMedia)) {
                postBtn.classList.add("active");
            } else {
                postBtn.classList.remove("active");
            }
        }


        // --- Preview Handling ---
        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement("div");
                wrapper.className = "preview-wrapper";
                let element;

                if (file.type.startsWith("image/")) {
                    element = document.createElement("img");
                    element.src = e.target.result;
                } else if (file.type.startsWith("video/")) {
                    element = document.createElement("video");
                    element.src = e.target.result;
                    element.controls = true;
                } else if (file.type === "image/gif") {
                    element = document.createElement("img");
                    element.src = e.target.result;
                } else if (file.type.startsWith("audio/")) {
                    element = document.createElement("audio");
                    element.src = e.target.result;
                    element.controls = true;
                }

                if (element) {
                    const closeBtn = document.createElement("span");
                    closeBtn.innerHTML = "&times;";
                    closeBtn.onclick = () => {
                        wrapper.remove();
                        allFiles = allFiles.filter(f => f !== file); // ✅ remove from array
                        validateForm();
                    };
                    wrapper.appendChild(element);
                    wrapper.appendChild(closeBtn);
                    previewArea.appendChild(wrapper);

                    allFiles.push(file); // ✅ add to array
                    validateForm();
                }
            };
            reader.readAsDataURL(file);
        }

        // Setup multiple pickers
        function setupPreview(inputId) {
            const input = document.getElementById(inputId);
            input.addEventListener("change", e => {
                if (e.target.files.length > 0) {
                    for (let file of e.target.files) {
                        showPreview(file);
                    }
                }
                input.value = ""; // ✅ reset so same file can be picked again
            });
        }


        ["photoPickerLarge","photoPickerSmall",
        "gifPickerLarge","gifPickerSmall",
        "cameraPickerLarge","cameraPickerSmall",
        "audioPickerLarge","audioPickerSmall"].forEach(setupPreview);

        // Validation on input typing
        titleInput.addEventListener("input", validateForm);
        textArea.addEventListener("input", validateForm);

        function showToast(message, type = "info") {
            const toast = document.getElementById("toast");
            toast.className = "show " + type;
            toast.textContent = message;

            setTimeout(() => {
                toast.className = toast.className.replace("show", "");
            }, 3000);
        }

        // Prevent accidental submit
        postBtn.addEventListener("click", (e) => {
            e.preventDefault();

            if (!postBtn.classList.contains("active")) return;

            const form = document.getElementById("postForm");
            const formData = new FormData(form);

            // Append all files from preview inputs
            allFiles.forEach(file => {
                console.log("Appending file:", file.name);
                formData.append("media[]", file);
            });

            fetch("save.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                     showToast("✅ " + data.message, "success");
                    form.reset();
                    previewArea.innerHTML = ""; // clear preview
                    postBtn.classList.remove("active");
                    setTimeout(() => window.location.href="happy_zone.php", 1500);
                } else {
                     showToast("⚠️ " + data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("❌ Failed to save post." + data.message, "error");
            });
        });

        function checkFileType(input) {
            const file = input.files[0];
            if (!file) return;
            const allowed = ['audio/mpeg', 'audio/mp4', 'audio/x-m4a'];
            if (!allowed.includes(file.type)) {
                showToast('Please upload only audio files (MP3, M4A).');
                input.value = ''; // reset file input
            }
        }

        // Initial check
        validateForm();
    </script>
</body>
</html>
