<?php
session_start();
include('../includes/dbconn.php');

if(isset($_POST['submit']))
{
    // Lấy dữ liệu từ form
    $room_id = $_POST['room_id'];
    $tenant_id = $_POST['tenant_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $deposit = $_POST['deposit'];

    // Kiểm tra phòng có trạng thái 'available' và lấy giá thuê (price)
    $query_room = "SELECT price FROM rooms WHERE id = ? AND status = 'available'";
    $stmt_room = $mysqli->prepare($query_room);
    $stmt_room->bind_param('i', $room_id);
    $stmt_room->execute();
    $result = $stmt_room->get_result();

    if ($result->num_rows > 0) {
        $room_data = $result->fetch_assoc();
        $monthly_rent = $room_data['price']; // Lấy giá thuê từ bảng rooms

        // Tiến hành tạo hợp đồng
        $query_contract = "INSERT INTO contracts (room_id, tenant_id, start_date, end_date, deposit, monthly_rent, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
        $stmt_contract = $mysqli->prepare($query_contract);
        $stmt_contract->bind_param('iissdd', $room_id, $tenant_id, $start_date, $end_date, $deposit, $monthly_rent);
        $stmt_contract->execute();

        // Cập nhật trạng thái phòng thành 'occupied'
        $query_update_room = "UPDATE rooms SET status = 'occupied' WHERE id = ?";
        $stmt_update = $mysqli->prepare($query_update_room);
        $stmt_update->bind_param('i', $room_id);
        $stmt_update->execute();

        echo "<script>alert('Contract has been created and room status updated!');</script>";
    } else {
        echo "<script>alert('Selected room is not available!');</script>";
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

    <script>

    </script>
    
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Bookings</h4>
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
                                <h4 class="card-title">Select Room</h4>
                                <div class="form-group">
                                    <select name="room_id" id="room_id" class="form-control" required>
                                        <option value="">-- Select Available Room --</option>
                                        <?php
                                        include('../includes/dbconn.php');
                                        $query = "SELECT id, room_number FROM rooms WHERE status = 'available'";
                                        $result = $mysqli->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>Room {$row['room_number']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn khách thuê (chỉ hiển thị tenant chưa có hợp đồng) -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Select Tenant</h4>
                                <div class="form-group">
                                    <select name="tenant_id" id="tenant_id" class="form-control" required>
                                        <option value="">-- Select Tenant --</option>
                                        <?php
                                        $query = "SELECT t.tenant_id, t.full_name FROM tenants t 
                                                LEFT JOIN contracts c ON t.tenant_id = c.tenant_id 
                                                WHERE c.tenant_id IS NULL";
                                        $result = $mysqli->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
                                        }
                                        ?>
                                    </select>
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
                                    <input type="date" name="start_date" required class="form-control">
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
                                    <input type="date" name="end_date" required class="form-control">
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
                                    <input type="number" name="deposit" step="0.01" required class="form-control">
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
                                    <input type="text" name="monthly_rent" id="monthly_rent" class="form-control" readonly>
                                </div>
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