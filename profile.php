<?php
session_start();
include('condb.php');

date_default_timezone_set("Asia/Bangkok");
$datenow = date('Y-m-d');
$timenow = date('H:i:s');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] != 'staff') {
  header("Location: logout.php");
  exit();
}

$m_id = mysqli_real_escape_string($condb, $_SESSION['m_id']);

$queryemp = "SELECT * FROM tbl_emp WHERE m_id='$m_id'";
$resultm = mysqli_query($condb, $queryemp);
$rowm = mysqli_fetch_array($resultm);

$queryHoliday = "SELECT * FROM tbl_holidays WHERE holiday_date = '$datenow'";
$resultHoliday = mysqli_query($condb, $queryHoliday);
$isHoliday = mysqli_num_rows($resultHoliday) > 0;

$queryworkio = "SELECT workin, workout, status FROM tbl_work_io WHERE m_id='$m_id' AND workdate='$datenow'";
$resultio = mysqli_query($condb, $queryworkio);
$rowio = mysqli_fetch_array($resultio);

$workin = $rowio['workin'] ?? NULL;  // ✅ ถ้าไม่มีข้อมูลให้ใส่ NULL
$workout = $rowio['workout'] ?? NULL;  // ✅ ถ้าไม่มีข้อมูลให้ใส่ NULL
$status = $rowio['status'] ?? NULL;

// ✅ เช็คและบันทึก "ขาด" อัตโนมัติหากเกิน 18:01 น. และยังไม่มีบันทึก
if (mysqli_num_rows($resultio) == 0 && $timenow > '18:01:00' && !$isHoliday) {
  // แสดงข้อมูลที่กำลังจะบันทึก
  

  // สร้างคำสั่ง SQL เพื่อบันทึก "ขาด"
  $insertAbsenceQuery = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout, status) 
                           VALUES ('$m_id', '$datenow', NULL, NULL, 'ขาด')";
  $result = mysqli_query($condb, $insertAbsenceQuery);

  // ตรวจสอบผลลัพธ์การบันทึก
  if (!$result) {
    echo "Error: " . mysqli_error($condb);  // แสดงข้อผิดพลาดที่เกิดขึ้น
  } else {
    echo "บันทึกข้อมูล 'ขาด' สำเร็จ";
  }
}

// ✅ บันทึก "หยุด" อัตโนมัติหากเป็นวันหยุดและไม่มีบันทึก
if ($isHoliday && !$rowio) {
  // แสดงข้อมูลที่กำลังจะบันทึก
  echo "m_id: " . $m_id . "<br>";
  echo "datenow: " . $datenow . "<br>";

  // สร้างคำสั่ง SQL เพื่อบันทึก "หยุด"
  $insertHolidayQuery = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout, status) 
                           VALUES ('$m_id', '$datenow', NULL, NULL, 'หยุด')";
  $resultHoliday = mysqli_query($condb, $insertHolidayQuery);

  // ตรวจสอบผลลัพธ์การบันทึก
  if (!$resultHoliday) {
    echo "Error: " . mysqli_error($condb);  // แสดงข้อผิดพลาดที่เกิดขึ้น
  } else {
    echo "บันทึกข้อมูล 'หยุด' สำเร็จ";
  }
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</title>
</head>

