<?php
session_start();
include '../conn.php';

if(!isset($_SESSION['admin_id'])){
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$admin_id = $_SESSION['admin_id'];
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

// fetch counselor + appointments in this month
$sql = $dbConn->prepare("
    SELECT c.counselor_name, b.booking_date
    FROM booking b
    JOIN counselor c ON b.counselor_id = c.counselor_id
    WHERE c.school_id=? 
      AND MONTH(b.booking_date)=? 
      AND YEAR(b.booking_date)=?
      AND b.booking_status='approved'
");
$sql->bind_param("iii", $school_id, $month, $year);
$sql->execute();
$res = $sql->get_result();

$data = [];
while($row = $res->fetch_assoc()){
    $data[$row['counselor_name']][] = [
        "date" => $row['booking_date']
    ];
}
$sql->close();

header('Content-Type: application/json');
echo json_encode($data);
