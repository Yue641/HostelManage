<?php
session_start();
include('../includes/dbconn.php'); // Kết nối CSDL
include('../includes/check-login-tenant.php');
check_login();

// Bật hiển thị lỗi (debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra nếu có đầy đủ tham số
if (!isset($_GET['amount']) || !isset($_GET['desc']) || !isset($_GET['room_id']) || !isset($_GET['bill_id'])) {
    die("Thiếu thông tin thanh toán!");
}

// Nhận dữ liệu từ URL
$amount = floatval($_GET['amount']);
$desc = urlencode($_GET['desc']);
$room_id = intval($_GET['room_id']);
$bill_id = intval($_GET['bill_id']);

// Cấu hình VNPay
$vnp_TmnCode = "YOUR_TMNCODE"; // Mã website của bạn tại VNPay
$vnp_HashSecret = "YOUR_HASH_SECRET"; // Chuỗi bí mật từ VNPay
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // Địa chỉ thanh toán (sandbox)
$vnp_Returnurl = "http://yourdomain.com/vn_return.php"; // URL nhận kết quả trả về

$vnp_TxnRef = time(); // Mã giao dịch duy nhất
$vnp_OrderInfo = "Thanh toan hoa don: " . urldecode($desc);
$vnp_Amount = $amount * 100; // Nhân 100 vì VNPay dùng đơn vị là VND * 100
$vnp_Locale = "vn";
$vnp_BankCode = "VNPAYQR";
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

// Tạo mảng dữ liệu gửi đến VNPay
$vnp_Params = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "billpayment",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef
);

// Sắp xếp dữ liệu và tạo chữ ký
ksort($vnp_Params);
$hashdata = "";
$query = "";
foreach ($vnp_Params as $key => $value) {
    $hashdata .= $key . "=" . $value . "&";
    $query .= urlencode($key) . "=" . urlencode($value) . "&";
}
$hashdata = rtrim($hashdata, "&");
$query = rtrim($query, "&");

$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

// Chuyển hướng sang trang thanh toán VNPay
header("Location: " . $vnp_Url);
exit;
?>
