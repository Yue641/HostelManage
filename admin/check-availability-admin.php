<?php
    require_once("../includes/dbconn.php");
    if(!empty($_POST["emailid"])) {
        $email= $_POST["emailid"];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)===false) {

            echo "error : You did not enter a valid email.";
        } else {
            $result ="SELECT count(*) FROM tenants WHERE email=?";
            $stmt = $mysqli->prepare($result);
            $stmt->bind_param('s',$email);
            $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if($count>0){
    echo "<span style='color:red'> Email already exist .</span>";
        } else {
            echo "<span style='color:green'> Email available for registration .</span>";
        }
     }
    }

    if(!empty($_POST["oldpassword"])) {
    $pass=$_POST["oldpassword"];
    $result ="SELECT password FROM admin WHERE password=?";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('s',$pass);
    $stmt->execute();
    $stmt -> bind_result($result);
    $stmt -> fetch();
    $opass=$result;
    if($opass==$pass) 
    echo "<span style='color:green'> Password  matched.</span>";
    else echo "<span style='color:red'>Password doesnot match!</span>";
    }

    if (!empty($_POST["room_number"])) {    
        $room_number = $_POST["room_number"];
        $result = "SELECT COUNT(*) FROM rooms WHERE room_number = ?";
        $stmt = $mysqli->prepare($result);
        $stmt->bind_param("s", $room_number);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    
        if ($count > 0) {
            echo "<span style='color:red'> Room number already exists. </span>";
        } else {
            echo "<span style='color:green'> Room number is available. </span>";
        }
    }

    if (isset($_POST['utility_id'])) {
        $utility_id = intval($_POST['utility_id']);
    
        // Kết nối cơ sở dữ liệu trước đoạn này
        $query = "SELECT price_per_unit, price_per_unit_f2, price_per_unit_f3 FROM utilities WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $utility_id);
        $stmt->execute();
        $stmt->bind_result($price_per_unit, $price_per_unit_f2, $price_per_unit_f3);
        
        if ($stmt->fetch()) {
            // Trả về JSON
            echo json_encode([
                'price_per_unit' => $price_per_unit,
                'price_per_unit_f2' => $price_per_unit_f2,
                'price_per_unit_f3' => $price_per_unit_f3
            ]);
        }
    
        $stmt->close();
    }
    
?>