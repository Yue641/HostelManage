<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login-tenant.php');
check_login();

$tenant_id = $_SESSION['tenant_id'];


?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<!-- By CodeAstro - codeastro.com -->
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
             <?php include '../includes/tenant-navigation.php'?>
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
                <?php include '../includes/tenant-sidebar.php'?>
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
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                
                <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Check Bills</h4>
                </div>



                <!-- Table Starts -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <hr>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-striped table-hover table-bordered no-wrap">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Room</th>
                                                <th>Amount</th>
                                                <th>Issue Date</th>
                                                <th>Due Date</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php

                                            // Lấy room_id từ bảng contracts theo tenant_id
                                            $query = "SELECT room_id FROM contracts WHERE tenant_id = ?";
                                            $stmt = $mysqli->prepare($query);
                                            $stmt->bind_param("i", $tenant_id);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            $contract = $res->fetch_assoc();
                                            
                                            if ($contract) {
                                                $room_id = $contract['room_id'];

                                                // Lấy dữ liệu từ bảng bills dựa vào room_id
                                                $query = "SELECT b.id, r.room_number, b.total_amount,b.issue_date, b.due_date,b.description, b.status 
                                                        FROM bills b
                                                        JOIN rooms r ON b.room_id = r.id
                                                        WHERE b.room_id = ?";
                                                $stmt = $mysqli->prepare($query);
                                                $stmt->bind_param("i", $room_id);
                                                $stmt->execute();
                                                $res = $stmt->get_result();

                                                $cnt = 1;
                                                while ($row = $res->fetch_object()) {
                                        ?>
                                            <tr>
                                                <td><?php echo $cnt; ?></td>
                                                <td><?php echo $row->room_number; ?></td>
                                                <td><?php echo number_format($row->total_amount, 2); ?> VND</td>
                                                <td><?php echo $row->issue_date; ?></td>
                                                <td><?php echo $row->due_date; ?></td>
                                                <td><?php echo $row->description; ?></td>
                                                <td><?php echo ucwords(str_replace('_', ' ', $row->status)); ?></td>
                                                <td>
                                                    <?php if ($row->status == "unpaid" || $row->status == "overdue") { ?>
                                                        <a href="pay_cash.php?bill_id=<?php echo $row->id; ?>" class="btn btn-success btn-sm">
                                                            Cash
                                                        </a>
                                                        <a href="pay_bank.php?bill_id=<?php echo $row->id; ?>" class="btn btn-success btn-sm">
                                                            Bank-Transfer
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                                    $cnt++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center'>No bill!</td></tr>";
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table Ends -->




                <!-- Table column end -->

            </div><!-- By CodeAstro - codeastro.com -->
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
</body>

</html>