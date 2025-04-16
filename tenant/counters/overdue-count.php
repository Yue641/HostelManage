<?php
include('../../includes/dbconn.php'); // Lùi lên 2 cấp từ tenant/counters/
include('../../includes/check-login-tenant.php');

check_login(); // Kiểm tra đăng nhập

// Kiểm tra session tenant_id
if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id'])) {
    die("Lỗi: Không tìm thấy tenant_id trong session.");
}

$tenant_id = $_SESSION['tenant_id']; 

// Truy vấn tổng số tiền đã thanh toán
$query = "
    SELECT COALESCE(SUM(b.total_amount), 0) AS total_overdue
    FROM bills b
    JOIN contracts c ON b.room_id = c.room_id
    WHERE c.tenant_id = ? AND b.status = 'overdue'
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// In ra kết quả
echo $row['total_overdue'] ?? 0;
?>
