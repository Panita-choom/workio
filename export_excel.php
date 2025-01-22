<?php
// เชื่อมต่อฐานข้อมูล
include('condb.php');

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

// ดึงข้อมูลจากฐานข้อมูล
$resultlist = mysqli_query($condb, $querylist) or die("Error in query: $querylist " . mysqli_error($condb));

// รวมข้อมูลในรูปแบบที่ต้องการ
$data = [];
while ($row = mysqli_fetch_assoc($resultlist)) {
    $fullname = $row['m_firstname'] . " " . $row['m_name'] . " " . $row['m_lastname'];
    $data[$fullname][] = $row;
}

// เรียกใช้ PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// สร้างสเปรดชีตใหม่
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// กำหนดหัวตาราง
$sheet->setCellValue('A1', 'ชื่อบุคลากร');
$sheet->setCellValue('B1', 'ตำแหน่ง');
$sheet->setCellValue('C1', 'วันที่');
$sheet->setCellValue('D1', 'เวลาเข้างาน');
$sheet->setCellValue('E1', 'เวลาออกงาน');
$sheet->setCellValue('F1', 'สถานะ');

$rowNum = 2;  // เริ่มต้นที่แถว 2

// เติมข้อมูลลงในสเปรดชีต
foreach ($data as $fullname => $records) {
    foreach ($records as $value) {
        $status = is_null($value["workin"]) ? 'ขาด' : ($value["workin"] > '08:30:00' ? 'สาย' : 'ปกติ');
        $sheet->setCellValue("A$rowNum", $fullname);
        $sheet->setCellValue("B$rowNum", $value['m_position']);
        $sheet->setCellValue("C$rowNum", $value['workdate']);
        $sheet->setCellValue("D$rowNum", $value['workin']);
        $sheet->setCellValue("E$rowNum", $value['workout']);
        $sheet->setCellValue("F$rowNum", $status);
        $rowNum++;
    }
}

// สร้างไฟล์ Excel และส่งให้ผู้ใช้ดาวน์โหลด
$writer = new Xlsx($spreadsheet);
$filename = "work_time_records.xlsx";

// ตั้งค่า header เพื่อให้บราวเซอร์ดาวน์โหลดไฟล์
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
?>
