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

// รับค่าการค้นหาชื่อจากฟอร์ม (ถ้ามี)
$searchName = isset($_POST['searchName']) ? trim($_POST['searchName']) : '';

// รับค่าเดือนและปีจากฟอร์ม (ถ้ามี)
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// ดึงข้อมูลบุคลากรและข้อมูลการทำงาน
$querylist = "
    SELECT 
    e.m_firstname, 
    e.m_name, 
    e.m_lastname, 
    e.m_position,
    w.workdate, 
    w.workin, 
    w.workout, 
    w.status 
FROM tbl_emp e
LEFT JOIN tbl_work_io w ON e.m_id = w.m_id
WHERE e.m_level != 'admin'
";

// ถ้ามีการค้นหาชื่อ ให้เพิ่มเงื่อนไขใน SQL
if (!empty($searchName)) {
    $querylist .= " AND (e.m_firstname LIKE '%$searchName%' OR e.m_name LIKE '%$searchName%' OR e.m_lastname LIKE '%$searchName%')";
}

// ถ้ามีการเลือกเดือนและปี ให้เพิ่มเงื่อนไขใน SQL
if (!empty($month) && !empty($year)) {
    $querylist .= " AND MONTH(w.workdate) = '$month' AND YEAR(w.workdate) = '$year'";
}

$querylist .= " ORDER BY e.m_firstname, e.m_lastname, w.workdate DESC";
$resultlist = mysqli_query($condb, $querylist) or die("Error in query: $querylist " . mysqli_error($condb));

