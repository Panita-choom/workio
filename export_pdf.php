<?php
require_once('tcpdf_include.php');

// เชื่อมต่อฐานข้อมูล
include('condb.php');

// รับค่าการค้นหาและวันที่จากฟอร์ม
$searchName = isset($_POST['searchName']) ? trim($_POST['searchName']) : '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

// ดึงข้อมูลบุคลากร
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

// เพิ่มเงื่อนไขการค้นหาชื่อและวันที่
if (!empty($searchName)) {
    $querylist .= " AND (e.m_firstname LIKE '%$searchName%' OR e.m_name LIKE '%$searchName%' OR e.m_lastname LIKE '%$searchName%')";
}

if (!empty($startDate) && !empty($endDate)) {
    $querylist .= " AND w.workdate BETWEEN '$startDate' AND '$endDate'";
} elseif (!empty($startDate)) {
    $querylist .= " AND w.workdate >= '$startDate'";
} elseif (!empty($endDate)) {
    $querylist .= " AND w.workdate <= '$endDate'";
}

$querylist .= " ORDER BY e.m_firstname, e.m_lastname, w.workdate DESC";
$resultlist = mysqli_query($condb, $querylist) or die("Error in query: $querylist " . mysqli_error($condb));

// สร้างเอกสาร PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('times', '', 12);

// เขียนหัวข้อ
$pdf->Cell(0, 10, 'รายงานบันทึกเวลาปฏิบัติงานบุคลากร', 0, 1, 'C');

// วนลูปเพื่อแสดงข้อมูล
while ($row = mysqli_fetch_assoc($resultlist)) {
    $fullname = $row['m_firstname'] . " " . $row['m_name'] . " " . $row['m_lastname'];
    $pdf->Cell(0, 10, 'ชื่อบุคลากร: ' . $fullname, 0, 1);
    $pdf->Cell(0, 10, 'ตำแหน่ง: ' . $row['m_position'], 0, 1);

    // ตารางบันทึกเวลาการทำงาน
    $pdf->SetFont('times', '', 10);
    $pdf->Cell(40, 10, 'วันที่', 1);
    $pdf->Cell(40, 10, 'เวลาเข้างาน', 1);
    $pdf->Cell(40, 10, 'เวลาออกงาน', 1);
    $pdf->Cell(40, 10, 'สถานะ', 1);
    $pdf->Ln();

    // เพิ่มข้อมูลลงในตาราง
    $workin = isset($row['workin']) ? $row['workin'] : '';
    $workout = isset($row['workout']) ? $row['workout'] : '';
    $status = is_null($workin) ? 'ขาด' : ($workin > '08:30:00' ? 'สาย' : 'ปกติ');

    $pdf->Cell(40, 10, $row['workdate'], 1);
    $pdf->Cell(40, 10, $workin, 1);
    $pdf->Cell(40, 10, $workout, 1);
    $pdf->Cell(40, 10, $status, 1);
    $pdf->Ln();
}

// ส่งไฟล์ PDF
$pdf->Output('report.pdf', 'I');
?>
