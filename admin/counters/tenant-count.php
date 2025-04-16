<?php
    include '../includes/dbconn.php';

    $sql = "SELECT tenant_id FROM tenants";
                $query = $mysqli->query($sql);
                echo "$query->num_rows";
?>