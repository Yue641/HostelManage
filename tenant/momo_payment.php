<?php
session_start();
include('../includes/dbconn.php'); // Kết nối CSDL
include('../includes/check-login-tenant.php');
check_login();

// Bật hiển thị lỗi (dùng để debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra nếu có đầy đủ tham số
if (!isset($_GET['amount']) || !isset($_GET['desc']) || !isset($_GET['room_id']) || !isset($_GET['bill_id'])) {
    die("Thiếu thông tin thanh toán!");
}

// Nhận dữ liệu từ URL
$amount = floatval($_GET['amount']); // Số tiền
$desc = urldecode($_GET['desc']); // Mô tả
$room_id = intval($_GET['room_id']); // Mã phòng
$bill_id = intval($_GET['bill_id']); // Mã hóa đơn

// Cấu hình thông tin Momo (Thay bằng API Key thật)
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMOXXXX"; // Thay bằng mã đối tác của bạn
$accessKey = "ACCESS_KEY"; // Thay bằng access key của bạn
$secretKey = "SECRET_KEY"; // Thay bằng secret key của bạn
$orderId = time() . ""; // Mã đơn hàng (đảm bảo duy nhất)
$orderInfo = $desc;
$returnUrl = "http://yourwebsite.com/momo_return.php"; // Trang xử lý kết quả thanh toán
$notifyUrl = "http://yourwebsite.com/momo_notify.php"; // Trang nhận callback từ Momo
$requestId = time() . "";
$extraData = "";

// Tạo mảng dữ liệu gửi đi
$data = array(
    'partnerCode' => $partnerCode,
    'accessKey' => $accessKey,
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'returnUrl' => $returnUrl,
    'notifyUrl' => $notifyUrl,
    'extraData' => $extraData,
    'requestType' => 'captureWallet'
);

// Tạo chữ ký
ksort($data);
$rawHash = "";
foreach ($data as $key => $value) {
    $rawHash .= $key . "=" . $value . "&";
}
$rawHash = rtrim($rawHash, "&");
$signature = hash_hmac("sha256", $rawHash, $secretKey);
$data['signature'] = $signature;

// Gửi request đến Momo
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['resultCode'] == 0) {
    // Nếu thành công, chuyển hướng đến trang thanh toán Momo
    header("Location: " . $result['payUrl']);
    exit;
} else {
    die("Lỗi khi tạo thanh toán: " . $result['message']);
}
?>
