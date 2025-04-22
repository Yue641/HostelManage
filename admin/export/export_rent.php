<?php
require '../../includes/dbconn.php'; // Kết nối CSDL

if ($_POST['export'] == 'word') {
    // Truy vấn lấy dữ liệu hóa đơn & số phòng
    $sql = "SELECT r.room_number, u.recorded_date, u.utility_id, u.total 
            FROM utility_usages u 
            JOIN rooms r ON u.room_id = r.id"; // JOIN để lấy room_number
    $result = $mysqli->query($sql);

    $rooms = [];

    while ($row = $result->fetch_assoc()) {
        $room_number = $row['room_number'];
        $month_year = date("m/Y", strtotime($row['recorded_date'])); // Lấy tháng/năm
        $filename = "Room-{$room_number}-{$month_year} Rent.doc"; // Đặt tên file

        if (!isset($rooms[$room_number])) {
            $rooms[$room_number] = [
                'room_number' => $room_number,
                'month_year' => $month_year,
                'room_total' => 0,
                'electricity' => 0,
                'water' => 0,
                'internet' => 0
            ];
        }

        // Xử lý tổng tiền tiện ích
        switch ($row['utility_id']) {
            case 1:
                $rooms[$room_number]['electricity'] += $row['total'];
                break;
            case 2:
                $rooms[$room_number]['water'] += $row['total'];
                break;
            case 3:
                $rooms[$room_number]['internet'] += $row['total'];
                break;
            default:
                $rooms[$room_number]['room_total'] += $row['total'];
                break;
        }
    }

    // Xuất file Word
    header("Content-Type: application/msword");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<h2>Danh sách hóa đơn - Tháng {$month_year}</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Room Number</th><th>Month/Year</th><th>Room Rent</th><th>Điện</th><th>Nước</th><th>Internet</th><th>Total</th></tr>";

    foreach ($rooms as $room) {
        $total_amount = $room['room_total'] + $room['electricity'] + $room['water'] + $room['internet'];
        echo "<tr>
                <td>{$room['room_number']}</td>
                <td>{$room['month_year']}</td>
                <td>{$room['room_total']}</td>
                <td>{$room['electricity']}</td>
                <td>{$room['water']}</td>
                <td>{$room['internet']}</td>
                <td>{$total_amount}</td>
              </tr>";
    }

    echo "</table>";
    echo "</body></html>";
    exit();
}
?>

<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_POST['export'] == 'excel') {
    require '../../vendor/autoload.php';
    require '../../includes/dbconn.php';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Tiêu đề cột
    $sheet->setCellValue('A1', 'Room Number');
    $sheet->setCellValue('B1', 'Month/Year');
    $sheet->setCellValue('C1', 'Room Rent');
    $sheet->setCellValue('D1', 'Electricity');
    $sheet->setCellValue('E1', 'Water');
    $sheet->setCellValue('F1', 'Internet');
    $sheet->setCellValue('G1', 'Total');

    // Lấy dữ liệu từ CSDL
    $sql = "SELECT r.room_number, u.recorded_date, u.utility_id, u.total 
            FROM utility_usages u 
            JOIN rooms r ON u.room_id = r.id"; // JOIN để lấy room_number
    $result = $mysqli->query($sql);

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $room_number = $row['room_number'];
        $month_year = date("m/Y", strtotime($row['recorded_date']));

        if (!isset($rooms[$room_number])) {
            $rooms[$room_number] = [
                'room_number' => $room_number,
                'month_year' => $month_year,
                'room_total' => 0,
                'electricity' => 0,
                'water' => 0,
                'internet' => 0
            ];
        }

        switch ($row['utility_id']) {
            case 1:
                $rooms[$room_number]['electricity'] += $row['total'];
                break;
            case 2:
                $rooms[$room_number]['water'] += $row['total'];
                break;
            case 3:
                $rooms[$room_number]['internet'] += $row['total'];
                break;
            default:
                $rooms[$room_number]['room_total'] += $row['total'];
                break;
        }
    }

    // Ghi dữ liệu vào Excel
    $rowNum = 2;
    foreach ($rooms as $room) {
        $total_amount = $room['room_total'] + $room['electricity'] + $room['water'] + $room['internet'];

        $sheet->setCellValue("A$rowNum", $room['room_number']);
        $sheet->setCellValue("B$rowNum", $room['month_year']);
        $sheet->setCellValue("C$rowNum", $room['room_total']);
        $sheet->setCellValue("D$rowNum", $room['electricity']);
        $sheet->setCellValue("E$rowNum", $room['water']);
        $sheet->setCellValue("F$rowNum", $room['internet']);
        $sheet->setCellValue("G$rowNum", $total_amount);

        $rowNum++;
    }

    // Xuất file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename='Room-{$room_number}-{$month_year} Rent.xlsx'");

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
