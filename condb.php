<?php
$condb = mysqli_connect("localhost", "root", "", "workshop_work_io");

if (!$condb) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . mysqli_connect_error());
}

mysqli_query($condb, "SET NAMES 'utf8' ");
date_default_timezone_set('Asia/Bangkok');
?>
