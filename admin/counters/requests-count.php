<?php
    include '../includes/dbconn.php';

    $sql = "SELECT id FROM maintenance_requests";
                $query = $mysqli->query($sql);
                echo "$query->num_rows";
?>