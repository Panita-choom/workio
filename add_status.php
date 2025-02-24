<?php
// เริ่มต้น session ก่อนใช้งาน $_SESSION
session_start();

// เชื่อมต่อฐานข้อมูล
include('condb.php');

// ตรวจสอบว่า session m_id และ m_level ถูกตั้งค่าไว้หรือไม่
if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] != 'admin') {
    header("Location: admin.php");
    exit;
}

// ตรวจสอบการบันทึกสถานะ
if (isset($_POST["addStatus"])) {
    $m_id = mysqli_real_escape_string($condb, $_POST["m_id"]);
    $status = mysqli_real_escape_string($condb, $_POST["status"]);
    $workdate = mysqli_real_escape_string($condb, $_POST["workdate"]);

    // ตรวจสอบว่ามีการบันทึกสถานะในวันที่นั้น ๆ หรือไม่
    $checkQuery = "SELECT * FROM tbl_work_io WHERE m_id = '$m_id' AND workdate = '$workdate'";
    $checkResult = mysqli_query($condb, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // ถ้ามีการบันทึกแล้ว ให้แสดงข้อความแจ้งเตือน
        echo "<script>alert('มีการบันทึกสถานะในวันที่นี้แล้ว'); window.location='add_status.php';</script>";
    } else {
        // กำหนดค่าเวลาเข้าออกงานตามสถานะ
        if ($status == 'ราชการ') {
            $workin = '08:30:00';
            $workout = '15:30:00';
        } else {
            $workin = NULL;
            $workout = NULL;
        }

        // บันทึกข้อมูลสถานะใหม่
        $insertQuery = "INSERT INTO tbl_work_io (m_id, workdate, status, workin, workout) 
                        VALUES ('$m_id', '$workdate', '$status', '$workin', '$workout')";
        $insertResult = mysqli_query($condb, $insertQuery) or die("Error in query: $insertQuery " . mysqli_error($condb));

        if ($insertResult) {
            echo "<script>alert('บันทึกสถานะเรียบร้อย'); window.location='add_status.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการบันทึก'); window.location='add_status.php';</script>";
        }
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>บันทึกสถานะการลางาน</title>
</head>

<body style="background-color:rgb(250, 238, 255);">
    <div class="container">
        <div class="row" style="background-color: #7109c0;">
            <div class="col col-sm-3">
                <img src="img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
            </div>
            <div class="col col-sm-8 d-flex justify-content-center align-items-center">
                <h2 class="text-center" align="center" style="color: white;">ระบบบันทึกสถานะการลางาน</h2>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col col-sm-3">
                <br>
                <b>ผู้ดูแลระบบ</b>
                <br>
                <a href="logout.php" class="btn btn-danger btn-sm mb-3">LOGOUT</a>
                <b>
                    <a href="admin.php" class="btn btn-outline-primary d-flex text-left">ประวัติบันทึกเวลางาน</a>
                    <a href="employee_data.php" class="btn btn-outline-primary d-flex text-left">ข้อมูลบุคลากร</a>
                </b>
            </div>
            <div class="col col-sm-9">
                <br>
                <h3>บันทึกสถานะการลางาน</h3>

                <!-- ฟอร์มเพิ่มสถานะ -->
                <form method="POST" action="add_status.php">
                    <div class="form-group">
                        <label for="m_id">เลือกบุคลากร:</label>
                        <select name="m_id" id="m_id" class="form-control">
                            <?php
                            // ดึงข้อมูลบุคลากรจากฐานข้อมูล
                            $query = "SELECT m_id, m_firstname, m_name, m_lastname FROM tbl_emp WHERE m_level != 'admin'";
                            $result = mysqli_query($condb, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='".$row['m_id']."'>".$row['m_firstname']." ".$row['m_name']." ".$row['m_lastname']."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">เลือกสถานะ:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="ลากิจ">ลากิจ</option>
                            <option value="ลาป่วย">ลาป่วย</option>
                            <option value="ราชการ">ราชการ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="workdate">เลือกวันที่:</label>
                        <input type="date" name="workdate" id="workdate" class="form-control">
                    </div>

                    <button type="submit" name="addStatus" class="btn btn-success">บันทึกสถานะ</button>
                    <a href="admin.php" class="btn btn-primary ml-2">กลับสู่หน้าแรก</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE5f6XDJhbbmFuH6zUda7rmc1qSz0iEreZlUohyy2ttzqg3Am4zprY"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"
        integrity="sha384-KyZXEJQefjUuRytONcQw3jjmT0rR25Wpl9dj2f2LXaESe0hbLdoXlbmcH08BIlrS"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-VyZbxhIRlmftvTAs9iwgltjms4ZT0fRfj5lLXyKhFEAqI1qdxFGDLfEx9eRO9V8A"
        crossorigin="anonymous"></script>
</body>

</html>
