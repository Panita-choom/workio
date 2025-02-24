<meta charset="utf-8">
<?php 
include("condb.php");

$workdate = date('Y-m-d');
// บันทึกเวลาเข้า (workin)
if (isset($_POST["workin"])) {
    $m_id = mysqli_real_escape_string($condb, $_POST["m_id"]);
    $workin = mysqli_real_escape_string($condb, $_POST["workin"]);

    // บันทึกข้อมูลเวลาเข้า
    $sql = "INSERT INTO tbl_work_io (workdate, m_id, workin) VALUES ('$workdate', '$m_id', '$workin')";
    $result = mysqli_query($condb, $sql) or die ("Error in query: $sql " . mysqli_error($condb));

    mysqli_close($condb);
    if ($result) {
        echo "<script>alert('บันทึกเวลาเข้าสำเร็จ'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error! ไม่สามารถบันทึกข้อมูลได้'); window.location='profile.php';</script>";
    }
} 

// บันทึกเวลาออก (workout)
elseif (isset($_POST["workout"])) {
    $m_id = mysqli_real_escape_string($condb, $_POST["m_id"]);
    $workout = mysqli_real_escape_string($condb, $_POST["workout"]);

    // ดึงข้อมูลเวลาเข้าจากฐานข้อมูล
    $checkQuery = "SELECT workin FROM tbl_work_io WHERE m_id='$m_id' AND workdate='$workdate'";
    $checkResult = mysqli_query($condb, $checkQuery);
    $row = mysqli_fetch_array($checkResult);

    if ($row) {
        $workin = $row['workin'];
        $status = "ขาด"; // ค่าเริ่มต้น

        // คำนวณสถานะ
        if (!is_null($workin)) {
            if ($workin <= '08:30:00' && !is_null($workout)) {
                $status = "มา"; // มา
            } elseif ($workin > '08:30:00' && !is_null($workout)) {
                $status = "สาย"; // สาย
            } elseif ($workin <= '08:30:00' && is_null($workout)) {
                $status = "ไม่สมบูรณ์"; // ไม่สมบูรณ์
            }
        }

        // อัปเดตข้อมูลเวลาออกและสถานะ
        $sql2 = "UPDATE tbl_work_io SET workout='$workout', status='$status' WHERE m_id='$m_id' AND workdate='$workdate'";
        $result2 = mysqli_query($condb, $sql2) or die("Error in query: $sql2 " . mysqli_error($condb));

        mysqli_close($condb);
        if ($result2) {
            echo "<script>alert('บันทึกเวลาออกสำเร็จ! สถานะของคุณคือ $status'); window.location='profile.php';</script>";
        } else {
            echo "<script>alert('Error! ไม่สามารถบันทึกข้อมูลได้'); window.location='profile.php';</script>";
        }
    } else {
        echo "<script>alert('กรุณาบันทึกเวลาเข้าก่อน!'); window.location='profile.php';</script>";
    }
}

?>
