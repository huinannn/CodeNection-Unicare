<?php
session_start();
include '../conn.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: Login/login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get school_id of admin
$getSchool = $dbConn->prepare("SELECT school_id FROM admin WHERE admin_id = ?");
$getSchool->bind_param("s", $admin_id);

$getSchool->execute();
$res = $getSchool->get_result();
$schoolData = $res->fetch_assoc();
$school_id = $schoolData['school_id'];
$getSchool->close();

$popupMsg = "";

// Handle Approve/Reject/Notify actions
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    if($action === "approve"){
        $status = "approved";
        $updateQuery = $dbConn->prepare("UPDATE booking SET booking_status = ? WHERE booking_id = ?");
        $updateQuery->bind_param("si", $status, $booking_id);

        if($updateQuery->execute()){
            $updateQuery->close();

            $insertNotif = $dbConn->prepare("INSERT INTO notification_system 
                (notification_date_time, booking_id, read_status, system_id) 
                VALUES (NOW(), ?, 'unread', 1)");
            $insertNotif->bind_param("i", $booking_id);
            $insertNotif->execute();
            $insertNotif->close();

            // get student_id for message
            $getStudent = $dbConn->prepare("SELECT student_id FROM booking WHERE booking_id = ?");
            $getStudent->bind_param("i", $booking_id);
            $getStudent->execute();
            $stuRes = $getStudent->get_result();
            $stuData = $stuRes->fetch_assoc();
            $student_id = $stuData['student_id'];
            $getStudent->close();

            $popupMsg = "Booking approved for {$student_id} successful and system notification sent";
        }

    } elseif($action === "notify_student"){
        $message = $_POST['message'];

        // Update booking status to reschedule
        $status = "reschedule";
        $update = $dbConn->prepare("UPDATE booking SET booking_status = ? WHERE booking_id = ?");
        $update->bind_param("si", $status, $booking_id);
        $update->execute();
        $update->close();

        // Insert into notification_admin
        $insertNotifAdmin = $dbConn->prepare("INSERT INTO notification_admin
            (message, message_date_time, booking_id, admin_id) 
            VALUES (?, NOW(), ?, ?)");
        $insertNotifAdmin->bind_param("sis", $message, $booking_id, $admin_id);
        $insertNotifAdmin->execute();
        $insertNotifAdmin->close();

        $popupMsg = "Reschedule notification sent successfully.";
    }
}

// Fetch pending appointment requests
$query = $dbConn->prepare("
    SELECT b.booking_id, b.booking_date, b.booking_start_time, b.booking_end_time,
           b.booking_status, s.student_id, s.student_name, c.counselor_name
    FROM booking b
    JOIN student s ON b.student_id = s.student_id
    JOIN counselor c ON b.counselor_id = c.counselor_id
    WHERE c.school_id = ? AND b.booking_status = 'pending'
    ORDER BY b.booking_date, b.booking_start_time
");
$query->bind_param("i", $school_id);
$query->execute();
$appointments = $query->get_result();
$query->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Counselor Appointment Management</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="booking.css" />
    <link rel="icon" href="../image/favicon.png" type="image/x-icon" />

    <style>
        table.calendar { border-collapse: collapse; width: 100%; max-width:700px; margin:20px 0; }
        table.calendar th, table.calendar td { border:1px solid #ccc; padding:8px; text-align:center; width:14%; }
        table.calendar td.empty { background:#f9f9f9; cursor:default; }
        table.calendar td.has-appointment { background:#ffe6cc; font-weight:bold; cursor:pointer; }
        table.calendar td:hover { background:#e6f7ff; }
        .results { margin-top:20px; padding:10px; border:1px solid #ccc; border-radius:8px; }
        .counselor-block { margin-bottom:15px; }
        .reject-box { margin:15px 0; padding:15px; border:1px solid #f00; border-radius:8px; background:#fff4f4; }
        .reject-box textarea { width:100%; height:80px; margin-top:10px; }
        .reject-box button { margin:5px; padding:5px 15px; border:none; border-radius:6px; cursor:pointer; }
        .back-btn { background:#ccc; }
        .notify-btn { background:#ff6969; color:#fff; }
        .btn button { margin:2px; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="right-content" style="padding-left:350px;">
        <div class="admin-booking">
            <h1 style="font-family:var(--itim);">Counselor Appointment Management</h1>
        </div>
        <div class="right">
            <div class="Appointment-Request">
                <h1 style="font-family:var(--itim);">Appointment Request</h1>
                <hr>
                

                <div class="booking-content" style="font-family:var(--itim);">
                    <?php while($row = $appointments->fetch_assoc()): ?>
                        <div class="content" id="booking-<?php echo $row['booking_id']; ?>" style="font-family:var(--itim);">
                            <div class="content-left" style="font-family:var(--itim);">
                                <div class="first-line" style="font-family:var(--itim);">
                                    <div class="std-id" style="font-family:var(--itim);">ID: <?php echo $row['student_id']; ?></div>
                                    <p style="font-family:var(--itim);" >|</p>    
                                    <div class="std-name" style="font-family:var(--itim);"><?php echo $row['student_name']; ?></div>
                                </div>
                                
                                <div class="counsellor" style="font-family:var(--itim);">Counselor: <?php echo $row['counselor_name']; ?></div>
                                <div class="booking-date" style="font-family:var(--itim);">
                                    <?php echo $row['booking_date']; ?>,
                                    <?php echo date("H:i", strtotime($row['booking_start_time'])); ?>
                                    - <?php echo date("H:i", strtotime($row['booking_end_time'])); ?>
                                </div>
                            </div>
                            <div class="btn" style="font-family:var(--itim);">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <button type="submit" name="action" value="approve" class="Approve" style="background-color:#08C97E; border-radius:8px; width:70px;height:25px;font-family:var(--itim);">Approve</button>
                                </form>
                                <button class="Reject" style="background-color:#FF6969; border-radius:8px; width:70px;height:25px;font-family:itim;" 
                                        onclick="showRejectBox('<?php echo $row['booking_id']; ?>','<?php echo $row['student_name']; ?>','<?php echo $row['student_id']; ?>')" style="font-family:var(--itim);">Reject</button>
                            </div>
                        </div>
                        <hr>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="calender" style="font-family:var(--itim);height:650px;">
                <div class="cal" style="font-family:var(--itim);">
                    <div class="calendar-controls" style="font-family:var(--itim);">
                        <label for="month" style="font-family:var(--itim);"> Month:</label>
                        <select id="month" onchange="renderCalendar()" style="font-family:var(--itim);">
                            <?php for($m=1;$m<=12;$m++): ?>
                                <option value="<?php echo $m; ?>" <?php if($m==date("n")) echo "selected"; ?> style="font-family:var(--itim);">
                                    <?php echo date("F", mktime(0,0,0,$m,1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <label for="year" style="font-family:var(--itim);">Year:</label>
                        <select id="year" onchange="renderCalendar()" style="font-family:var(--itim);">
                            <?php for($y=date("Y")-2; $y<=date("Y")+2; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php if($y==date("Y")) echo "selected"; ?> style="font-family:var(--itim);">
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div id="calendar-container" style="padding-left:25px;padding-bottom:1px;padding-right:25px;font-family:itim;"></div>
                </div>
                <div id="appointment-results" class="results" style="border-radius:30px;font-family:var(--itim);">
                    <p style="padding-top:10px;font-family:var(--itim);">Select a date from the calendar to see appointments.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($popupMsg)): ?>
        <script>alert("<?php echo $popupMsg; ?>");</script>
    <?php endif; ?>

    <script>
    function showRejectBox(bookingId, studentName, studentId){
        if(document.getElementById("reject-box-"+bookingId)) return;

        const container = document.getElementById("booking-"+bookingId);
        const rejectBox = document.createElement("div");
        rejectBox.classList.add("reject-box");
        rejectBox.id = "reject-box-"+bookingId;

        rejectBox.innerHTML = `
            <div class="student" style="font-family:var(--itim);">
                <p style="font-family:var(--itim);"><b style='font-family:var(--itim);'>Student Name:</b> ${studentName}</p>
                <p style="font-family:var(--itim);"><b style='font-family:var(--itim);'>Student ID:</b> ${studentId}</p>
            </div>
            <form method="POST">
                <input type="hidden" name="booking_id" value="${bookingId}" style="font-family:var(--itim);">
                <textarea name="message" placeholder="Write a message to the student..." style="font-family:var(--itim);" required></textarea>
                <br>
                <button type="button" class="back-btn" onclick="document.getElementById('reject-box-${bookingId}').remove()" style="font-family:var(--itim);">Back</button>
                <button type="submit" name="action" value="notify_student" class="notify-btn"style="font-family:var(--itim);">Notify Student</button>
            </form>
        `;

        container.insertAdjacentElement("afterend", rejectBox);
    }

    function renderCalendar() {
        const month = parseInt(document.getElementById("month").value);
        const year = parseInt(document.getElementById("year").value);

        fetch(`fetch_monthly_appointments.php?month=${month}&year=${year}`)
        .then(resp => resp.json())
        .then(appointments => {
            const firstDay = new Date(year, month-1, 1);
            const lastDay = new Date(year, month, 0);
            const startDay = firstDay.getDay();
            const totalDays = lastDay.getDate();

            const appointmentDays = new Set();
            for(const counselor in appointments){
                appointments[counselor].forEach(a => {
                    const day = new Date(a.date).getDate();
                    appointmentDays.add(day);
                });
            }

            let html = "<table class='calendar' ><thead><tr>";
            const days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
            for(let d of days){ html += "<th>"+d+"</th>"; }
            html += "</tr></thead><tbody><tr>";

            let cell = 0;
            for(let i=0;i<startDay;i++){
                html += "<td  class='empty' style='font-family:var(--itim);'></td>";
                cell++;
            }

            for(let day=1; day<=totalDays; day++){
                if(cell % 7 === 0 && cell !== 0) html += "</tr><tr>";
                const hasApp = appointmentDays.has(day);
                const cssClass = hasApp ? "has-appointment" : "";
                html += `<td class="${cssClass}" onclick="loadDayAppointments(${day}, ${month}, ${year})">${day}</td>`;
                cell++;
            }

            while(cell % 7 !== 0){
                html += "<td class='empty' style='font-family:var(--itim);'></td>";
                cell++;
            }
            html += "</tr></tbody></table>";

            document.getElementById("calendar-container").innerHTML = html;
        });
    }

    function loadDayAppointments(day, month, year){
        fetch(`fetch_day_appointments.php?day=${day}&month=${month}&year=${year}`)
        .then(resp => resp.json())
        .then(data => {
            const container = document.getElementById("appointment-results");
            container.innerHTML = `<h3 style="font-family:var(--itim);">Appointments for ${day}/${month}/${year}</h3>`;

            if(Object.keys(data).length === 0){
                container.innerHTML += '<p style="font-family:var(--itim);">No appointments found for this day.</p>';
            } else {
                for(const counselor in data){
                    container.innerHTML += `<div class="counselor-block" style="font-family:var(--itim);"><h4 style="font-family:var(--itim);">${counselor}</h4>`;
                    data[counselor].forEach(app => {
                        container.innerHTML += `<p style="font-family:var(--itim);">${app.time}: ${app.student_name} (${app.student_id})</p>`;
                    });
                    container.innerHTML += "</div>";
                }
            }
        });
    }

    renderCalendar();
    </script>
</body>
</html>
