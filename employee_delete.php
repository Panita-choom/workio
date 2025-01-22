<?php
include('condb.php');

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ลบข้อมูลในฐานข้อมูล
    $sql = "DELETE FROM tbl_emp WHERE m_id = '$id'";
    $result = mysqli_query($condb, $sql);

    if ($result) {
        echo "<script>";
        echo "alert('ลบข้อมูลสำเร็จ');";
        echo "window.location.href = 'employee_data.php';";
        echo "</script>";
    } else {
        echo "<script>";
        echo "alert('เกิดข้อผิดพลาดในการลบข้อมูล');";
        echo "window.history.back();";
        echo "</script>";
    }
} else {
    echo "ไม่มีข้อมูล ID ที่ต้องการลบ";
}
?>
