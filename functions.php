<?php
// เชื่อมต่อฐานข้อมูล
function dbConnect() {
    $servername = "localhost";
    $username = "root";  // เปลี่ยนเป็นชื่อผู้ใช้ของ MySQL ถ้าไม่ใช่ root
    $password = "";      // ใส่รหัสผ่านถ้ามี
    $dbname = "workio";  // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    mysqli_set_charset($conn, "utf8");
    return $conn;
}

// ดึงข้อมูลวันหยุดราชการไทยจาก BOT API
function getThaiHolidays($year) {
    $apiKey = "YOUR_API_KEY";  // 🔹 ใส่ API Key ที่ได้รับจาก BOT
    $url = "https://apigw1.bot.or.th/bot/public/financial-institutions-holidays/$year";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-IBM-Client-Id: $apiKey"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $holidays = json_decode($response, true);
    return $holidays['result']['data'] ?? [];
}

// บันทึกวันหยุดลงฐานข้อมูล
function saveHolidaysToDB($year) {
    $conn = dbConnect();
    $holidays = getThaiHolidays($year);

    if (empty($holidays)) {
        echo "ไม่พบข้อมูลวันหยุดของปี $year";
        return;
    }

    foreach ($holidays as $holiday) {
        $date = $holiday['date'];
        $name = $holiday['description']['th'];

        // ตรวจสอบว่ามีวันหยุดนี้ในฐานข้อมูลหรือไม่
        $checkQuery = "SELECT * FROM tbl_holidays WHERE holiday_date = '$date'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows == 0) {
            $insertQuery = "INSERT INTO tbl_holidays (holiday_date, holiday_name) VALUES ('$date', '$name')";
            $conn->query($insertQuery);
        }
    }
    echo "บันทึกวันหยุดเรียบร้อย";
    $conn->close();
}

// ตรวจสอบว่าวันที่กำหนดเป็นวันหยุดหรือไม่
function isHoliday($date) {
    $conn = dbConnect();
    $query = "SELECT * FROM tbl_holidays WHERE holiday_date = '$date'";
    $result = $conn->query($query);
    return $result->num_rows > 0;
}

// ตรวจสอบว่าวันที่กำหนดเป็นวันเสาร์-อาทิตย์หรือไม่
function isWeekend($date) {
    $dayOfWeek = date('N', strtotime($date)); // 6 = เสาร์, 7 = อาทิตย์
    return ($dayOfWeek == 6 || $dayOfWeek == 7);
}
?>
