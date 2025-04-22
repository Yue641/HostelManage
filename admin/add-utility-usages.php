<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');

// Xử lý thêm chi tiêu mới
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $utility_id = $_POST['utility_id'];
    $usage_amount = $_POST['usage_amount'];
    $recorded_date = $_POST['recorded_date'];
    $total = $_POST['total'];

    $insert_query = "INSERT INTO utility_usages (room_id,utility_id, usage_amount, recorded_date, total) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param("iidsd", $room_id, $utility_id, $usage_amount, $recorded_date, $total);
    
    if ($stmt->execute()) {
        echo "<script>alert('Utility Usages added successfully!'); window.location.href='manage-utility-usages.php';</script>";
    } else {
        echo "Error adding Utility Usages: " . $mysqli->error;
    }
}

// Lấy danh sách các phòng
$room_query = "SELECT id, room_number FROM rooms";
$room_result = $mysqli->query($room_query);

// Lấy danh sách các tiện ích
$utilities_query = "SELECT id, name, price_per_unit, price_per_unit_f2, price_per_unit_f3 FROM utilities";
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Add Bills</h4>
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


                    <!-- Hiển thị toàn bộ danh sách phòng -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Room Number</h4>
                                <div class="form-group">
                                    <select name="room_id" class="form-control" required>
                                        <option value="">-- Select Room --</option>
                                        <?php 
                                        $room_query = "SELECT id, room_number FROM rooms"; // Truy vấn toàn bộ phòng
                                        $room_result = $mysqli->query($room_query);
                                        while ($room = $room_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $room['id']; ?>">
                                                <?php echo htmlspecialchars($room['room_number']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị toàn bộ danh sách phòng -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Utility</h4>
                                <div class="form-group">
                                    <select name="utility_id" class="form-control" required>
                                        <option value="">-- Select Utility --</option>
                                        <?php 
                                        $utilities_query = "SELECT id, name FROM utilities"; // Truy vấn toàn bộ tiện ích
                                        $utilities_result = $mysqli->query($utilities_query);
                                        while ($utiliies = $utilities_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $utiliies['id']; ?>">
                                                <?php echo htmlspecialchars($utiliies['name']); ?>
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
                                    <input type="number" name="usage_amount" id="usage_amount" placeholder="Enter Usage Amount" required="required" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Total</h4>
                                <div class="form-group">
                                    <input type="number" name="total" id="total" placeholder="Total Amount" class="form-control" readonly>
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
                                    <input type="date" name="recorded_date" required class="form-control">
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