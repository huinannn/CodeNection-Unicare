<?php
    session_start();
    include '../../conn.php';

    $error = $_SESSION['error'] ?? '';
    $success = $_SESSION['success'] ?? '';
    $prev_student_id = $_SESSION['prev_student_id'] ?? '';
    $prev_school_id = $_SESSION['prev_school_id'] ?? '';
    $prev_password = $_SESSION['prev_password'] ?? '';
    unset($_SESSION['error'], $_SESSION['success'], $_SESSION['prev_student_id'], $_SESSION['prev_school_id'], $_SESSION['prev_password']);

    $schools = mysqli_query($dbConn, "SELECT * FROM school");

    if (isset($_POST['login'])) {
        $student_id = trim($_POST['student_id']);
        $student_password = trim($_POST['student_password']);
        $school_id = (int)$_POST['school_id'];

        $query = "SELECT * FROM student WHERE student_id=? AND student_password=? AND school_id=?";
        $stmt = $dbConn->prepare($query);
        $stmt->bind_param("ssi", $student_id, $student_password, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $student = $result->fetch_assoc();

            date_default_timezone_set('Asia/Kuala_Lumpur');
            $date = date('Y-m-d');
            $time = date('H:i:s');

            $update_sql = "UPDATE student 
                        SET last_login_time=?, last_login_date=?, student_account_status='active' 
                        WHERE student_id=?";
            $update_stmt = $dbConn->prepare($update_sql);
            $update_stmt->bind_param("sss", $time, $date, $student['student_id']);
            $update_stmt->execute();

            $insert_sql = "INSERT INTO login (login_date, login_time, student_id) VALUES (?, ?, ?)";
            $insert_stmt = $dbConn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $date, $time, $student['student_id']);
            $insert_stmt->execute();

            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['student_name'];
            $_SESSION['school_id'] = $student['school_id'];
            $_SESSION['success'] = "Login successful.";

            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid credentials or school.";
            $_SESSION['prev_student_id'] = $student_id;
            $_SESSION['prev_school_id'] = $school_id;
            $_SESSION['prev_password'] = $student_password;
            header("Location: tryagain.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Unicare</title>
    <link rel="icon" href="../../image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="login.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div id="snackbar"><span></span></div>

    <div class="login">
        <img class="picture" src="../../image/logo.png" alt="">
        <div class="text"><span>Login</span></div>

        <form method="POST" action="login.php" id="loginForm">
            <div class="form">
                <div class="input-label">
                    <label>Student ID</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input name="student_id" type="text" placeholder="Enter your student ID" value="<?= htmlspecialchars($prev_student_id) ?>">
                    </div>
                </div>
                
                <div class="input-label">
                    <label>School Name</label>
                    <div class="input-icon">
                        <i class="fas fa-building"></i>
                        <select name="school_id">
                            <option value="" disabled selected hidden>Select your school</option>
                            <?php while($row = mysqli_fetch_assoc($schools)): ?>
                                <option value="<?= $row['school_id'] ?>" <?= ($prev_school_id==$row['school_id'])?'selected':'' ?>>
                                    <?= $row['school_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="input-label">
                    <label>Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input name="student_password" type="password" placeholder="Enter your password" value="<?= htmlspecialchars($prev_password) ?>">
                    </div>
                </div>

                <div class="button-row">
                    <button type="reset" class="btn btn-1">Reset</button>
                    <button type="submit" name="login" class="btn btn-2">Login</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        window.ERROR = "<?= $error ?>";
        window.SUCCESS = "<?= $success ?>";
    </script>
    <script src="login.js"></script>
</body>
</html>