<?php
    session_start();
    include '../../conn.php';

    if (!isset($_SESSION['student_id'])) {
        header("Location: ../Login/login.php");
        exit();
    }
    
    $studentId = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Unicare</title>
    <link rel="icon" href="../../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="unibot.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
    <div class="header">
        <a href="../dashboard.php">
            <img src="../../image/icons/arrow.png" alt="Back arrow" class="back-arrow">           
        </a>

        <div class="header-title" id="header-title">
            Chat with Unibot
        </div>

        <div class="dropdown">
            <i class="fas fa-ellipsis-vertical dropdown-btn"></i>
            <div class="dropdown-content">
                <div class="dropdown-item1" data-action="search">
                    <i class="fas fa-magnifying-glass"></i> Search
                </div>
                
                <div class="dropdown-item2" data-action="clear">
                    <i class="fas fa-trash-alt"></i> Clear Chat 
                </div>
            </div>
        </div>
    </div>

    <div class="chatbot">
        <div class="chat-timestamp" id="chat-timestamp"></div>
        <div class="chat-container" id="chat-container"></div>
    </div>

    <div class="input-area">
        <div class="input-box">
            <input type="text" id="user-input" placeholder="Type a message" />
        </div>

        <button class="send-btn" id="send-btn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>

    <div id="reminder-overlay">
        <div class="reminder-backdrop"></div>
        <div class="reminder-sheet">
            <i class="fas fa-trash-alt reminder-icon"></i>
            <div class="reminder-title">Clear Chat</div>
            <div class="reminder-line"></div>
            <div class="reminder-text">
                Do you want to clear this conversation?<br/><br/>
                Once cleared, it can't be restored.
            </div>
            <div class="reminder-line"></div>
            <div class="btn-row">
                <button class="btn btn-1" id="cancelBtn">Cancel</button>
                <button class="btn btn-2" id="yesBtn">Yes</button>
            </div>
        </div>
    </div>

    <div id="empty-chat">
        <div class="empty-icon">
            <i class="fas fa-comment-dots"></i>
        </div>

        <div class="empty-text">
            <div class="title">No chat history</div>
            <div class="line"></div>
            <div class="desc">Start a new conversation and I'll be here to support you!</div>
        </div>

        <div class="empty-btns">
            <div id="startChatBtn">Start Chat</div>
            <div id="homeBtn">Home</div>
        </div>
    </div>

    <div id="snackbar" class="snackbar">
        <span>
            <i class="fas fa-check-circle"></i>
            Successfully deleted.
        </span>
    </div>

    <script>
        const studentId = "<?php echo $studentId; ?>";
        const chatKey = `chatMessages_${studentId}`;
    </script>
<script src="unibot.js"></script>
</body>
</html>