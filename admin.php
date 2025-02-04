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

// รับค่า startDate และ endDate จากฟอร์ม (ถ้ามี)
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

// ดึงข้อมูลบุคลากรและข้อมูลการทำงาน
$querylist = "
    SELECT 
        e.m_firstname, 
        e.m_name, 
        e.m_lastname, 
        e.m_position,
        w.workdate, 
        w.workin, 
        w.workout 
    FROM tbl_emp e
    LEFT JOIN tbl_work_io w ON e.m_id = w.m_id
    WHERE e.m_level != 'admin'
";

// ถ้ามีการค้นหาชื่อ ให้เพิ่มเงื่อนไขใน SQL
if (!empty($searchName)) {
    $querylist .= " AND (e.m_firstname LIKE '%$searchName%' OR e.m_name LIKE '%$searchName%' OR e.m_lastname LIKE '%$searchName%')";
}

// ถ้ามีการเลือกวันที่เริ่มต้นและสิ้นสุด ให้เพิ่มเงื่อนไขใน SQL
if (!empty($startDate) && !empty($endDate)) {
    $querylist .= " AND w.workdate BETWEEN '$startDate' AND '$endDate'";
} elseif (!empty($startDate)) {
    $querylist .= " AND w.workdate >= '$startDate'";
} elseif (!empty($endDate)) {
    $querylist .= " AND w.workdate <= '$endDate'";
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

            <div class="col col-sm-9 d-flex justify-content-center align-items-center">
                <h3 class="text-center" align="center" style="color: white;">
                    ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</h3>
            </div>
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
                            <label for="startDate">เลือกช่วงเวลาจาก:</label>
                            <input type="date" name="startDate" id="startDate" class="form-control"
                                value="<?php echo isset($_POST['startDate']) ? $_POST['startDate'] : ''; ?>">
                        </div>

                        <div class="form-group col col-sm-3">
                            <label for="endDate">ถึง:</label>
                            <input type="date" name="endDate" id="endDate" class="form-control"
                                value="<?php echo isset($_POST['endDate']) ? $_POST['endDate'] : ''; ?>">
                                
                        </div>
                        
                    </div>
                    <div class="form-group d-flex">
                    <button type="submit" class="btn btn-primary mr-2">ค้นหา</button>
                        <button type="button" class="btn btn-secondary mr-2"
                            onclick="window.location.href = 'admin.php';">รีเซ็ท</button>
                        <div class="col  d-flex justify-content-end">
                            <a href="export_pdf.php?searchName=<?php echo urlencode($searchName); ?>&startDate=<?php echo $startDate; ?>&endDate=<?php echo $endDate; ?>"
                                class="btn btn-success">รายงานPDF</a>
                        </div>
                    </div>
                </form>

                <!-- ตารางข้อมูล -->
                <?php if (!empty($groupedData)): ?>
                    <?php foreach ($groupedData as $fullname => $records): ?>
                        <h4><?php echo $fullname; ?> (กลุ่มสาระการเรียนรู้: <?php echo $records[0]['m_position']; ?>)</h4>

                        <?php
                        // Initialize counters for statuses
                        $lateCount = 0;
                        $normalCount = 0;
                        $absentCount = 0;

                        // Loop through records and count statuses
                        foreach ($records as $value) {
                            if (is_null($value["workin"])) {
                                $absentCount++;
                            } elseif ($value["workin"] > '08:30:00') {
                                $lateCount++;
                            } else {
                                $normalCount++;
                            }
                        }
                        ?>

                        <p>สถานะการทำงาน:
                            สาย: <?php echo $lateCount; ?> ครั้ง,
                            ปกติ: <?php echo $normalCount; ?> ครั้ง,
                            ขาด: <?php echo $absentCount; ?> ครั้ง
                        </p>

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
                                        <td><?php echo date('d-m-Y', strtotime($value["workdate"])); ?></td> <!-- แปลงวันที่ -->
                                        <td><?php echo $value["workin"]; ?></td>
                                        <td><?php echo $value["workout"]; ?></td>
                                        <td>
                                            <?php
                                            if (is_null($value["workin"])) {
                                                echo "<span style='color: orange;'>ขาด</span>";
                                            } elseif ($value["workin"] > '08:30:00') {
                                                echo "<span style='color: red;'>สาย</span>";
                                            } else {
                                                echo "<span style='color: green;'>ปกติ</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">ไม่พบข้อมูลบุคลากรที่คุณค้นหา</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>