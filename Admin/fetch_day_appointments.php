<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$admin_id = $_SESSION['admin_id'];
$day   = intval($_GET['day']);
$month = intval($_GET['month']);
$year  = intval($_GET['year']);

// find school_id
$getSchool = $dbConn->prepare("SELECT school_id FROM admin WHERE admin_id=?");
$getSchool->bind_param("s", $admin_id);
$getSchool->execute();
$res = $getSchool->get_result();
$school = $res->fetch_assoc();
$school_id = $school['school_id'];
$getSchool->close();

$sql = $dbConn->prepare("
    SELECT c.counselor_name, s.student_id, s.student_name, 
           DATE_FORMAT(b.booking_start_time, '%H:%i') AS start_time,
           DATE_FORMAT(b.booking_end_time, '%H:%i')   AS end_time
    FROM booking b
    JOIN student s ON b.student_id = s.student_id
    JOIN counselor c ON b.counselor_id = c.counselor_id
    WHERE c.school_id=? 
      AND DAY(b.booking_date)=? 
      AND MONTH(b.booking_date)=? 
      AND YEAR(b.booking_date)=?
      AND b.booking_status='approved'
    ORDER BY b.booking_start_time
");
$sql->bind_param("iiii", $school_id, $day, $month, $year);
$sql->execute();
$res = $sql->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $counselor = $row['counselor_name'];
    if (!isset($data[$counselor])) {
        $data[$counselor] = [];
    }
    $data[$counselor][] = [
        "student_id"   => $row['student_id'],
        "student_name" => $row['student_name'],
        "time"         => $row['start_time'] . " - " . $row['end_time']
    ];
}
$sql->close();

header('Content-Type: application/json');
echo json_encode($data);
