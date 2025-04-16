<?php
    include '../includes/dbconn.php';

    $sql = "SELECT id FROM rooms WHERE status = 'occupied'";
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>