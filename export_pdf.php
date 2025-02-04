<?php

session_start();
require('fpdf/fpdf.php');
include('condb.php');

// รับค่าตัวกรองจาก GET
$searchName = isset($_GET['searchName']) ? trim($_GET['searchName']) : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// สร้าง Query ดึงข้อมูลสรุป
$querylist = "
    SELECT 
        e.m_id,
        e.m_firstname,
        e.m_name, 
        e.m_lastname, 
        COUNT(w.workdate) AS total_workdays,
        SUM(CASE WHEN w.workin IS NULL THEN 1 ELSE 0 END) AS absent,
        SUM(CASE WHEN w.workin > '08:30:00' THEN 1 ELSE 0 END) AS late,
        SUM(CASE WHEN w.workin <= '08:30:00' THEN 1 ELSE 0 END) AS normal
    FROM tbl_emp e
    LEFT JOIN tbl_work_io w ON e.m_id = w.m_id
    WHERE e.m_level != 'admin'
";

// กรองข้อมูลตามตัวกรอง
if (!empty($searchName)) {
    $querylist .= " AND (e.m_firstname LIKE '%$searchName%' OR e.m_lastname LIKE '%$searchName%')";
}
if (!empty($startDate) && !empty($endDate)) {
    $querylist .= " AND w.workdate BETWEEN '$startDate' AND '$endDate'";
} elseif (!empty($startDate)) {
    $querylist .= " AND w.workdate >= '$startDate'";
} elseif (!empty($endDate)) {
    $querylist .= " AND w.workdate <= '$endDate'";
}

$querylist .= " GROUP BY e.m_id ORDER BY e.m_firstname, e.m_lastname";
$resultlist = mysqli_query($condb, $querylist) or die("Error in query: $querylist " . mysqli_error($condb));

// สร้าง Query ดึงข้อมูลรายละเอียด
$queryDetails = "
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

// กรองข้อมูลตามตัวกรอง
if (!empty($searchName)) {
    $queryDetails .= " AND (e.m_firstname LIKE '%$searchName%' OR e.m_name LIKE '%$searchName%' OR e.m_lastname LIKE '%$searchName%')";
}
if (!empty($startDate) && !empty($endDate)) {
    $queryDetails .= " AND w.workdate BETWEEN '$startDate' AND '$endDate'";
} elseif (!empty($startDate)) {
    $queryDetails .= " AND w.workdate >= '$startDate'";
} elseif (!empty($endDate)) {
    $queryDetails .= " AND w.workdate <= '$endDate'";
}

$queryDetails .= " ORDER BY e.m_firstname, e.m_lastname, w.workdate DESC";
$resultDetails = mysqli_query($condb, $queryDetails) or die("Error in query: $queryDetails " . mysqli_error($condb));

// จัดกลุ่มข้อมูลตามชื่อบุคลากร
$groupedData = [];
while ($row = mysqli_fetch_assoc($resultDetails)) {
    $fullname = $row['m_firstname'] . " " . $row['m_name'] . " " . $row['m_lastname'];
    $groupedData[$fullname][] = $row;
}

