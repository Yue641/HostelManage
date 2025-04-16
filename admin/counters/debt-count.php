<?php
    include '../includes/dbconn.php';

    $sql = "SELECT SUM(total_amount) AS total_unpaid_overdue FROM bills WHERE status IN ('unpaid', 'overdue')";
    $query = $mysqli->query($sql);
    $row = $query->fetch_assoc();
    echo $row['total_unpaid_overdue'] ?? 0; // Nếu không có dữ liệu, trả về 0
?>
