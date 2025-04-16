<?php
include('../../includes/dbconn.php'); // Lùi lên 2 cấp từ tenant/counters/
include('../../includes/check-login-tenant.php');

// Lấy ID của tenant đang đăng nhập
$tenant_id = $_SESSION['tenant_id']; // Giả sử ID tenant lưu trong session khi đăng nhập

// Truy vấn số lượng request của tenant trong bảng maintenance_requests
$query = "
    SELECT COUNT(*) AS total_requests
    FROM maintenance_requests
    WHERE tenant_id = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// In ra kết quả
echo $row['total_requests'] ?? 0;
?>
