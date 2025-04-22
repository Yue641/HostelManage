<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['export'] === 'excel') {
    session_start();
    
    if (!isset($_SESSION['report_data'])) {
        die('No data to export.');
    }

    $data = $_SESSION['report_data'];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Tiêu đề cột
    $sheet->fromArray(['#', 'Tenant', 'Room Number', 'Unpaid', 'Paid', 'Overdue'], NULL, 'A1');

    $row = 2;
    $count = 1;
    foreach ($data as $entry) {
        $sheet->setCellValue('A' . $row, $count++);
        $sheet->setCellValue('B' . $row, $entry['tenant']);
        $sheet->setCellValue('C' . $row, $entry['room_number']);
        $sheet->setCellValue('D' . $row, $entry['Unpaid']);
        $sheet->setCellValue('E' . $row, $entry['Paid']);
        $sheet->setCellValue('F' . $row, $entry['Overdue']);
        $row++;
    }

    // Xuất file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