// ใช้ FPDF สร้าง PDF
class PDF extends FPDF
{
    function Header()
    {
        $this->AddFont('THSarabun', '', 'THSarabun.php'); // เพิ่มฟอนต์
        $this->SetFont('THSarabun', '', 18); // ตั้งค่าฟอนต์ THSarabun
        $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'รายงานสรุปและข้อมูลบันทึกเวลาทำงานบุคลากร'), 0, 1, 'C');
        global $startDate, $endDate;
        if (!empty($startDate) && !empty($endDate)) {
            $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'ช่วงวันที่: ' . thai_date($startDate) . ' ถึง ' . thai_date($endDate)), 0, 1, 'C');
        } else {
            $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'ช่วงวันที่: ทั้งหมด'), 0, 1, 'C');
        }
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabun', '', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'หน้าที่ ') . $this->PageNo(), 0, 0, 'C');
    }

    function SummaryTable($resultlist)
    {
        $this->SetFont('THSarabun', '', 14);
        
        // Header
        $this->Cell(20, 10, iconv('UTF-8', 'TIS-620', 'รหัสบุคลากร'), 1);
        $this->Cell(15, 10, iconv('UTF-8', 'TIS-620', 'คำนำหน้า'), 1);
        $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', 'ชื่อ'), 1);
        $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', 'นามสกุล'), 1);
        $this->Cell(25, 10, iconv('UTF-8', 'TIS-620', 'วันทำงานทั้งหมด'), 1);
        $this->Cell(15, 10, iconv('UTF-8', 'TIS-620', 'ขาด'), 1);
        $this->Cell(15, 10, iconv('UTF-8', 'TIS-620', 'สาย'), 1);
        $this->Cell(15, 10, iconv('UTF-8', 'TIS-620', 'ปกติ'), 1);
        $this->Ln();

        // Rows
        while ($row = mysqli_fetch_assoc($resultlist)) {
            $this->Cell(20, 10, $row['m_id'], 1);
            $this->Cell(15, 10, iconv('UTF-8', 'TIS-620', $row['m_firstname']), 1);
            $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', $row['m_name']), 1);
            $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', $row['m_lastname']), 1);
            $this->Cell(25, 10, $row['total_workdays'], 1);
            $this->Cell(15, 10, $row['absent'], 1);
            $this->Cell(15, 10, $row['late'], 1);
            $this->Cell(15, 10, $row['normal'], 1);
            $this->Ln();
        }
    }

    function DetailedTable($groupedData)
    {
        $this->AddPage();
        $this->SetFont('THSarabun', '', 16);
        foreach ($groupedData as $fullname => $records) {
            $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'ชื่อ: ' . $fullname), 0, 1, 'L');
            $this->Cell(0, 2, iconv('UTF-8', 'TIS-620', 'ตำแหน่ง: ' . $records[0]['m_position']), 0, 1, 'L');

            $lateCount = 0;
            $normalCount = 0;
            $absentCount = 0;

            foreach ($records as $value) {
                if (is_null($value["workin"])) {
                    $absentCount++;
                } elseif ($value["workin"] > '08:30:00') {
                    $lateCount++;
                } else {
                    $normalCount++;
                }
            }

            $this->Ln(0);
            $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'สถานะการทำงาน: สาย: ' . $lateCount . ' ครั้ง, ปกติ: ' . $normalCount . ' ครั้ง, ขาด: ' . $absentCount . ' ครั้ง'), 0, 1, 'L');

            // ตารางรายละเอียด
            $this->SetFont('THSarabun', '', 14);
            $this->Cell(50, 10, iconv('UTF-8', 'TIS-620', 'วันที่'), 1);
            $this->Cell(50, 10, iconv('UTF-8', 'TIS-620', 'เวลาเข้างาน'), 1);
            $this->Cell(50, 10, iconv('UTF-8', 'TIS-620', 'เวลาออกงาน'), 1);
            $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', 'สถานะ'), 1);
            $this->Ln();

            foreach ($records as $value) {
                $formattedDate = date('d/m/Y', strtotime($value['workdate']));
                $this->Cell(50, 10, $formattedDate, 1);
                $this->Cell(50, 10, $value['workin'], 1);
                $this->Cell(50, 10, $value['workout'], 1);
                $status = '';
                if (is_null($value["workin"])) {
                    $status = 'ขาด';
                } elseif ($value["workin"] > '08:30:00') {
                    $status = 'สาย';
                } else {
                    $status = 'ปกติ';
                }
                $this->Cell(40, 10, iconv('UTF-8', 'TIS-620', $status), 1);
                $this->Ln();
            }
            $this->Ln(5);
        }
    }
}

// ฟังก์ชันแปลงวันที่เป็นภาษาไทย
function thai_date($date)
{
    $thai_months = [
        1 => 'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
    ];

    if (!empty($date) && $date != '0000-00-00') {
        $year = (int) substr($date, 0, 4) + 543; // แปลงปีเป็น พ.ศ.
        $month = (int) substr($date, 5, 2); // ดึงเดือน
        $day = (int) substr($date, 8, 2); // ดึงวันที่

        return "$day {$thai_months[$month]} $year"; // แสดงเป็นวันที่ เดือน ปี
    }

    return '-'; // กรณีวันที่ไม่ถูกต้อง
}

// สร้าง PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->AddFont('THSarabun', '', 'THSarabun.php'); // เพิ่มฟอนต์ THSarabun

// แสดงข้อมูลสรุป
$pdf->SummaryTable($resultlist);

// แสดงข้อมูลรายละเอียด
$pdf->DetailedTable($groupedData);

// Output PDF
$pdf->Output();
