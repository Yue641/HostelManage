<?php
include('../includes/dbconn.php');

if(isset($_POST['room_id'])) {
    $room_id = intval($_POST['room_id']);
    $query = "SELECT price FROM rooms WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo $row['price']; // Trả về giá thuê
    } else {
        echo "0";
    }
}
?>
