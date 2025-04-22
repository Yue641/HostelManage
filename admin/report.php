<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $selected_month = isset($_POST['month']) ? $_POST['month'] : '';
    $selected_year = isset($_POST['year']) ? $_POST['year'] : '';

    $data = [];  // Đổi tên mảng để tránh trùng với biến kết quả SQL

    if (!empty($selected_month) && !empty($selected_year)) {
        $query = "
            SELECT 
                t.full_name,
                r.room_number,
                b.status,
                SUM(b.total_amount) AS total_amount
            FROM tenants t
            JOIN contracts c ON t.tenant_id = c.tenant_id
            JOIN rooms r ON c.room_id = r.id
            JOIN bills b ON b.room_id = r.id
            WHERE MONTH(b.due_date) = ? AND YEAR(b.due_date) = ?
            GROUP BY t.tenant_id, r.id, b.status
            ORDER BY t.full_name
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $selected_month, $selected_year);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $key = $row['full_name'] . '_' . $row['room_number'];

                if (!isset($data[$key])) {
                    $data[$key] = [
                        'tenant' => $row['full_name'],
                        'room_number' => $row['room_number'],
                        'Paid' => 0,
                        'Unpaid' => 0,
                        'Overdue' => 0,
                    ];
                }

                $status = ucfirst(strtolower($row['status'])); // Đảm bảo: Paid, Unpaid, Overdue
                $data[$key][$status] = $row['total_amount'];
            }
        } else {
            // Xử lý nếu truy vấn thất bại
            echo "Error executing query.";
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Report</h4>
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
                        <a href="report.php" class="btn btn-dark">Reset</a>
                    </div>
                </div>
            </form>

            <?php if (!empty($data)): ?>
                <?php
                // Lưu data vào session để dùng cho export
                $_SESSION['report_data'] = $data;
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="card-title">Result</h2>
                                <hr>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-striped table-hover table-bordered no-wrap">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Tenant</th>
                                                <th>Room Number</th>
                                                <th>Unpaid</th>
                                                <th>Paid</th>
                                                <th>Overdue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 1; ?>
                                        <?php foreach ($data as $entry): ?>
                                            <tr>
                                                <td><?= $count++ ?></td>
                                                <td><?= htmlspecialchars($entry['tenant']) ?></td>
                                                <td><?= htmlspecialchars($entry['room_number']) ?></td>
                                                <td><?= number_format($entry['Unpaid'], 0, ',', '.') ?> </td>
                                                <td><?= number_format($entry['Paid'], 0, ',', '.') ?> </td>
                                                <td><?= number_format($entry['Overdue'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="text-center">
                                        <form action="export/export_report.php" method="post">
                                            <button type="submit" name="export" value="excel" class="btn btn-success">Excel Export</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p>No data available</p>
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