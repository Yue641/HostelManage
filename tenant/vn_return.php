<?php
session_start();
include('../includes/dbconn.php'); // Kết nối CSDL
include('../includes/check-login-tenant.php');
check_login();

// Bật hiển thị lỗi (dùng để debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình mã bí mật (Hash Secret) của bạn tại đây
$vnp_HashSecret = "YOUR_HASH_SECRET"; // Thay bằng mã bí mật từ VNPay

// Lấy tất cả tham số trả về từ VNPay
$vnp_Params = $_GET;

// Tách thông tin cần thiết
$vnp_SecureHash = $vnp_Params['vnp_SecureHash'];
unset($vnp_Params['vnp_SecureHash']);
unset($vnp_Params['vnp_SecureHashType']);

// Sắp xếp lại dữ liệu
ksort($vnp_Params);
$hashData = "";
foreach ($vnp_Params as $key => $value) {
    $hashData .= $key . "=" . $value . "&";
}
$hashData = rtrim($hashData, "&");

// Tạo lại chữ ký để kiểm tra
$secureHashCheck = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Kiểm tra chữ ký hợp lệ và mã kết quả thành công
if ($secureHashCheck === $vnp_SecureHash && $vnp_Params['vnp_ResponseCode'] == '00') {
    $amount = floatval($vnp_Params['vnp_Amount']) / 100; // Chia lại vì đã nhân 100
    $bill_id = isset($vnp_Params['bill_id']) ? intval($vnp_Params['bill_id']) : 0;
    $room_id = isset($vnp_Params['room_id']) ? intval($vnp_Params['room_id']) : 0;

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

    $desc = $bill['description'];

    // Ghi thông tin vào bảng payments
    $sql = "INSERT INTO payments (bill_id, payment_date, amount_paid, payment_method, desc_pay, room_id) 
            VALUES (?, NOW(), ?, 'vnpay', ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("idsi", $bill_id, $amount, $desc, $room_id);
    $stmt->execute();
    $stmt->close();

    // Cập nhật hóa đơn đã thanh toán
    $sql = "UPDATE bills SET status = 'paid' WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['payment_status'] = "success";
    $_SESSION['payment_message'] = "Thanh toán thành công qua VNPay!";
} else {
    $_SESSION['payment_status'] = "error";
    $_SESSION['payment_message'] = "Thanh toán thất bại hoặc sai chữ ký!";
}

header("Location: check-bills.php");
exit;
?>
