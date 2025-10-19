<?php
    session_start();
    include '../conn.php';
    if(!isset($_SESSION['student_id'])){
        header("Location: Login/login.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];
    $todayDate = date('Y-m-d');
    $todayFeeling = null;
    
    // 1. Get today's feeling (single row check)
    $checkToday = $dbConn->prepare("SELECT feeling_status FROM feeling WHERE student_id = ? AND DATE(feeling_date_time) = ? LIMIT 1");
    $checkToday->bind_param("ss", $student_id, $todayDate);
    $checkToday->execute();
    $checkToday->bind_result($todayFeeling);
    $checkToday->fetch();
    $checkToday->close();

    // 2. Get the latest 30 feelings for the chart
    $feelings_query = "SELECT feeling_status, DATE(feeling_date_time) as date FROM feeling WHERE student_id = ? ORDER BY feeling_date_time DESC LIMIT 30";
    $stmt = $dbConn->prepare($feelings_query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count each feeling type
    $feeling_counts = [
        'happy' => 0,
        'calm' => 0,
        'manic' => 0,
        'angry' => 0,
        'sad' => 0
    ];
    while($row = $result->fetch_assoc()){
        $status = strtolower($row['feeling_status']);
        if(isset($feeling_counts[$status])) $feeling_counts[$status]++;
        if($row['date'] == $todayDate) $todayFeeling = $status;
    }
    $stmt->close();

    // 3. Get booked dates for calendar
    $booked_query = "SELECT DATE(booking_date) as date, booking_status FROM booking WHERE student_id = ? AND booking_status IN ('pending', 'approved')";
    $stmt2 = $dbConn->prepare($booked_query);
    $stmt2->bind_param("s", $student_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    $booked_dates = [];
    while($row = $result2->fetch_assoc()){
        $booked_dates[$row['date']] = $row['booking_status']; // store status
    }
    $stmt2->close();

    // 4. Get upcoming counselling (nearest future booking)
    $upcoming = null;
    $upcoming_sql = "
        SELECT b.booking_date, b.booking_start_time, b.booking_end_time,
            c.counselor_name, s.school_name
        FROM booking b
        JOIN counselor c ON b.counselor_id = c.counselor_id
        JOIN school s ON c.school_id = s.school_id
        WHERE b.student_id = ? AND b.booking_date > CURDATE()
        ORDER BY b.booking_date ASC, b.booking_start_time ASC
        LIMIT 1
    ";
    $stmt3 = $dbConn->prepare($upcoming_sql);
    $stmt3->bind_param("s", $student_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    if ($result3->num_rows > 0) {
        $upcoming = $result3->fetch_assoc();
    }
    $stmt3->close();

    // 5. Get notifications for student
    $notification_sql = "
        SELECT na.message_id AS id, 
            CONCAT('Dear Student,', CHAR(10), na.message) AS message, 
            na.message_date_time, 
            na.read_status AS read_status, 
            'Admin' AS title
        FROM notification_admin na
        INNER JOIN booking b ON na.booking_id = b.booking_id
        WHERE b.student_id = ?
        
        UNION
        
        SELECT ns.notification_id AS id, 
            CASE 
                WHEN ns.notification_id IS NOT NULL 
                THEN 'Your appointment has successfully booked, please check your calendar!' 
            END AS message,
            ns.notification_date_time AS message_date_time, 
            ns.read_status AS read_status, 
            'System' AS title
        FROM notification_system ns
        INNER JOIN booking b ON ns.booking_id = b.booking_id
        WHERE b.student_id = ?
        
        ORDER BY message_date_time DESC
    ";
    $stmt4 = $dbConn->prepare($notification_sql);
    $stmt4->bind_param("ss", $student_id, $student_id);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    $notifications = [];
    while ($row = $result4->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => mb_strimwidth($row['message'], 0, 60, "..."),
            'time' => $row['message_date_time'],
            'read_status' => $row['read_status']
        ];
    }
    $stmt4->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Unicare</title>
    <link rel="icon" href="image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Itim', cursive; background: #fff; margin: 0 auto; padding-bottom: 25%; max-width: 480px !important; }
        .dashboard-header {
            color: #F48C8C;
            font-size: 2rem;
            font-weight: bold;
            margin: 20px 20px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dashboard-icons { display: flex; gap: 16px; }
        .dashboard-icons img { width: 28px; height: 28px; }
        .feelings-section { margin: 20px; }
        .feelings-title { font-size: 1.1rem; margin-bottom: 10px; }
        .feelings-options { display: flex; gap: 18px; margin-bottom: 18px; }
        .feeling-btn { border: none; background: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; }
        .feeling-icon { width: 48px; height: 48px; border-radius: 12px; margin-bottom: 4px; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
        .happy { background: #F48C8C; }
        .calm { background: #A7D8F5; }
        .manic { background: #B6E3C6; }
        .angry { background: #FFD59E; }
        .sad { background: #B7E5B7; }
        .feeling-label { font-size: 0.95rem; color: #444; }
        #emotion-journey-row {
            background: #FFF6F0;
            border-radius: 18px;
            padding: 12px;
            margin: 0 20px 12px 20px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 100%;
            height: 170px;
            box-sizing: border-box;
        }
        .emotion-journey-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex: 1;
            align-items: flex-start;
        }
        .emotion-title { font-size: 1.1rem; font-weight: 500; margin-bottom: 8px; }
        .emotion-message { font-size: 0.98rem; color: #888; margin-bottom: 10px; }
        .emotion-chart-container {
            flex-shrink: 0;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-container {
            position: relative;
            width: 100px; /* Set a fixed, equal width and height */
            height: 100px;
        }
        .emotion-chart {
            width: 100%;
            height: 100%;
            /* margin-right: 15px; */
            box-shadow: 0 2px 8px rgba(244, 140, 140, 0.18);
            background: #FFF6F0;
            border-radius: 50%;
        }
        .action-buttons { display: flex; gap: 18px; margin: 0 20px 18px 20px; }
        .action-btn { font-family: 'Itim', cursive; flex: 1; background: #E6D3C7; border-radius: 12px; padding: 14px 0; text-align: center; font-size: 1.1rem; font-weight: 500; color: #7A5C3A; border: none; cursor: pointer; }
        .calendar-section { margin: 0 20px 20px 20px; }
        .calendar-title { color: #F48C8C; font-size: 1.2rem; font-weight: bold; margin-bottom: 8px; }

        .calendar-selects { margin-bottom: 10px; display: flex; gap: 10px; justify-content: center; }
        .calendar-selects select { padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc; color: orange; font-weight: bold; }

        .calendar-table { width: 100%; border-collapse: collapse; }
        .calendar-table th, .calendar-table td { width: 14%; text-align: center; padding: 6px 0; }
        .calendar-table th { color: #F48C8C; font-weight: 500; } /* weekday headers pink */
        .calendar-table td { color: #444; }
        .calendar-weekend { color: orange !important; font-weight: 500; }

        /* Booked status squares */
        .calendar-booked-pending {
            background: pink;
            color: #fff;
            font-weight: bold;
            border-radius: 8px;
        }
        .calendar-booked-approved {
            background: #d9f5b3; /* light yellow-green */
            color: #444;
            font-weight: bold;
            border-radius: 8px;
        }

        /* Today's date */
        .calendar-today {
            color: red !important;
            font-weight: bold;
        }

        .calendar-month { background: #FFA85C; color: #fff; border-radius: 8px; padding: 2px 10px; font-size: 0.95rem; margin-bottom: 6px; display: inline-block; text-align: center; }

        /* Legend */
        .calendar-legend { display: flex; gap: 15px; margin: 10px 0 0 20px; align-items: center; }
        .legend-box { width: 20px; height: 20px; display: inline-block; border-radius: 8px; }
        .legend-text { margin-left: 5px; font-weight: bold; }

        @media (max-width: 500px) {
            .calendar-section { margin: 10px; }
        }
        @media (max-width: 700px) {
            .emotion-journey-row { flex-direction: row !important; gap: 8px; padding: 8px; margin: 0 8px 8px 8px; }
            .emotion-chart { width: 30px; height: 30px; }
            .emotion-journey-info { align-items: flex-start; }
        }
        @media (max-width: 500px) {
            .emotion-journey-row { margin: 6px; }
            .emotion-chart { width: 20px; height: 20px; }
        }
        @media (max-width: 500px) {
            .dashboard-header, .calendar-section, .feelings-section, .emotion-journey-row, .action-buttons { margin: 10px; }
        }

        .notification-bell {
            position: relative;
            display: inline-block;
        }
        .notification-red-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 10px;
            height: 10px;
            background: #FF3B3B;
            border-radius: 50%;
            border: 2px solid #fff;
            z-index: 2;
            pointer-events: none;
        }
        .notification-green-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #2ECC40;
            border-radius: 50%;
            margin-left: 6px;
            vertical-align: middle;
        }

        .profile-dropdown {
            position: relative;
            cursor: pointer;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            top: 36px;
            right: 0;
            box-shadow: 0px 8px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
            min-width: 140px;
            z-index: 10;
        }

        .profile-dropdown-content a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #3D3D3D;
            font-size: 0.95rem;
            font-weight: normal;
        }

        .profile-dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .profile-dropdown-content a.logout-link {
            display: flex;
            align-items: center;
            gap: 15px; 
            text-decoration: none;
            color: #000; 
            font-size: 0.95rem;
        }

        .slider {
            overflow: hidden;
            margin: 0 20px 12px 20px;
            border-radius: 18px;
        }
        .slider-wrapper {
            display: flex;
            transition: transform 0.5s ease;
            will-change: transform;
        }
        .slider-item {
            min-width: 90%;
            box-sizing: border-box;
            padding-right: 10%;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        Dashboard
        <div class="dashboard-icons">
            <span class="notification-bell" onclick="openNotifications()" style="cursor:pointer;">
                <img src="../image/icons/notification.png" alt="Notifications" />
                <span id="notificationRedDot" class="notification-red-dot" style="display:none;"></span>
            </span>
            <div class="profile-dropdown">
                <img src="../image/icons/profile.png" alt="Profile" />
                <div class="profile-dropdown-content">
                     <a href="../Student/Login/logout.php" class="logout-link">
                        <i class="fa-solid fa-right-from-bracket"></i> Log Out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="feelings-section">
        <div class="feelings-title" id="feelings-question">How are you feeling today?</div>
        <?php if ($todayFeeling): ?>
            <!-- Show encouragement only -->
            <div id="encouragement-message" style="font-size:1rem; color:#888; margin-top:10px;">
                <?php
                    $encouragements = [
                        'happy' => "Keep up the positivity!",
                        'calm' => "Stay balanced and serene!",
                        'manic' => "Take a deep breath, you're doing great!",
                        'angry' => "It's okay to feel angry. Try to relax!",
                        'sad' => "It's okay to feel sad. Remember, brighter days are ahead!"
                    ];
                    echo $encouragements[$todayFeeling];
                ?>
            </div>
        <?php else: ?>
            <!-- Show feeling options -->
            <div class="feelings-options" id="feelings-options">
                <button class="feeling-btn" onclick="selectFeeling('happy')">
                    <div class="feeling-icon happy">😊</div>
                    <span class="feeling-label">Happy</span>
                </button>
                <button class="feeling-btn" onclick="selectFeeling('calm')">
                    <div class="feeling-icon calm">☯️</div>
                    <span class="feeling-label">Calm</span>
                </button>
                <button class="feeling-btn" onclick="selectFeeling('manic')">
                    <div class="feeling-icon manic">🌪️</div>
                    <span class="feeling-label">Manic</span>
                </button>
                <button class="feeling-btn" onclick="selectFeeling('angry')">
                    <div class="feeling-icon angry">😠</div>
                    <span class="feeling-label">Angry</span>
                </button>
                <button class="feeling-btn" onclick="selectFeeling('sad')">
                    <div class="feeling-icon sad">😢</div>
                    <span class="feeling-label">Sad</span>
                </button>
            </div>
            <div id="encouragement-message" style="display:none; font-size:1rem; color:#888; margin-top:10px;"></div>
        <?php endif; ?>
    </div>
    <?php if ($upcoming): ?>
    <div class="slider">
        <div class="slider-wrapper">
            <div class="slider-item" id="upcoming-counselling" style="background:#FFF6F0; border-radius:18px; padding:16px; margin:0 20px 12px 20px; display:block;">
                <div style="font-size:1.3rem; font-weight:bold; margin-bottom:6px;">Upcoming Counselling</div>
                <div style="font-size:1rem; margin-bottom:4px;"><?php echo htmlspecialchars($upcoming['counselor_name']); ?>, Msc in Clinical Psychology</div>
                <div style="font-size:0.95rem; margin-bottom:4px;"><?php echo htmlspecialchars($upcoming['school_name']); ?></div>
                <div style="font-size:0.95rem; margin-bottom:4px;"><?php echo date('j/n/Y', strtotime($upcoming['booking_date'])); ?></div>
                <div style="font-size:0.95rem;"><?php echo date('g:i A', strtotime($upcoming['booking_start_time'])); ?> - <?php echo date('g:i A', strtotime($upcoming['booking_end_time'])); ?></div>
            </div>
            <?php endif; ?>
            <div class="slider-item" id="emotion-journey-row">
                <div class="emotion-journey-info">
                    <div class="emotion-title">Emotions journey for the last 30 days:</div>
                    <div class="emotion-message" id="emotion-message">
                        <?php
                            if($todayFeeling){
                                echo $encouragements[$todayFeeling];
                            } else {
                                echo "Keep up the positivity!";
                            }
                        ?>
                    </div>
                </div>
                <div class="emotion-chart-container">
                    <canvas id="emotionChart" class="emotion-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="action-buttons">
        <button class="action-btn" onclick="location.href='Chatbot/unibot.php'">Unibot</button>
        <button class="action-btn" onclick="location.href='leisure.php'">Leisure</button>
    </div>
    <div class="calendar-section">
        <div class="calendar-title">Calendar</div>
        <div class="calendar-selects">
            <select id="month-select"></select>
            <select id="year-select"></select>
        </div>
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th><th>Su</th>
                </tr>
            </thead>
            <tbody id="calendar-body">
            </tbody>
        </table>

        <!-- Legend -->
        <div class="calendar-legend">
            <div class="legend-box" style="background:pink;"></div><span class="legend-text">Pending</span>
            <div class="legend-box" style="background:#d9f5b3;"></div><span class="legend-text">Approved</span>
        </div>
    </div>
    <?php include 'navigation.php' ?>
    <script>
        // Show each notifications available
        let notifications = <?php echo json_encode($notifications); ?>;

        function hasUnreadNotifications() {
            return notifications.some(n => n.read_status === 'unread');
        }

        function updateNotificationBell() {
            document.getElementById('notificationRedDot').style.display = hasUnreadNotifications() ? 'block' : 'none';
        }

        function openNotifications() {
            window.location.href = 'notification.php';
        }

        updateNotificationBell();

        // 1. Chart data from PHP
        const emotions = <?php echo json_encode($feeling_counts); ?>;
        const emotionColors = {
            happy: '#F48C8C',
            calm: '#A7D8F5',
            manic: '#B6E3C6',
            angry: '#FFD59E',
            sad: '#B7E5B7'
        };

        function updateEmotionChart() {
            const ctx = document.getElementById('emotionChart').getContext('2d');
            if (window.emotionChartInstance) window.emotionChartInstance.destroy();
            window.emotionChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(emotions),
                    datasets: [{
                        data: Object.values(emotions),
                        backgroundColor: Object.values(emotionColors),
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 2. Restrict feeling selection to once per day
        function selectFeeling(feeling) {
            // AJAX to save feeling to DB
            fetch('../Student/save_feeling.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'feeling_status=' + feeling
            })
            .then(response => response.json())
            .then(data => {
                if(data.success){
                    document.getElementById('feelings-question').textContent = "Thank you for sharing!";
                    document.getElementById('emotion-message').textContent = data.encouragement;
                    document.getElementById('feelings-options').style.display = "none";
                    document.getElementById('encouragement-message').textContent = data.encouragement;
                    document.getElementById('encouragement-message').style.display = "block";
                    // Optionally reload chart data
                    location.reload();
                } else {
                    alert("You already submitted your feeling today.");
                }
            });
        }

        // 3. Calendar booked dates from PHP
        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        const today = new Date();
        let selectedMonth = today.getMonth(); 
        let selectedYear = today.getFullYear();

        const monthSelect = document.getElementById('month-select');
        const yearSelect = document.getElementById('year-select');
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        monthNames.forEach((m,i)=> { 
            const option = document.createElement('option');
            option.value = i;
            option.text = m;
            if(i===selectedMonth) option.selected=true;
            monthSelect.appendChild(option);
        });
        for(let y=today.getFullYear()-5; y<=today.getFullYear()+5; y++){
            const option = document.createElement('option');
            option.value=y;
            option.text=y;
            if(y===selectedYear) option.selected=true;
            yearSelect.appendChild(option);
        }

        monthSelect.addEventListener('change', ()=>{ selectedMonth=parseInt(monthSelect.value); generateCalendar(); });
        yearSelect.addEventListener('change', ()=>{ selectedYear=parseInt(yearSelect.value); generateCalendar(); });

        function generateCalendar(){
            const firstDay = new Date(selectedYear, selectedMonth, 1).getDay(); // 0=Sun
            const daysInMonth = new Date(selectedYear, selectedMonth+1, 0).getDate();
            let html='';
            let day=1;
            for(let row=0; row<6; row++){
                html+='<tr>';
                for(let col=1; col<=7; col++){
                    let cellDay = (row===0 && col < ((firstDay===0?7:firstDay))) ? '' : day<=daysInMonth?day:'';
                    let classes='';
                    let content=cellDay?cellDay:'';

                    if(cellDay){
                        let cellMonth = selectedMonth+1;
                        let cellDateStr = selectedYear + '-' + (cellMonth<10?'0'+cellMonth:cellMonth) + '-' + (cellDay<10?'0'+cellDay:cellDay);
                        if(bookedDates[cellDateStr]==='pending'){
                            classes+=' calendar-booked-pending';
                        } else if(bookedDates[cellDateStr]==='approved'){
                            classes+=' calendar-booked-approved';
                        } else if(col===6 || col===7){
                            classes+=' calendar-weekend';
                        }

                        // Today font red
                        if(cellDay===today.getDate() && selectedMonth===today.getMonth() && selectedYear===today.getFullYear()){
                            classes+=' calendar-today';
                        }

                        day++;
                    }
                    html+=`<td class="${classes}">${content}</td>`;
                }
                html+='</tr>';
                if(day>daysInMonth) break;
            }
            document.getElementById('calendar-body').innerHTML=html;
        }

        // Initial render
        updateEmotionChart();
        generateCalendar();

        // Switch between counselling and emotion journey every 10 seconds
        document.addEventListener("DOMContentLoaded", () => {
            const wrapper = document.querySelector(".slider-wrapper");
            const items = document.querySelectorAll(".slider-item");
            let currentIndex = 0;
            let startX = 0;
            let endX = 0;

            function showSlide(index) {
                if (index < 0) index = items.length - 1;
                if (index >= items.length) index = 0;
                currentIndex = index;
                wrapper.style.transform = `translateX(-${index * 100}%)`;
            }

            // Auto switch every 10s
            setInterval(() => {
                showSlide(currentIndex + 1);
            }, 10000);

            // Touch swipe
            wrapper.addEventListener("touchstart", e => {
                startX = e.touches[0].clientX;
            });

            wrapper.addEventListener("touchend", e => {
                endX = e.changedTouches[0].clientX;
                if (startX - endX > 50) {
                    // swipe left
                    showSlide(currentIndex + 1);
                } else if (endX - startX > 50) {
                    // swipe right
                    showSlide(currentIndex - 1);
                }
            });

            // Show first slide
            showSlide(0);
    });
    </script>
</body>
</html>