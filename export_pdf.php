<?php

session_start();
require('fpdf/fpdf.php');
include('condb.php');

// กำหนดชื่อเดือนภาษาไทย
$thai_months = [
    "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", "05" => "พฤษภาคม", "06" => "มิถุนายน",
    "07" => "กรกฎาคม", "08" => "สิงหาคม", "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
];

// ตรวจสอบค่าปีและเดือนจาก $_GET
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
if ($year < 1900 || $year > 2100) {
    $year = date('Y'); // ใช้ปีปัจจุบันถ้าค่าผิดปกติ
}

$month = isset($_GET['month']) ? str_pad((int)$_GET['month'], 2, "0", STR_PAD_LEFT) : date('m');
if (!array_key_exists($month, $thai_months)) {
    $month = date('m'); // ใช้เดือนปัจจุบันถ้าค่าผิดปกติ
}

$thai_year = $year + 543;
$month_name = $thai_months[$month];

// ตรวจสอบค่าที่ส่งไปยัง cal_days_in_month()
$month_int = (int)$month;
if ($month_int < 1 || $month_int > 12) {
    die("Error: Invalid month value.");
}

// หาจำนวนวันในเดือน
$total_days = cal_days_in_month(CAL_GREGORIAN, $month_int, $year);

// ดึงข้อมูลพนักงาน
$queryEmp = "SELECT m_id, m_firstname, m_name, m_lastname FROM tbl_emp WHERE m_level != 'admin'";
$resultEmp = mysqli_query($condb, $queryEmp);
$employees = mysqli_fetch_all($resultEmp, MYSQLI_ASSOC);

// ดึงข้อมูลวันหยุดจากฐานข้อมูล
$holidays = [];
$queryHolidays = "SELECT holiday_date, holiday_name FROM tbl_holidays WHERE MONTH(holiday_date) = '$month' AND YEAR(holiday_date) = '$year'";
$resultHolidays = mysqli_query($condb, $queryHolidays);
while ($row = mysqli_fetch_assoc($resultHolidays)) {
    $holidays[$row['holiday_date']] = $row['holiday_name'];
}

class PDF extends FPDF {
    function Header() {
        global $month_name, $thai_year;
        $this->AddFont('THSarabun', '', 'THSarabun.php');
        $this->SetFont('THSarabun', '', 16);
        $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', "รายงานบันทึกเวลาทำงาน เดือน $month_name ปี $thai_year"), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-30);
        $this->SetFont('THSarabun', '', 12);
        
        
        $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'คำอธิบาย: ม = มาปกติ, ข = ขาด, มส = มาสาย, ลก = ลากิจ, ลป = ลาป่วย, รช = ราชการ, ย = หยุด'), 0, 0, 'C');
        $this->Ln(5);
        $this->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'หน้าที่ ') . $this->PageNo(), 0, 0, 'C');
    }

    function AttendanceTable($employees, $total_days, $year, $month, $condb, $holidays) {
        $this->SetFont('THSarabun', '', 10);
        $this->SetFillColor(200, 220, 255);

        $this->AddPage('L');
        $this->Cell(30, 10, iconv('UTF-8', 'TIS-620', 'ชื่อ - สกุล'), 1, 0, 'C', true);
        for ($day = 1; $day <= $total_days; $day++) {
            $this->Cell(8, 10, $day, 1, 0, 'C', true);
        }
        $this->Ln();

        foreach ($employees as $emp) {
            $fullname = $emp['m_firstname'] . " " . $emp['m_name'] . " " . $emp['m_lastname'];
            $this->Cell(30, 10, iconv('UTF-8', 'TIS-620', $fullname), 1);

            for ($day = 1; $day <= $total_days; $day++) {
                $date = "$year-$month-" . str_pad($day, 2, "0", STR_PAD_LEFT);
                if (isset($holidays[$date]) || date('N', strtotime($date)) >= 6) {
                    $status = 'ย';
                } else {
                    $query = "SELECT status, workin, workout FROM tbl_work_io WHERE m_id='{$emp['m_id']}' AND workdate='$date'";
                    $result = mysqli_query($condb, $query);
                    $row = mysqli_fetch_assoc($result);

                    if (!$row) {
                        $status = 'ข';
                    } elseif ($row['status'] == 'ลากิจ') {
                        $status = 'ลก';
                    } elseif ($row['status'] == 'ลาป่วย') {
                        $status = 'ลป';
                    } elseif ($row['status'] == 'ราชการ') {
                        $status = 'รช';
                    } elseif ($row['workin'] && !$row['workout']) {
                        $status = 'มส';
                    } elseif ($row['workin'] > '08:30:00') {
                        $status = 'ส';
                    } else {
                        $status = 'ม';
                    }
                }
                $this->Cell(8, 10, iconv('UTF-8', 'TIS-620', $status), 1, 0, 'C');
            }
            $this->Ln();
        }
    }
}

$pdf = new PDF();
$pdf->AddFont('THSarabun', '', 'THSarabun.php');
$pdf->AttendanceTable($employees, $total_days, $year, $month, $condb, $holidays);
$pdf->Output();

?>
