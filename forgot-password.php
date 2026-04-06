<?php
require_once './config/config.php';
require_once './functions/functions.php';
require_once 'Header.php';

require './vendor/autoload.php';
require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $gmail = $_POST['email'];

    $checkQuery = "SELECT * FROM account WHERE username = ? AND email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $gmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $newPassword = generateRandomPassword();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateQuery = "UPDATE account SET password = ? WHERE username = ? AND email = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sss", $hashedPassword, $username, $gmail);

        if ($updateStmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ngocrongdragonking@gmail.com'; 
                $mail->Password = 'erlthhyxknomaxgx'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('hoangbach7718@gmail.com', 'Cửa hàng nước hoa'); 
                $mail->addAddress($gmail);
                $mail->Subject = '=?UTF-8?B?' . base64_encode('Quên Mật Khẩu - Cửa hàng nước hoa') . '?=';
                $mail->Body = "Xin chào bạn,\n\nTài khoản $username đang thực hiện Quên mật khẩu.\n\nThông tin tài khoản của bạn:\n- Tài khoản: $username \n- Mật khẩu mới: $newPassword \n\nAdmin chân thành cảm ơn bạn đã tin tưởng và đồng hành cùng Chúng tôi";
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->send();
                echo '<div class="alert alert-success">Mật khẩu mới đã được gửi đến email của bạn!</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">Có lỗi khi gửi email: ' . $mail->ErrorInfo . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Lỗi khi cập nhật mật khẩu: ' . $conn->error . '</div>';
        }
        $updateStmt->close();
    } else {
        echo '<div class="alert alert-danger">Không tìm thấy tài khoản với thông tin này!</div>';
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu</title>
    <link rel="icon" type="image/x-icon" href="assets/images/icon/icon.ico">


    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <!-- sweetalert -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- myjs -->
    <!--<script src="js/tet.js"></script>-->

    <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
	<style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-align: center;
            background-color:rgb(54, 12, 225);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color:rgb(34, 14, 213);
        }
    </style>
</head>
<div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <h4 class="text-center">QUÊN MẬT KHẨU</h4>
                
                <form id="form" method="POST">
                    <div class="form-group">
                        <label>Tài khoản:</label>
                        <input class="form-control" type="text" name="username" id="username"
                            placeholder="Nhập tên tài khoản">
                    </div>
                    <div class="form-group">
                        <label>Gmail:</label>
                        <input class="form-control" type="email" name="email" id="email"
                            placeholder="Nhập Gmail của bạn">
                    </div>
                    <div id="notify" class="text-danger pb-2 font-weight-bold"></div>
                    <button class="btn btn-main form-control" type="submit">XÁC NHẬN</button>
                </form>
                <br>
                <div class="text-center">
                    <p>Bạn đã lấy lại tài khoản? <a href="/dang-nhap">Đăng nhập tại đây</a></p>
                </div>
            </div>
        </div>
    </div>
<footer class="mt-1">


    <div class="text-center mt-1">
        <b style="font-size:13px;" class="text-white">Tham gia cộng đồng giao lưu game với chúng tớ.</b>
        <br>
        <a href="" target="_blank"><img src="assets/images/icon/findondiscord.png" style="max-width:100px" class="mt-1"></a>
        <a href="https://www.facebook.com/groups/ngocronghades" target="_blank"><img src="assets/images/icon/findonfb.png" style="max-width:100px" class="mt-1"></a>
    </div>
    <div class="text-center text-white">
        Trò chơi không có bản quyền chính thức, hãy cân nhắc kỹ trước khi tham gia.<br> Chơi quá 180 phút một ngày sẽ ảnh hưởng đến sức khỏe.
    </div>
</footer>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="asset/main.js"></script>
</body>

</html>
<?php
require_once 'footer.php';
?>