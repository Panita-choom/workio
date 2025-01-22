<?php
// เริ่มต้น session และเชื่อมต่อฐานข้อมูล
session_start();
include("condb.php");

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $m_username = mysqli_real_escape_string($condb, $_POST['m_username']);
    $m_password = mysqli_real_escape_string($condb, sha1($_POST['m_password'])); // เข้ารหัสด้วย SHA1
    $m_firstname = mysqli_real_escape_string($condb, $_POST['m_firstname']);
    $m_name = mysqli_real_escape_string($condb, $_POST['m_name']);
    $m_lastname = mysqli_real_escape_string($condb, $_POST['m_lastname']);
    $m_position = mysqli_real_escape_string($condb, $_POST['m_position']);
    $m_level = mysqli_real_escape_string($condb, $_POST['m_level']);

    // SQL เพิ่มข้อมูล
    $sql = "INSERT INTO tbl_emp (m_username, m_password, m_firstname, m_name, m_lastname, m_position, m_level) 
            VALUES ('$m_username', '$m_password', '$m_firstname', '$m_name', '$m_lastname', '$m_position', '$m_level')";

    // ตรวจสอบการเพิ่มข้อมูล
    if (mysqli_query($condb, $sql)) {
        echo "<script>alert('เพิ่มข้อมูลบุคลากรสำเร็จ'); window.location='employee_data.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาด: " . mysqli_error($condb);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลบุคลากร</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body style="background-color:rgb(250, 238, 255);">
    <div class="container">
        <div class="row" style="background-color: #7109c0;">
            <div class="col col-sm-3">
                <img src="img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
            </div>

            <div class="col col-sm-9 d-flex justify-content-center align-items-center">
                <h3 class="text-center" align="center" style="color: white;">
                    ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</h3>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col col-sm-2">
                <br>
                <b>ผู้ดูแลระบบ</b>
                <br>
                <a href="logout.php" class="btn btn-danger btn-sm mb-3">LOGOUT</a>
                <b>
                    <a href="admin.php" class="btn btn-outline-primary d-flex text-left">ประวัติบันทึกเวลางาน</a>
                    <a href="employee_data.php" class="btn btn-outline-primary d-flex text-left">ข้อมูลบุคลากร</a>
                </b>
            </div>
            <div class="col col-sm-10">
                <br>
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <h3>แก้ไขข้อมูลบุคลากร</h3>
                    </div>
                    <div class="col text-right">
                        <a href="employee_data.php" class="btn btn-secondary">กลับ</a>
                    </div>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="m_username">ชื่อผู้ใช้:</label>
                        <input type="text" name="m_username" id="m_username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_password">รหัสผ่าน:</label>
                        <input type="password" name="m_password" id="m_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_firstname">คำนำหน้า:</label>
                        <input type="text" name="m_firstname" id="m_firstname" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_name">ชื่อ:</label>
                        <input type="text" name="m_name" id="m_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_lastname">นามสกุล:</label>
                        <input type="text" name="m_lastname" id="m_lastname" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_position">ตำแหน่ง:</label>
                        <input type="text" name="m_position" id="m_position" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="m_level">ประเภทผู้ใช้งาน:</label>
                        <select name="m_level" id="m_level" class="form-control" required>

                            <option value="staff">ผู้ใช้</option>
                            <option value="admin">ผู้ดูแลระบบ</option>

                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>

                </form>
            </div>
</body>

</html>