<?php
session_start();
include('../includes/dbconn.php'); // Kết nối CSDL
include('../includes/check-login-tenant.php');
check_login();

// Bật hiển thị lỗi (dùng để debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy dữ liệu từ URL do Momo trả về
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$resultCode = isset($_GET['resultCode']) ? intval($_GET['resultCode']) : -1; // 0 là thành công

if ($resultCode == 0) {
    // Lấy thông tin hóa đơn
    $sql = "SELECT description FROM bills WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bill = $result->fetch_assoc();
    $stmt->close();

    if (!$bill) {
        $_SESSION['payment_status'] = "error";
        $_SESSION['payment_message'] = "Hóa đơn không tồn tại!";
        header("Location: check-bills.php");
        exit;
    }

    $desc = $bill['description']; // Lấy mô tả thanh toán

    // Thêm thông tin vào bảng payments
    $sql = "INSERT INTO payments (bill_id, payment_date, amount_paid, payment_method, desc_pay, room_id) VALUES (?, NOW(), ?, 'bank_transfer', ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("idsi", $bill_id, $amount, $desc, $room_id);
    $stmt->execute();
    $stmt->close();

    // Cập nhật trạng thái hóa đơn thành "paid"
    $sql = "UPDATE bills SET status = 'paid' WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['payment_status'] = "success";
    $_SESSION['payment_message'] = "Payment Successful!";
} else {
    $_SESSION['payment_status'] = "error";
    $_SESSION['payment_message'] = "Payment Error!";
}

// Chuyển hướng về check-bills.php sau khi xử lý xong
header("Location: check-bills.php");
exit;
?>
