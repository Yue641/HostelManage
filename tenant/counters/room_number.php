<?php
include('../../includes/dbconn.php'); // Lùi lên 2 cấp từ tenant/counters/
include('../../includes/check-login-tenant.php');

check_login(); // Kiểm tra đăng nhập

// Kiểm tra session tenant_id
if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id'])) {
    die("Lỗi: Không tìm thấy tenant_id trong session.");
}

$tenant_id = $_SESSION['tenant_id']; 

// Truy vấn lấy room_number
$query = "
    SELECT r.room_number
    FROM contracts c
    JOIN rooms r ON c.room_id = r.id
    WHERE c.tenant_id = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

// In ra kết quả
while ($row = $result->fetch_assoc()) {
    echo " " . $row['room_number'] . "<br>";
}
?>
