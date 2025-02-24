<?php
include('condb.php');
date_default_timezone_set("Asia/Bangkok");

$datenow = date('Y-m-d');

// ดึงรายชื่อบุคลากรทั้งหมด
$queryEmp = "SELECT m_id FROM tbl_emp";
$resultEmp = mysqli_query($condb, $queryEmp);

while ($rowEmp = mysqli_fetch_assoc($resultEmp)) {
    $m_id = $rowEmp['m_id'];

    // ตรวจสอบว่าวันนี้เป็นวันหยุดหรือไม่
    $queryHoliday = "SELECT * FROM tbl_holidays WHERE holiday_date = '$datenow'";
    $resultHoliday = mysqli_query($condb, $queryHoliday);
    $isHoliday = mysqli_num_rows($resultHoliday) > 0;

    // ตรวจสอบว่ามีการบันทึกของวันนี้หรือยัง
    $queryWorkIO = "SELECT * FROM tbl_work_io WHERE m_id='$m_id' AND workdate='$datenow'";
    $resultWorkIO = mysqli_query($condb, $queryWorkIO);
    $rowWorkIO = mysqli_fetch_assoc($resultWorkIO);

    // ถ้าไม่มีบันทึก ให้เพิ่มข้อมูลตามสถานะ
    if (!$rowWorkIO) {
        if ($isHoliday) {
            $status = 'หยุด';
        } else {
            $status = 'ขาด';
        }

        $insertQuery = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout, status) 
                        VALUES ('$m_id', '$datenow', '-', '-', '$status')";
        mysqli_query($condb, $insertQuery);
    }
}

echo "Cron job executed successfully.";
?>
