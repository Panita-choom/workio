<?php
include('condb.php'); // เชื่อมต่อฐานข้อมูล
include('functions.php'); // เรียกใช้ฟังก์ชัน API

$year = date('Y'); // ปีปัจจุบัน
$holidays = getThaiHolidays($year);

if (!empty($holidays)) {
    foreach ($holidays as $date => $name) {
        $sql = "INSERT INTO tbl_holidays (holiday_date, holiday_name) VALUES ('$date', '$name')
                ON DUPLICATE KEY UPDATE holiday_name='$name'";
        mysqli_query($condb, $sql);
    }
    echo "✅ อัปเดตวันหยุดเรียบร้อย!";
} else {
    echo "❌ ไม่สามารถดึงข้อมูลวันหยุดได้!";
}
?>