<body style="background-color:rgb(250, 238, 255);">
  <div class="container">
    <div class="row" style="background-color:rgb(128, 21, 211);">
      <div class="col col-sm-3">
        <img src="img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
      </div>
      <div class="col col-sm-8 d-flex justify-content-center align-items-center">
        <h2 class="text-center" style="color: white;">ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</h2>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col col-sm-3">
        <br>
        <b>
          <?php echo $rowm['m_firstname'] . $rowm['m_name'] . ' ' . $rowm['m_lastname']; ?>
          <br>
          กลุ่มสาระการเรียนรู้ : <?php echo $rowm['m_position']; ?>
        </b>
        <br>
        <a href="logout.php" class="btn btn-danger btn-sm"> LOGOUT </a>
        <div class="alert alert-info mt-3">
          <strong>คำอธิบายสถานะ</strong><br>
          <span style="color: green;">มา</span>: บันทึกเวลาเข้าและออกถูกต้องตามเวลาที่กำหนด<br>
          <span style="color: orange;">ขาด</span>: ไม่มีการบันทึกเวลาเข้าและออกในวันนั้น<br>
          <span style="color: blue;">ไม่สมบูรณ์</span>: มีการบันทึกเวลาเข้าแต่ไม่มีการบันทึกเวลาออก<br>
          <span style="color: red;">สาย</span>: บันทึกเวลาเข้าเกินเวลาที่กำหนด (หลัง 08:30 น.)<br>
        </div>
      </div>

      <div class="col col-sm-9">
        <br>
        <h3> ลงเวลาเข้า-ออกปฏิบัติงาน <?php echo date('d-m-Y'); ?></h3>
        <form action="save.php" method="post" class="form-horizontal">
          <div class="form-group row">
            <div class="col col-sm-2">
              <label for="m_id">รหัสพนักงาน</label>
              <input type="text" class="form-control" name="m_id" placeholder="รหัสพนักงาน"
                value="<?php echo $rowm['m_id']; ?>" readonly>
            </div>
            <div class="col col-sm-3">
              <label for="workin">เวลาเข้างาน</label>
              <?php if (isset($rowio['workin'])) { ?>
                <input type="text" class="form-control" name="workin" value="<?php echo $rowio['workin']; ?>" disabled>
              <?php } else { ?>
                <input type="text" class="form-control" name="workin" value="<?php echo date('H:i:s'); ?>" readonly>
              <?php } ?>
            </div>
            <div class="col col-sm-3">
              <label for="workout">เวลาออกงาน</label>
              <?php if ($timenow > '15:00:00') {
                if (isset($rowio['workout'])) { ?>
                  <input type="text" class="form-control" name="workout" value="<?php echo $rowio['workout']; ?>" disabled>
                <?php } else { ?>
                  <input type="text" class="form-control" name="workout" value="<?php echo date('H:i:s'); ?>" readonly>
                <?php }
              } else {
                echo '<br><font color="red">หลัง 15.00 น.</font>';
              } ?>
            </div>
            <div class="col col-sm-3">
              <label>-</label>
              <?php if ($timenow <= '18:00:00') { ?>
                <button type="submit" class="btn btn-success">บันทึกเวลาเข้า-ออกปฏิบัติงาน</button>
              <?php } else { ?>
                <button type="button" class="btn btn-secondary" disabled>หมดเวลาบันทึก</button>
              <?php } ?>
            </div>
          </div>
        </form>


        <h3>ประวัติบันทึกเวลาปฏิบัติงาน</h3>

        <!-- ฟอร์มกรองข้อมูลตามเดือนและปี -->
        <form action="" method="get">
          <div class="form-group row">
            <div class="col col-sm-3">
              <label for="month">เลือกเดือน:</label>
              <select class="form-control" name="month">
                <?php
                $months = [
                  "01" => "มกราคม",
                  "02" => "กุมภาพันธ์",
                  "03" => "มีนาคม",
                  "04" => "เมษายน",
                  "05" => "พฤษภาคม",
                  "06" => "มิถุนายน",
                  "07" => "กรกฎาคม",
                  "08" => "สิงหาคม",
                  "09" => "กันยายน",
                  "10" => "ตุลาคม",
                  "11" => "พฤศจิกายน",
                  "12" => "ธันวาคม"
                ];
                $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
                foreach ($months as $num => $name) {
                  $selected = ($selectedMonth == $num) ? 'selected' : '';
                  echo "<option value='$num' $selected>$name</option>";
                }
                ?>
              </select>
            </div>

            <div class="col col-sm-3">
              <label for="year">เลือกปี:</label>
              <select class="form-control" name="year">
                <?php
                $queryYear = "SELECT DISTINCT YEAR(workdate) AS year FROM tbl_work_io WHERE m_id = $m_id ORDER BY year DESC";
                $resultYear = mysqli_query($condb, $queryYear);
                $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
                while ($rowYear = mysqli_fetch_assoc($resultYear)) {
                  $selected = ($selectedYear == $rowYear['year']) ? 'selected' : '';
                  echo "<option value='" . $rowYear['year'] . "' $selected>" . $rowYear['year'] . "</option>";
                }
                ?>
              </select>
            </div>

            <div class="col col-sm-3 d-flex align-items-end">
              <button type="submit" class="btn btn-info">
                <i class="fas fa-search"></i> ค้นหา
              </button>
              <button type="button" class="btn btn-warning ml-2" onclick="window.location.href = 'profile.php';">
                <i class="fas fa-sync-alt"></i> รีเซ็ต
              </button>
            </div>
          </div>
        </form>

        <h3>ประวัติการบันทึกเวลา</h3>

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
  <?php
  $month = isset($_GET['month']) ? $_GET['month'] : date('m');
  $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

  $querylist = "SELECT workdate, workin, workout, status 
  FROM tbl_work_io 
  WHERE m_id = $m_id 
  AND MONTH(workdate) = '$month' 
  AND YEAR(workdate) = '$year' 
  AND workdate <= CURDATE()
  ORDER BY workdate DESC";
  $resultlist = mysqli_query($condb, $querylist);

  while ($row = mysqli_fetch_assoc($resultlist)) {
    echo "<tr>";
    echo "<td>" . date('d-m-Y', strtotime($row['workdate'])) . "</td>";
    echo "<td>" . (($row['workin'] == "00:00:00" || is_null($row['workin'])) ? "-" : $row['workin']) . "</td>";
    echo "<td>" . (($row['workout'] == "00:00:00" || is_null($row['workout'])) ? "-" : $row['workout']) . "</td>";

    // แสดงสถานะจากฐานข้อมูล
    $status = $row['status'];
    if ($status == 'ขาด') {
      echo "<td><span style='color: orange;'>ขาด</span></td>";
    } elseif ($status == 'ไม่สมบูรณ์') {
      echo "<td><span style='color: blue;'>ไม่สมบูรณ์</span></td>";
    } elseif ($status == 'สาย') {
      echo "<td><span style='color: red;'>สาย</span></td>";
    } elseif ($status == 'มา') {
      echo "<td><span style='color: green;'>มา</span></td>";
    } elseif ($status == 'ลากิจ') {
      echo "<td><span style='color: purple;'>ลากิจ</span></td>";
    } elseif ($status == 'ลาป่วย') {
      echo "<td><span style='color: pink;'>ลาป่วย</span></td>";
    }  elseif ($status == 'ราชการ') {
      echo "<td><span style='color: black;'>ราชการ</span></td>";
    }else {
      echo "<td><span style='color: gray;'>ไม่ทราบสถานะ</span></td>";
    }
    echo "</tr>";
  }
  ?>
</tbody>


        </table>


      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>