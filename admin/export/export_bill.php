<?php
require_once '../../vendor/autoload.php'; // Composer autoload
use PhpOffice\PhpWord\TemplateProcessor;

require '../../includes/dbconn.php'; // Kết nối CSDL

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Kiểm tra nếu có id hóa đơn trong URL
if (isset($_GET['id'])) {
    $bill_id = $_GET['id'];

    // Lấy dữ liệu hóa đơn từ cơ sở dữ liệu
    $ret = "SELECT b.id, r.room_number, b.issue_date, b.due_date, b.total_amount, b.status, b.description
            FROM bills b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_object();

    // Tạo đối tượng Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Thiết lập tiêu đề cột
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Room');
    $sheet->setCellValue('C1', 'Issue Date');
    $sheet->setCellValue('D1', 'Due Date');
    $sheet->setCellValue('E1', 'Total Amount');
    $sheet->setCellValue('F1', 'Status');
    $sheet->setCellValue('G1', 'Description');

    // Điền dữ liệu vào Excel
    $sheet->setCellValue('A2', $row->id);
    $sheet->setCellValue('B2', $row->room_number);
    $sheet->setCellValue('C2', $row->issue_date);
    $sheet->setCellValue('D2', $row->due_date);
    $sheet->setCellValue('E2', $row->total_amount);
    $sheet->setCellValue('F2', ucwords(str_replace('_', ' ', $row->status)));
    $sheet->setCellValue('G2', $row->description);

    // Thiết lập tiêu đề để trình duyệt hiểu là file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="bill_' . $bill_id . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Ghi file Excel
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    echo "No bill ID provided.";
}
?>