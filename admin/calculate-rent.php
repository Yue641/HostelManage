<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    // Lấy danh sách phòng từ bảng rooms
    $rooms_query = "SELECT id, room_number FROM rooms";
    $rooms_result = $mysqli->query($rooms_query);

    // Lấy danh sách tất cả dịch vụ từ bảng utilities
    $utility_names = [];
    $utility_query = "SELECT id, name FROM utilities";
    $utility_result = $mysqli->query($utility_query);
    while ($utility = $utility_result->fetch_assoc()) {
        $utility_names[$utility['id']] = $utility['name'];
    }

    // Xử lý khi người dùng bấm Submit
    $selected_room = isset($_POST['room_id']) ? $_POST['room_id'] : '';
    $selected_month = isset($_POST['month']) ? $_POST['month'] : "";
    $selected_year = isset($_POST['year']) ? $_POST['year'] : "";

    $result = null;
    if (!empty($selected_room)) {
        $query = "SELECT 
                    r.id AS room_id,
                    r.room_number,
                    r.price AS room_price,
                    u.utility_id,
                    ut.name AS utility_name,
                    u.usage_amount,
                    u.total AS utility_cost,
                    (r.price + COALESCE(u.total, 0)) AS total_cost
                FROM rooms r
                LEFT JOIN utility_usages u ON r.id = u.room_id 
                    AND MONTH(u.recorded_date) = ? 
                    AND YEAR(u.recorded_date) = ?
                LEFT JOIN utilities ut ON u.utility_id = ut.id
                WHERE r.id = ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iii", $selected_month, $selected_year, $selected_room);
        $stmt->execute();
        $result = $stmt->get_result();
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

     <!-- This page plugin CSS -->
     <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">

    <script language="javascript" type="text/javascript">
    var popUpWin=0;
    function popUpWindow(URLStr, left, top, width, height){
        if(popUpWin) {
         if(!popUpWin.closed) popUpWin.close();
            }
            popUpWin = open(URLStr,'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width='+510+',height='+430+',left='+left+', top='+top+',screenX='+left+',screenY='+top+'');
        }
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Calculate Month Rent</h4>
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

            <form method="POST" action="">
                <div class="row">
                    <!-- Chọn phòng -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Room</h4>
                                <div class="form-group">
                                    <select name="room_id" class="form-control" required>
                                        <option value="">-- Select Room --</option>
                                        <?php while ($room = $rooms_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $room['id']; ?>" <?= ($room['id'] == $selected_room) ? 'selected' : '' ?>>
                                                Room <?php echo $room['room_number']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn tháng -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Month</h4>
                                <div class="form-group">
                                    <select name="month" class="form-control">
                                        <option value="">-- Select Month --</option>
                                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                                            <option value="<?= $m ?>" <?= ($m == $selected_month) ? 'selected' : '' ?>><?= $m ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn năm -->
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Year</h4>
                                <div class="form-group">
                                    <select name="year" class="form-control">
                                        <option value="">-- Select Year --</option>
                                        <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++) { ?>
                                            <option value="<?= $y ?>" <?= ($y == $selected_year) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút Submit và Reset -->
                <div class="form-actions">
                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-success">Submit</button>
                        <a href="calculate-rent.php" class="btn btn-dark">Reset</a>
                    </div>
                </div>
            </form>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="card-title">Kết Quả Hóa Đơn</h2>
                                <hr>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-striped table-hover table-bordered no-wrap">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Room Number</th>
                                                <th>Room Rent</th>
                                                <th>Electricity</th>
                                                <th>Water</th>
                                                <th>Internet</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $cnt = 1;
                                            $processed_rooms = []; // Mảng lưu các phòng đã xử lý

                                            while ($row = $result->fetch_assoc()): 
                                                $room_id = $row['room_id'];

                                                // Kiểm tra nếu phòng đã được xử lý, bỏ qua để tránh trùng
                                                if (in_array($room_id, $processed_rooms)) {
                                                    continue;
                                                }

                                                // Lưu phòng đã xử lý để không bị lặp
                                                $processed_rooms[] = $room_id;

                                                // Mặc định giá trị của điện, nước, internet là 0
                                                $electricity_cost = 0;
                                                $water_cost = 0;
                                                $internet_cost = 0;

                                                // Truy vấn danh sách dịch vụ cho từng phòng
                                                $utilities_sql = "SELECT utility_id, total FROM utility_usages WHERE room_id = ?";
                                                $stmt = $mysqli->prepare($utilities_sql);
                                                $stmt->bind_param("i", $room_id);
                                                $stmt->execute();
                                                $utilities_result = $stmt->get_result();

                                                while ($utility = $utilities_result->fetch_assoc()) {
                                                    if ($utility['utility_id'] == 1) {
                                                        $electricity_cost = $utility['total'];
                                                    } elseif ($utility['utility_id'] == 2) {
                                                        $water_cost = $utility['total'];
                                                    } elseif ($utility['utility_id'] == 3) {
                                                        $internet_cost = $utility['total'];
                                                    }
                                                }

                                                $total_cost = $row['room_price'] + $electricity_cost + $water_cost + $internet_cost;
                                            ?>
                                                <tr>
                                                    <td><?= $cnt ?></td>
                                                    <td><?= $row['room_number'] ?></td>
                                                    <td><?= number_format($row['room_price'], 0, ',', '.') ?> </td>
                                                    <td><?= number_format($electricity_cost, 0, ',', '.') ?> </td>
                                                    <td><?= number_format($water_cost, 0, ',', '.') ?> </td>
                                                    <td><?= number_format($internet_cost, 0, ',', '.') ?> </td>
                                                    <td><strong><?= number_format($total_cost, 0, ',', '.') ?> </strong></td>
                                                </tr>
                                            <?php 
                                                $cnt++;
                                            endwhile; ?>
                                        </tbody>
                                    </table>
                                    <div class="text-center">
                                        <form action="export/export_rent.php" method="post">
                                            <button type="submit" name="export" value="word" class="btn btn-primary">Word Export</button>
                                            <button type="submit" name="export" value="excel" class="btn btn-success">Excel Export</button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

</body>

</html>