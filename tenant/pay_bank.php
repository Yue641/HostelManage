<?php
session_start();
include('../includes/dbconn.php'); // Kết nối CSDL
include('../includes/check-login-tenant.php');
check_login();

// Bật hiển thị lỗi (dùng để debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['bill_id'])) {
    die("Thiếu mã hóa đơn!");
}

$bill_id = intval($_GET['bill_id']); // Chuyển đổi thành số nguyên để tránh lỗi

// Kiểm tra kết nối CSDL
if (!$mysqli) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}

// Truy vấn lấy thông tin hóa đơn từ bảng `bills`
$sql = "SELECT total_amount, description, room_id FROM bills WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $mysqli->error);
}
$stmt->bind_param("i", $bill_id);
$stmt->execute();
$result = $stmt->get_result();
$bill = $result->fetch_assoc();

if (!$bill) {
    die("Hóa đơn không tồn tại!");
}

// Gán thông tin hóa đơn để sử dụng trong thanh toán VNPay
$amount = $bill['total_amount']; // Số tiền
$desc = urlencode($bill['description']); // Mô tả (phải mã hóa URL)
$room_id = intval($bill['room_id']); // Mã phòng (chuyển về số nguyên)

// Đóng kết nối trước khi chuyển hướng
$stmt->close();
$mysqli->close();

// Chuyển hướng đến trang thanh toán VNPay
header("Location: momo_payment.php?amount=$amount&desc=$desc&room_id=$room_id&bill_id=$bill_id");
exit;
?>