// จัดกลุ่มข้อมูลตามชื่อบุคลากร
$groupedData = [];
while ($row = mysqli_fetch_assoc($resultlist)) {
    $fullname = $row['m_firstname'] . " " . $row['m_name'] . " " . $row['m_lastname'];
    $groupedData[$fullname][] = $row;
}


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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

            <div class="col col-sm-8 d-flex justify-content-center align-items-center">
                <h2 class="text-center" align="center" style="color: white;">
                    ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</h2>
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
                <div class="alert alert-info mt-3">
                    <strong>คำอธิบายสถานะ</strong><br>
                    <span style="color: green;">มา</span>: บันทึกเวลาเข้าและออกถูกต้องตามเวลาที่กำหนด<br>
                    <span style="color: orange;">ขาด</span>: ไม่มีการบันทึกเวลาเข้าและออกในวันนั้น<br>
                    <span style="color: blue;">ไม่สมบูรณ์</span>: มีการบันทึกเวลาเข้าแต่ไม่มีการบันทึกเวลาออก<br>
                    <span style="color: red;">สาย</span>: บันทึกเวลาเข้าเกินเวลาที่กำหนด (หลัง 08:30 น.)<br>
                    <span style="color: gray;">หยุด</span>: วันหยุด ไม่มีการบันทึกเวลา
                </div>
            </div>
            <div class="col col-sm-9">
                <br>
                <h3>ประวัติบันทึกเวลาปฏิบัติงานของบุคลากร</h3>

                <!-- ฟอร์มค้นหา -->
                <form method="POST" action="admin.php" class="mb-4">
                    <div class="form-group">
                        <label for="searchName">ค้นหาชื่อบุคลากร:</label>
                        <input type="text" name="searchName" id="searchName" class="form-control"
                            placeholder="กรอกชื่อหรือสกุลบุคลากร" value="<?php echo htmlspecialchars($searchName); ?>">
                    </div>
                    <div class="row">
                        <div class="form-group col col-sm-3">
                            <label for="month">เลือกเดือน:</label>
                            <select name="month" id="month" class="form-control">
                                <option value="">เลือกเดือน</option>
                                <option value="01" <?php echo isset($_POST['month']) && $_POST['month'] == '01' ? 'selected' : ''; ?>>มกราคม</option>
                                <option value="02" <?php echo isset($_POST['month']) && $_POST['month'] == '02' ? 'selected' : ''; ?>>กุมภาพันธ์</option>
                                <option value="03" <?php echo isset($_POST['month']) && $_POST['month'] == '03' ? 'selected' : ''; ?>>มีนาคม</option>
                                <option value="04" <?php echo isset($_POST['month']) && $_POST['month'] == '04' ? 'selected' : ''; ?>>เมษายน</option>
                                <option value="05" <?php echo isset($_POST['month']) && $_POST['month'] == '05' ? 'selected' : ''; ?>>พฤษภาคม</option>
                                <option value="06" <?php echo isset($_POST['month']) && $_POST['month'] == '06' ? 'selected' : ''; ?>>มิถุนายน</option>
                                <option value="07" <?php echo isset($_POST['month']) && $_POST['month'] == '07' ? 'selected' : ''; ?>>กรกฎาคม</option>
                                <option value="08" <?php echo isset($_POST['month']) && $_POST['month'] == '08' ? 'selected' : ''; ?>>สิงหาคม</option>
                                <option value="09" <?php echo isset($_POST['month']) && $_POST['month'] == '09' ? 'selected' : ''; ?>>กันยายน</option>
                                <option value="10" <?php echo isset($_POST['month']) && $_POST['month'] == '10' ? 'selected' : ''; ?>>ตุลาคม</option>
                                <option value="11" <?php echo isset($_POST['month']) && $_POST['month'] == '11' ? 'selected' : ''; ?>>พฤศจิกายน</option>
                                <option value="12" <?php echo isset($_POST['month']) && $_POST['month'] == '12' ? 'selected' : ''; ?>>ธันวาคม</option>
                            </select>
                        </div>

                        <div class="form-group col col-sm-3">
                            <label for="year">เลือกปี:</label>
                            <select name="year" id="year" class="form-control">
                                <?php
                                $currentYear = date('Y');
                                for ($yearOption = $currentYear; $yearOption >= 2000; $yearOption--) {
                                    echo "<option value='$yearOption' " . (isset($_POST['year']) && $_POST['year'] == $yearOption ? 'selected' : '') . ">$yearOption</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-primary mr-2">ค้นหา</button>
                        <button type="button" class="btn btn-secondary mr-2"
                            onclick="window.location.href = 'admin.php';">รีเซ็ท</button>

                        <div class="col d-flex justify-content-end">
                            <div>
                            <a href="add_status.php" class="btn btn-info">เพิ่มสถานะการลางาน</a>
                            </div>

                            <a href="export_pdf.php?searchName=<?php echo urlencode($searchName); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                class="btn btn-success">รายงานPDF</a>
                        </div>
                    </div>
                </form>

                <!-- ตารางข้อมูล -->
                <?php if (!empty($groupedData)): ?>
                    <?php foreach ($groupedData as $fullname => $records): ?>
                        <h4><br><?php echo $fullname; ?> <br>กลุ่มสาระการเรียนรู้: <?php echo $records[0]['m_position']; ?></h4>

                        <table class="table table-bordered" style="background-color:#ffffff;">
                            <thead>
                                <tr class="table-Active">
                                    <td>วันที่</td>
                                    <td>เวลาเข้างาน</td>
                                    <td>เวลาออกงาน</td>
                                    <td>สถานะ</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $value): ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y', strtotime($value["workdate"])); ?></td>
                                        <td><?php echo (!empty($value["workin"]) && $value["workin"] != "00:00:00") ? $value["workin"] : "-"; ?>
                                        </td>
                                        <td><?php echo (!empty($value["workout"]) && $value["workout"] != "00:00:00") ? $value["workout"] : "-"; ?>
                                        </td>
                                        <td>
                                            <?php
                                            // แสดงสถานะจากฐานข้อมูล
                                            $status = $value['status'];
                                            switch ($status) {
                                                case 'ขาด':
                                                    echo "<span style='color: orange;'>ขาด</span>";
                                                    break;
                                                case 'ไม่สมบูรณ์':
                                                    echo "<span style='color: blue;'>ไม่สมบูรณ์</span>";
                                                    break;
                                                case 'สาย':
                                                    echo "<span style='color: red;'>สาย</span>";
                                                    break;
                                                case 'มา':
                                                    echo "<span style='color: green;'>มา</span>";
                                                    break;
                                                case 'ลากิจ':
                                                    echo "<span style='color: purple;'>ลากิจ</span>";
                                                    break;
                                                case 'ลาป่วย':
                                                    echo "<span style='color: pink;'>ลาป่วย</span>";
                                                    break;
                                                case 'ราชการ':
                                                    echo "<span style='color: black;'>ราชการ</span>";
                                                    break;
                                                default:
                                                    echo "<span style='color: gray;'>ไม่ทราบสถานะ</span>";
                                            }
                                            ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p>ไม่พบข้อมูลที่ค้นหา</p>
                <?php endif; ?>
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