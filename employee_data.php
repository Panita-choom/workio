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

// ดึงข้อมูลจาก session
$m_id = $_SESSION['m_id'];
$m_level = $_SESSION['m_level'];

// คิวรีข้อมูลของผู้ใช้ที่ล็อกอิน
$querym = "SELECT * FROM tbl_emp WHERE m_id = $m_id";
$resultm = mysqli_query($condb, $querym) or die("Error in query: $querym " . mysqli_error($condb));

// ตรวจสอบว่ามีข้อมูลหรือไม่
$rowm = mysqli_fetch_array($resultm);
if (!$rowm) {
    // หากไม่พบข้อมูลให้ทำการ redirect หรือแสดงข้อความ error
    echo "ไม่พบข้อมูลผู้ใช้!";
    exit;
}

// คิวรีข้อมูลบุคลากรทั้งหมด
$queryemp = "SELECT * FROM tbl_emp";
$resultemp = mysqli_query($condb, $queryemp) or die("Error in query: $queryemp " . mysqli_error($condb));

?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</title>
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
                        <h3 > ข้อมูลบุคลากร </h3>
                        <a href="employee_add.php" class="btn btn-success btn-sm" style="margin-top: 10px; margin-bottom: 10px;">เพิ่มข้อมูลบุคลากร</a>

                        <br>
                
                        <table class='table table-bordered' style='background-color:#ffffff;'
                            <thead>
                            <tr class='table-Primary' >
                                    <th>รหัสบุคลากร</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>คำนำหน้า</th>
                                    <th>ชื่อ</th>
                                    <th>นามสกุล</th>
                                    <th>กลุ่มสาระการเรียนรู้</th>
                                    <th>ประเภท</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($rowemp = mysqli_fetch_array($resultemp)) { ?>
                                    <tr>
                                        <td><?php echo $rowemp['m_id']; ?></td>
                                        <td><?php echo $rowemp['m_username']; ?></td>
                                        <td><?php echo $rowemp['m_firstname']; ?></td>
                                        <td><?php echo $rowemp['m_name']; ?></td>
                                        <td><?php echo $rowemp['m_lastname']; ?></td>
                                        <td><?php echo $rowemp['m_position']; ?></td>
                                        <td><?php echo $rowemp['m_level']; ?></td>
                                        <td>
                                            <a href="employee_edit.php?id=<?php echo $rowemp['m_id']; ?>"
                                                class="btn btn-warning btn-sm">แก้ไข</a>
                                            <a href="employee_delete.php?id=<?php echo $rowemp['m_id']; ?>"
                                                class="btn btn-danger btn-sm">ลบ</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
</body>

</html>