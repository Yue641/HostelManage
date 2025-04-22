<?php
require '../../includes/dbconn.php'; // Kết nối CSDL

if (isset($_GET['id'])) {
    $contract_id = intval($_GET['id']);

    $query = "SELECT t.full_name, t.address, t.id_card_number, t.phone,
                     r.room_number, c.monthly_rent, c.deposit, c.start_date, c.end_date
              FROM contracts c
              JOIN tenants t ON c.tenant_id = t.tenant_id
              JOIN rooms r ON c.room_id = r.id
              WHERE c.id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $contract_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        // Xử lý format ngày tháng và tiền
        $data['monthly_rent'] = number_format($data['monthly_rent'], 0, ',', '.') . ' đ/tháng';
        $data['deposit'] = number_format($data['deposit'], 0, ',', '.') . ' đ';
        $data['start_date'] = date("d/m/Y", strtotime($data['start_date']));
        $data['end_date'] = date("d/m/Y", strtotime($data['end_date']));

        // Đọc file mẫu
        $templateContent = file_get_contents('78d139ea-51a4-4a20-9389-c0d126dc064d.docx');

        // File Word thực tế là file nén nên ta không thay trực tiếp được trong .docx —> Giải pháp là dùng HTML
        ob_start();
        ?>
        <!DOCTYPE html>
        
        <html>
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            <title>Hợp đồng thuê phòng trọ</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; line-height: 1.6; }
                .center { text-align: center; }
                .signature {
                    margin-top: 80px;
                    display: flex;
                    justify-content: space-between;
                    width: 100%;
                }
                .signature div {
                    width: 40%;
                    text-align: center;
                }
                .signature .title {
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
        <div class="center">
            <h3>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h3>
            <p><strong>Độc lập – Tự do – Hạnh phúc</strong></p>
            <h2>HỢP ĐỒNG THUÊ PHÒNG TRỌ</h2>
        </div>

        <p>Hôm nay ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>; tại địa chỉ: Hà Nội</p>
        <p>Chúng tôi gồm:</p>

        <p><strong>1. Đại diện bên cho thuê phòng trọ (Bên A):</strong></p>
        <p>Ông/bà: Lương Ngọc Văn, Sinh ngày: 11/10/2003.</p>
        <p>Nơi đăng ký HK: Xuân Tiến- Xuân Trường- Nam Định</p>
        <p>CMND số: 036203013088</p>
        <p>Số điện thoại: 0879273981</p>

        <p><strong>2. Bên thuê phòng trọ (Bên B):</strong></p>
        <p>Ông/bà: <?= $data['full_name'] ?></p>
        <p>Nơi đăng ký HK thường trú: <?= $data['address'] ?></p>
        <p>Số CMND: <?= $data['id_card_number'] ?></p>
        <p>Số điện thoại: <?= $data['phone'] ?></p>

        <p>Sau khi bàn bạc trên tinh thần dân chủ, hai bên cùng có lợi, cùng thống nhất như sau:</p>
        <p>Bên A đồng ý cho bên B thuê phòng số: <?= $data['room_number'] ?></p>
        <p>Giá thuê: <?= $data['monthly_rent'] ?></p>
        <p>Tiền đặt cọc: <?= $data['deposit'] ?></p>
        <p>Hợp đồng có giá trị kể từ <?= $data['start_date'] ?> đến <?= $data['end_date'] ?></p>

        <h4>TRÁCH NHIỆM CỦA CÁC BÊN</h4>
        <p><strong>Trách nhiệm của bên A:</strong></p>
        <ul>
            <li>Tạo mọi điều kiện thuận lợi để bên B thực hiện theo hợp đồng.</li>
            <li>Cung cấp nguồn điện, nước, wifi cho bên B sử dụng.</li>
        </ul>
        <p><strong>Trách nhiệm của bên B:</strong></p>
        <ul>
            <li>Thanh toán đầy đủ các khoản tiền theo đúng thỏa thuận.</li>
            <li>Bảo quản các trang thiết bị và cơ sở vật chất của bên A.</li>
            <li>Không được tự ý sửa chữa khi chưa có sự đồng ý của bên A.</li>
            <li>Giữ gìn vệ sinh phòng trọ.</li>
            <li>Tuân thủ pháp luật và quy định địa phương.</li>
            <li>Nếu có khách ở qua đêm phải báo trước và chịu trách nhiệm.</li>
        </ul>

        <h4>TRÁCH NHIỆM CHUNG</h4>
        <ul>
            <li>Hai bên phải tạo điều kiện cho nhau thực hiện hợp đồng.</li>
            <li>Vi phạm hợp đồng sẽ bị chấm dứt và bồi thường.</li>
            <li>Nếu chấm dứt trước thời hạn phải báo trước 30 ngày.</li>
            <li>Bên A phải trả lại tiền đặt cọc.</li>
            <li>Vi phạm sẽ chịu trách nhiệm trước pháp luật.</li>
        </ul>

        <p>Hợp đồng được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ một bản.</p>

        <div class="signature">
            <div>
                <p class="title">Bên A</p>
                <p>Đã ký và ghi rõ họ tên</p>
                <br><br><br><br><br><br><br><br>
            </div>
            <div>
                <p class="title">Bên B</p>
                <p>Đã ký và ghi rõ họ tên</p>
                <br><br><br><br>
            </div>
        </div>

        </body>
        </html>
        <?php
        $content = ob_get_clean();

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=Hop_dong_{$contract_id}.doc");
        echo $content;
        exit;
    } else {
        echo "Không tìm thấy hợp đồng.";
    }
} else {
    echo "Thiếu ID hợp đồng.";
}

