<?php
session_start();
include('condb.php');
$m_id = $_SESSION['m_id'];
$m_level = $_SESSION['m_level'];

if ($m_level != 'staff') {
  Header("Location: logout.php");
}

// query member login
$queryemp = "SELECT * FROM tbl_emp WHERE m_id=$m_id";
$resultm = mysqli_query($condb, $queryemp) or die("Error in query: $queryemp " . mysqli_error($condb));
$rowm = mysqli_fetch_array($resultm);

// เวลาปัจจุบัน
$timenow = date('H:i:s');
$datenow = date('Y-m-d');

// เวลาที่บันทึก
$queryworkio = "SELECT MAX(workdate) as lastdate, workin, workout FROM tbl_work_io WHERE m_id=$m_id AND workdate='$datenow' ";
$resultio = mysqli_query($condb, $queryworkio) or die("Error in query: $queryworkio " . mysqli_error($condb));
$rowio = mysqli_fetch_array($resultio);

// ตรวจสอบค่าว่างหรือ NULL
$lastdate = isset($rowio['lastdate']) && !empty($rowio['lastdate']) ? $rowio['lastdate'] : null;
$workin = isset($rowio['workin']) && !empty($rowio['workin']) ? $rowio['workin'] : null;
$workout = isset($rowio['workout']) && !empty($rowio['workout']) ? $rowio['workout'] : null;
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
    <div class="row " style="background-color: #7109c0;">
      <div class="col col-sm-3">
        <img src="img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
      </div>

      <div class="col col-sm-9 d-flex justify-content-center align-items-center">
        <h3 class="text-center" align="center" style="color: white;">ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา
        </h3>
      </div>
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
      </div>
      <div class="col col-sm-9">
        <br>
        <h3> ลงเวลาเข้า-ออกปฏิบัติงาน <?php echo date('d-m-Y'); ?></h3>
        <form action="save.php" method="post" class="form-horizontal">
          <div class="form-group row">
            <div class="col col-sm-2">
              <label for="m_id">รหัสบุคลากร</label>
              <input type="text" class="form-control" name="m_id" placeholder="รหัสพนักงาน"
                value="<?php echo $rowm['m_id']; ?>" readonly>
            </div>
            <div class="col col-sm-3">
              <label for="m_id">เวลาเข้างาน</label>
              <?php if ($workin) { ?>
                <input type="text" class="form-control" name="workin" value="<?php echo $workin; ?>" disabled>
              <?php } else { ?>
                <input type="text" class="form-control" name="workin" value="<?php echo date('H:i:s'); ?>" readonly>
              <?php } ?>
            </div>
            <div class="col col-sm-3">
              <label for="m_id">เวลาออกงาน</label>
              <?php
              if ($timenow > '15:00:00') {
                if ($workout) { ?>
                  <input type="text" class="form-control" name="workout" value="<?php echo $workout; ?>" disabled>
                <?php } else { ?>
                  <input type="text" class="form-control" name="workout" value="<?php echo date('H:i:s'); ?>" readonly>
                  <?php
                }
              } else {
                echo '<br><font color="red"> หลัง 15.00 น. </font>';
              }
              ?>
            </div>
            <div class="col col-sm-3">
              <label>-</label>
              <button type="submit" class="btn btn-success">บันทึกเวลาเข้า-ออกปฏิบัติงาน</button>
            </div>
          </div>
        </form>

        <h3>ประวัติบันทึกเวลาปฏิบัติงาน</h3>

        <!-- ฟอร์มกรองข้อมูลตามช่วงวันที่ -->
        <form action="" method="get">
          <div class="form-group row">
            <div class="col col-sm-3 ">
              <label for="start_date">เลือกวันที่เริ่มต้น:</label>
              <input type="date" class="form-control" name="start_date"
                value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
            </div>
            <div class="col col-sm-3">
              <label for="end_date">เลือกวันที่สิ้นสุด:</label>
              <input type="date" class="form-control" name="end_date"
                value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            </div>
            <div class="col col-sm-3 d-flex align-items-end">
              <button type="submit" class="btn btn-primary">ค้นหา</button>
              <!-- ปุ่มรีเซ็ทเพื่อล้างค่าในฟอร์ม -->
              <button type="reset" class="btn btn-secondary ml-2" onclick="resetForm()">รีเซ็ท</button>
            </div>
          </div>

          <script>
            // ฟังก์ชั่นรีเซ็ทฟอร์มเพื่อทำการล้างข้อมูล
            function resetForm() {
              // คืนค่าการค้นหาเป็นค่าว่าง
              window.location.href = window.location.pathname; // รีเฟรชหน้าปัจจุบัน
            }
          </script>




        </form>

        <?php
        // รับค่าช่วงวันที่ที่กรอง
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        // คำสั่ง SQL ที่กรองตามวันที่
        $querylist = "SELECT * FROM tbl_work_io WHERE m_id = $m_id";

        if ($start_date && $end_date) {
          $querylist .= " AND workdate BETWEEN '$start_date' AND '$end_date'";
        } elseif ($start_date) {
          $querylist .= " AND workdate >= '$start_date'";
        } elseif ($end_date) {
          $querylist .= " AND workdate <= '$end_date'";
        }

        $querylist .= " ORDER BY workdate DESC";
        $resultlist = mysqli_query($condb, $querylist) or die("Error:" . mysqli_error($condb));

        // ตัวแปรนับจำนวนสถานะ
        $absentCount = 0;
        $lateCount = 0;
        $normalCount = 0;

        echo "
          <table class='table table-bordered' style='background-color:#ffffff;'>
          <thead>
            <tr class='table-Active'>
              <td>วันที่</td>
              <td>เวลาเข้างาน</td>
              <td>เวลาออกงาน</td>
              <td>สถานะ</td>
            </tr>
          </thead>
          ";

        foreach ($resultlist as $value) {
          echo "<tr>";

          // แปลงวันที่จาก 'YYYY-MM-DD' เป็น 'DD-MM-YYYY'
          $formattedDate = date('d-m-Y', strtotime($value["workdate"]));
          echo "<td>" . $formattedDate . "</td>";

          echo "<td>" . $value["workin"] . "</td>";
          echo "<td>" . $value["workout"] . "</td>";

          // ตรวจสอบสถานะ
          if (is_null($value["workin"])) {
            echo "<td><span style='color: orange;'>ขาด</span></td>";
            $absentCount++; // เพิ่มจำนวนการขาด
          } elseif ($value["workin"] > '08:30:00') {
            echo "<td><span style='color: red;'>สาย</span></td>";
            $lateCount++; // เพิ่มจำนวนการสาย
          } else {
            echo "<td><span style='color: green;'>ปกติ</span></td>";
            $normalCount++; // เพิ่มจำนวนการปกติ
          }

          echo "</tr>";
        }

        echo '</table>';
        ?>

        

      </div>
    </div>
  </div>

  <!-- Optional JavaScript -->
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