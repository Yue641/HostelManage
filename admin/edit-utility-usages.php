<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');

// Kiểm tra xem có ID nào được gửi lên không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid request!'); window.location.href='manage-utility-usages.php';</script>";
    exit();
}

$id = $_GET['id'];

// Lấy dữ liệu hiện tại của utility usage
$query = "SELECT * FROM utility_usages WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usage = $result->fetch_assoc();

if (!$usage) {
    echo "<script>alert('Record not found!'); window.location.href='manage-utility-usages.php';</script>";
    exit();
}

// Xử lý cập nhật dữ liệu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $utility_id = $_POST['utility_id'];
    $usage_amount = $_POST['usage_amount'];
    $recorded_date = $_POST['recorded_date'];
    $total = $_POST['total'];

    $update_query = "UPDATE utility_usages SET room_id=?, utility_id=?, usage_amount=?, recorded_date=?, total=? WHERE id=?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("iidsdi", $room_id, $utility_id, $usage_amount, $recorded_date, $total, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Utility Usages updated successfully!'); window.location.href='manage-utility-usages.php';</script>";
    } else {
        echo "Error updating record: " . $mysqli->error;
    }
}

// Lấy danh sách các phòng
$room_query = "SELECT id, room_number FROM rooms";
$room_result = $mysqli->query($room_query);

// Lấy danh sách các tiện ích
$utilities_query = "SELECT id, name FROM utilities";
$utilities_result = $mysqli->query($utilities_query);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Utility Details</h4>
                        <div class="d-flex align-items-center">
                            <!-- <nav aria-label="breadcrumb">
                                
                            </nav> -->
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->

            <div class="container-fluid">

                <form method="POST">

                <div class="row">


                    <!-- Hiển thị toàn bộ danh sách phòng -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Room Number</h4>
                                <div class="form-group">
                                    <select name="room_id" class="form-control" required>
                                        <option value="">-- Select Room --</option>
                                        <?php while ($room = $room_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $room['id']; ?>" <?php echo ($room['id'] == $usage['room_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($room['room_number']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị toàn bộ danh sách dịch vụ -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Utility</h4>
                                <div class="form-group">
                                    <select name="utility_id" class="form-control" required>
                                        <option value="">-- Select Utility --</option>
                                        <?php while ($utility = $utilities_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $utility['id']; ?>" <?php echo ($utility['id'] == $usage['utility_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($utility['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Usage Amount</h4>
                                <div class="form-group">
                                    <input type="number" name="usage_amount" value="<?php echo htmlspecialchars($usage['usage_amount']); ?>" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Total</h4>
                                <div class="form-group">
                                <input type="number" name="total" value="<?php echo htmlspecialchars($usage['total']); ?>" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Ngày khởi tạo -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Recorded Date</h4>
                                <div class="form-group">
                                    <input type="date" name="recorded_date" value="<?php echo htmlspecialchars($usage['recorded_date']); ?>" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                

                        <div class="form-actions">
                            <div class="text-center">
                                <button type="submit" name="submit" class="btn btn-success">Update</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                            </div>
                        </div>
                
                </form>


            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php include '../includes/footer.php' ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- apps -->
    <!-- apps -->
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <script src="../assets/extra-libs/c3/d3.min.js"></script>
    <script src="../assets/extra-libs/c3/c3.min.js"></script>
    <script src="../assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="../assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>

    <script>
    $(document).ready(function(){
        $("select[name='utility_id'], input[name='usage_amount']").on("change keyup", function(){
            var utility_id = $("select[name='utility_id']").val();
            var usage_amount = parseFloat($("input[name='usage_amount']").val());
            
            if (utility_id && !isNaN(usage_amount)) {
                $.ajax({
                    type: "POST",
                    url: "check-availability-admin.php",
                    data: { utility_id: utility_id },
                    dataType: "json",
                    success: function(response){
                        var price1 = parseFloat(response.price_per_unit);
                        var price2 = parseFloat(response.price_per_unit_f2);
                        var price3 = parseFloat(response.price_per_unit_f3);

                        if (!isNaN(price1) && !isNaN(price2) && !isNaN(price3)) {
                            let total = 0;
                            
                            if (usage_amount <= 50) {
                                total = usage_amount * price1;
                            } else if (usage_amount <= 100) {
                                total = 50 * price1 + (usage_amount - 50) * price2;
                            } else {
                                total = 50 * price1 + 50 * price2 + (usage_amount - 100) * price3;
                            }

                            $("input[name='total']").val(total.toFixed(2));
                        }
                    }
                });
            }
        });
    });
    </script>
</body>

</html>