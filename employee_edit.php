<?php
// เชื่อมต่อฐานข้อมูล
include('condb.php');

// ตรวจสอบว่าได้รับ ID ของบุคลากรที่ต้องการแก้ไขหรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ดึงข้อมูลบุคลากรจากฐานข้อมูล
    $query = "SELECT * FROM tbl_emp WHERE m_id = '$id'";
    $result = mysqli_query($condb, $query);
    $employee = mysqli_fetch_assoc($result);

    if (!$employee) {
        echo "ไม่พบข้อมูลบุคลากรที่ต้องการแก้ไข";
        exit;
    }
} else {
    echo "กรุณาเลือกบุคลากรที่ต้องการแก้ไข";
    exit;
}

// เมื่อฟอร์มถูกส่งมา (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $m_username = $_POST['m_username'];

    // ตรวจสอบว่าได้กรอกรหัสผ่านหรือไม่
    $m_password = isset($_POST['m_password']) && !empty($_POST['m_password']) ? sha1($_POST['m_password']) : $employee['m_password']; // ใช้ SHA1 เมื่อกรอกรหัสใหม่

    $m_firstname = $_POST['m_firstname'];
    $m_name = $_POST['m_name'];
    $m_lastname = $_POST['m_lastname'];
    $m_position = $_POST['m_position'];
    $m_level = $_POST['m_level'];

    // คำสั่ง SQL สำหรับอัปเดตข้อมูลบุคลากร
    $updateQuery = "UPDATE tbl_emp SET m_username='$m_username', m_password='$m_password', 
                    m_firstname='$m_firstname', m_name='$m_name', m_lastname='$m_lastname', 
                    m_position='$m_position', m_level='$m_level' WHERE m_id='$id'";

    if (mysqli_query($condb, $updateQuery)) {
        echo "ข้อมูลถูกอัปเดตเรียบร้อยแล้ว";
        header("Location: employee_data.php"); // หรือหน้าอื่นๆที่ต้องการ
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($condb);
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title>แก้ไขข้อมูลบุคลากร</title>
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
                        <input type="text" name="m_username" id="m_username" class="form-control"
                            value="<?php echo htmlspecialchars($employee['m_username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="m_password">รหัสผ่าน:</label>
                        <input type="password" name="m_password" id="m_password" class="form-control" value=""
                            placeholder="กรุณากรอกรหัสผ่านใหม่ถ้าต้องการเปลี่ยนแปลง">
                        <small class="form-text text-muted">กรุณากรอกรหัสผ่านใหม่หากต้องการเปลี่ยน</small>
                    </div>

                    <div class="form-group">
                        <label for="m_firstname">คำนำหน้า:</label>
                        <input type="text" name="m_firstname" id="m_firstname" class="form-control"
                            value="<?php echo htmlspecialchars($employee['m_firstname']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="m_name">ชื่อ:</label>
                        <input type="text" name="m_name" id="m_name" class="form-control"
                            value="<?php echo htmlspecialchars($employee['m_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="m_lastname">นามสกุล:</label>
                        <input type="text" name="m_lastname" id="m_lastname" class="form-control"
                            value="<?php echo htmlspecialchars($employee['m_lastname']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="m_position">กลุ่มสาระการเรียนรู้:</label>
                        <select name="m_position" id="m_position" class="form-control" required>
                            <option value="การงานอาชีพและเทคโนโลยี" <?php echo ($employee['m_position'] == 'การงานอาชีพและเทคโนโลยี') ? 'selected' : ''; ?>>
                                กลุ่มสาระการเรียนรู้การงานอาชีพและเทคโนโลยี</option>
                            <option value="ปฐมวัย" <?php echo ($employee['m_position'] == 'ปฐมวัย') ? 'selected' : ''; ?>>
                                กลุ่มสาระการเรียนรู้ปฐมวัย</option>
                            <option value="วิชาศิลปะ" <?php echo ($employee['m_position'] == 'วิชาศิลปะ') ? 'selected' : ''; ?>>กลุ่มสาระการเรียนรู้วิชาศิลปะ</option>
                            <option value="ภาษาไทย" <?php echo ($employee['m_position'] == 'ภาษาไทย') ? 'selected' : ''; ?>>กลุ่มสาระการเรียนรู้ภาษาไทย</option>
                            <option value="ภาษาต่างประเทศ" <?php echo ($employee['m_position'] == 'ภาษาต่างประเทศ') ? 'selected' : ''; ?>>กลุ่มสาระการเรียนรู้ภาษาต่างประเทศ</option>
                            <option value="วิทยาศาสตร์" <?php echo ($employee['m_position'] == 'วิทยาศาสตร์') ? 'selected' : ''; ?>>กลุ่มสาระการเรียนรู้วิทยาศาสตร์</option>
                            <option value="สุขศึกษาและพลศึกษา" <?php echo ($employee['m_position'] == 'สุขศึกษาและพลศึกษา') ? 'selected' : ''; ?>>
                                กลุ่มสาระการเรียนรู้สุขศึกษาและพลศึกษา</option>
                            <option value="คณิตศาสตร์" <?php echo ($employee['m_position'] == 'คณิตศาสตร์') ? 'selected' : ''; ?>>กลุ่มสาระการเรียนรู้คณิตศาสตร์</option>
                            <option value="สังคมศึกษา ศาสนา และวัฒนธรรม" <?php echo ($employee['m_position'] == 'สังคมศึกษา ศาสนา และวัฒนธรรม') ? 'selected' : ''; ?>>
                                กลุ่มสาระการเรียนรู้สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                                <option value="อื่นๆ" <?php echo ($employee['m_position'] == 'อื่นๆ') ? 'selected' : ''; ?>>
                                อื่นๆ</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="m_level">ประเภท:</label>
                        <select name="m_level" id="m_level" class="form-control" required>
                            <option value="user" <?php if ($employee['m_level'] == 'user')
                                echo 'selected'; ?>>ผู้ใช้</option>
                            <option value="admin" <?php if ($employee['m_level'] == 'admin')
                                echo 'selected'; ?>>ผู้ดูแลระบบ
                            </option>

                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                </form>


            </div>
</body>

</html>