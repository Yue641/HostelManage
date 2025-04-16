<?php
session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');

// Lấy dữ liệu hợp đồng cần chỉnh sửa
if (isset($_GET['id'])) {
    $contract_id = $_GET['id'];
    
    // Câu truy vấn JOIN để lấy full_name từ tenants và room_number từ rooms
    $query = "
        SELECT c.*, c.monthly_rent, t.full_name, r.room_number, c.start_date, c.end_date, c.status
        FROM contracts c
        JOIN tenants t ON c.tenant_id = t.tenant_id
        JOIN rooms r ON c.room_id = r.id
        WHERE c.id = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $contract_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contract = $result->fetch_assoc();

    if (!$contract) {
        die("Contract not found.");
    }
} else {
    die("Invalid contract ID");
}

// Xử lý cập nhật hợp đồng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : (isset($contract['start_date']) ? $contract['start_date'] : '');
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : (isset($contract['end_date']) ? $contract['end_date'] : '');
    $status = $_POST['status'];

    $update_query = "UPDATE contracts SET start_date = ?, end_date = ?, status = ? WHERE id = ?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("sssi", $start_date, $end_date, $status, $contract_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Contract updated successfully!'); window.location.href='manage-booking.php';</script>";
    } else {
        echo "Error updating contract: " . $mysqli->error;
    }
}
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Bookings</h4>
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
                

                <!-- <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Bookings</h4>
                    </div> -->

                
                <div class="row">


                    <!-- Chọn phòng (chỉ hiển thị phòng available) -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Room</h4>
                                <div class="form-group">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($contract['room_number']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị tên khách thuê -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Tenant</h4>
                                <div class="form-group">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($contract['full_name']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ngày bắt đầu -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Start Date</h4>
                                <div class="form-group">
                                    <input type="date" name="start_date" class="form-control"
                                        value="<?php echo isset($contract['start_date']) ? htmlspecialchars($contract['start_date']) : ''; ?>" 
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ngày kết thúc -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">End Date</h4>
                                <div class="form-group">
                                    <input type="date" name="end_date" class="form-control"
                                        value="<?php echo isset($contract['end_date']) ? htmlspecialchars($contract['end_date']) : ''; ?>" 
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Đặt cọc -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Deposit</h4>
                                <div class="form-group">
                                <input type="number" class="form-control" value="<?php echo htmlspecialchars($contract['deposit']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                            <!-- Hiển thị Monthly Rent -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Monthly Rent</h4>
                                <div class="form-group">
                                    <input type="text" name="monthly_rent" id="monthly_rent" class="form-control" 
                                    value="<?php echo isset($contract['monthly_rent']) ? $contract['monthly_rent'] : ''; ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Status</h4>
                                <select name="status" class="form-control" required> 
                                <option value="<?php echo htmlspecialchars('active'); ?>" 
                                    <?php echo (!empty($row) && $row->status === 'active') ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="<?php echo htmlspecialchars('expired'); ?>" 
                                    <?php echo (!empty($row) && $row->status === 'expired') ? 'selected' : ''; ?>>
                                    Expired
                                </option>
                                <option value="<?php echo htmlspecialchars('terminated'); ?>" 
                                    <?php echo (!empty($row) && $row->status === 'terminated') ? 'selected' : ''; ?>>
                                    Terminated
                                </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>



                    <div class="form-actions">
                        <div class="text-center">
                            <button type="submit" name="submit" class="btn btn-success">Submit</button>
                            <button type="reset" class="btn btn-dark">Reset</button>
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

    <!-- Custom Ft. Script Lines -->    
<script type="text/javascript">
	$(document).ready(function(){
        $('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#paddress').val( $('#address').val() );
                $('#pcity').val( $('#city').val() );
                $('#ppincode').val( $('#pincode').val() );
            } 
            
        });
    });
    </script>

    
    
    <script>

        $(document).ready(function(){
            $("#room_id").change(function(){
                var room_id = $(this).val();
                if (room_id) {
                    $.ajax({
                        type: "POST",
                        url: "get-room-price.php",  // File xử lý lấy giá thuê phòng
                        data: { room_id: room_id },
                        success: function(response){
                            $("#monthly_rent").val(response); // Hiển thị giá thuê trong input
                        }
                    });
                } else {
                    $("#monthly_rent").val("");
                }
            });
        });


        function checkAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
        url: "check-availability.php",
        data:'roomno='+$("#room").val(),
        type: "POST",
        success:function(data){
            $("#room-availability-status").html(data);
            $("#loaderIcon").hide();
        },
            error:function (){}
            });
        }
    </script>


    <script type="text/javascript">

    $(document).ready(function() {
        $('#duration').keyup(function(){
            var fetch_dbid = $(this).val();
            $.ajax({
            type:'POST',
            url :"ins-amt.php?action=userid",
            data :{userinfo:fetch_dbid},
            success:function(data){
            $('.result').val(data);
            }
            });
            

    })});
    </script>


</body>

</html>