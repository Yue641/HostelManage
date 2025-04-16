<?php
session_start();
require_once 'dbconn.php';

if (!isset($_GET['bill_id'])) {
    die("Lỗi: Thiếu thông tin hóa đơn!");
}

$bill_id = $_GET['bill_id'];
$payment_method = "cash";
$payment_date = date("Y-m-d"); // Ngày thanh toán hiện tại

// Lấy thông tin hóa đơn từ bảng bills
$query = "SELECT total_amount, description, room_id FROM bills WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $bill_id);
$stmt->execute();
$result = $stmt->get_result();
$bill = $result->fetch_assoc();

if (!$bill) {
    die("Lỗi: Không tìm thấy hóa đơn!");
}

// Gán giá trị từ hóa đơn
$amount_paid = $bill['total_amount'];
$desc_pay = $bill['description'];
$room_id = $bill['room_id'];

// Thêm dữ liệu vào bảng payment
$insert_payment = "INSERT INTO payment (bill_id, payment_date, amount_paid, payment_method, desc_pay, room_id) 
                   VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($insert_payment);
$stmt->bind_param("isdssi", $bill_id, $payment_date, $amount_paid, $payment_method, $desc_pay, $room_id);
$success = $stmt->execute();

if ($success) {
    header("Location: bills.php?message=Payment registration successful! Please see manager for confirmation!");
} else {
    die("Lỗi: Không thể xử lý thanh toán!");
}
?>