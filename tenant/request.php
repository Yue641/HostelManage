<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login-tenant.php');
check_login();

$tenant_id = $_SESSION['tenant_id'];

$message = "";

// Lấy room_id từ bảng contracts
$query = "SELECT room_id FROM contracts WHERE tenant_id = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    die("Lỗi truy vấn: " . $mysqli->error);
}

$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $room_id = $row['room_id'];
} else {
    $room_id = null;
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && $room_id) {
    $description = htmlspecialchars(trim($_POST['description']));
    $status = "pending"; // Trạng thái mặc định

    if (empty($description)) {
        $message = "<div class='alert alert-danger'>Vui lòng nhập mô tả!</div>";
    } else {
        $insertQuery = "INSERT INTO maintenance_requests (room_id, tenant_id, description, request_date, status) VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $mysqli->prepare($insertQuery);

        if (!$stmt) {
            die("Lỗi chuẩn bị truy vấn: " . $mysqli->error);
        }

        $stmt->bind_param("iiss", $room_id, $tenant_id, $description, $status);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Yêu cầu của bạn đã được gửi!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi khi gửi yêu cầu: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

if(isset($_GET['del']))
{
    $id=intval($_GET['del']);
    $adn="DELETE from maintenance_requests where id=?";
        $stmt= $mysqli->prepare($adn);
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();	   
        echo "<script>alert('Request has been deleted');</script>" ;
}
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
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.js"></script>


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
                
            <form id="maintenanceForm" method="POST">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Description</h4>
                            <div class="form-group">
                                <div id="description" class="editable-div" contenteditable="true" data-placeholder="Nhập mô tả tại đây..."></div>
                                <input type="hidden" name="description" id="hiddenDescription">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Send</button>
                        <button type="button" id="resetBtn" class="btn btn-danger">Reset</button>
                    </div>
                </div>
            </form>
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
                                                <th>ID</th>
                                                <th>Room</th>
                                                <th>Tenant</th>
                                                <th>Description</th>
                                                <th>Request Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php	
                                            $aid=$_SESSION['tenant_id'];
                                            $ret="SELECT m.id, r.room_number, t.full_name, 
                                                         m.description, m.request_date, m.status 
                                                  FROM maintenance_requests m
                                                  JOIN rooms r ON m.room_id = r.id
                                                  JOIN tenants t ON m.tenant_id = t.tenant_id";
                                            $stmt= $mysqli->prepare($ret) ;
                                            $stmt->execute() ;
                                            $res=$stmt->get_result();
                                            $cnt=1;
                                            while($row=$res->fetch_object())
                                                {
                                                    ?>
                                        <tr><td><?php echo $cnt;;?></td>
                                        <td><?php echo $row->id;?></td>
                                        <td><?php echo $row->room_number;?></td>
                                        <td><?php echo $row->full_name;?></td>
                                        <td><?php echo $row->description;?></td>
                                        <td><?php echo $row->request_date;?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $row->status)); ?></td>
                                        </tr>
                                            <?php
                                                $cnt=$cnt+1;
                                            } ?>
									    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Table Ends -->

        </div>

                <script>
                    document.getElementById("maintenanceForm").addEventListener("submit", function(event) {
                        let descDiv = document.getElementById("description");
                        let hiddenInput = document.getElementById("hiddenDescription");

                        // Lấy nội dung từ div và gán vào input hidden
                        hiddenInput.value = descDiv.innerText.trim();

                        if (hiddenInput.value === "") {
                            alert("Vui lòng nhập mô tả!");
                            event.preventDefault(); // Chặn form submit nếu mô tả trống
                        }
                    });

                    // Xóa placeholder khi nhập nội dung
                    document.getElementById("description").addEventListener("input", function() {
                        if (this.innerText.trim() === "") {
                            this.setAttribute("data-placeholder", "Nhập mô tả tại đây...");
                        } else {
                            this.removeAttribute("data-placeholder");
                        }
                    });

                    // Nút Reset: Xóa nội dung của div contenteditable
                    document.getElementById("resetBtn").addEventListener("click", function() {
                        let descDiv = document.getElementById("description");
                        descDiv.innerText = "";
                        descDiv.setAttribute("data-placeholder", "Nhập mô tả tại đây...");
                    });
                </script>

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
    <script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>


</body>



    <style>
        .editable-div {
            width: 100%;
            min-height: 150px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            background-color: #fff;
            overflow-y: auto;
        }

        /* Hiển thị placeholder khi div rỗng */
        .editable-div:empty:before {
            content: attr(data-placeholder);
            color: #999; /* Màu placeholder */
            font-style: italic;
        }

        /* Khi người dùng nhập nội dung, placeholder sẽ biến mất */
        .editable-div:focus {
            outline: none;
        }
    </style>

</html>