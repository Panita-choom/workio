<?php
include('condb.php'); // ไฟล์เชื่อมต่อฐานข้อมูล
date_default_timezone_set("Asia/Bangkok");

$datenow = date('Y-m-d');
$timenow = date('H:i:s');

// ดึงรายชื่อพนักงานทั้งหมด
$queryEmp = "SELECT m_id FROM tbl_emp";
$resultEmp = mysqli_query($condb, $queryEmp);

while ($row = mysqli_fetch_assoc($resultEmp)) {
    $m_id = $row['m_id'];

    // ตรวจสอบว่าวันนี้เป็นวันหยุดหรือไม่
    $queryHoliday = "SELECT * FROM tbl_holidays WHERE holiday_date = '$datenow'";
    $resultHoliday = mysqli_query($condb, $queryHoliday);
    $isHoliday = mysqli_num_rows($resultHoliday) > 0;

    // ตรวจสอบว่าพนักงานมีบันทึกเวลาวันนี้หรือยัง
    $queryWorkIO = "SELECT * FROM tbl_work_io WHERE m_id='$m_id' AND workdate='$datenow'";
    $resultWorkIO = mysqli_query($condb, $queryWorkIO);

    if (mysqli_num_rows($resultWorkIO) == 0) {
        if ($isHoliday) {
            // ถ้าเป็นวันหยุด ให้บันทึก "หยุด"
            $insertQuery = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout, status) 
                            VALUES ('$m_id', '$datenow', NULL, NULL, 'หยุด')";
        } else {
            // ถ้าไม่ใช่วันหยุด ให้บันทึก "ขาด"
            $insertQuery = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout, status) 
                            VALUES ('$m_id', '$datenow', NULL, NULL, 'ขาด')";
        }
        mysqli_query($condb, $insertQuery);
    }
}

echo "บันทึกข้อมูลอัตโนมัติสำเร็จ";
?>
