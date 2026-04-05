<?php
require_once './config/config.php';
require_once './functions/functions.php'; 


if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($conn, $_POST['username']);
    $email = escape($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fullname = escape($conn, $_POST['fullname']);
    $phone = escape($conn, $_POST['phone']);
    $address = escape($conn, $_POST['address']);
    

    $recaptcha_secret = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"; // Test secret key
    $recaptcha_response = $_POST['g-recaptcha-response'];
    
    $is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');
    
    if ($is_localhost || $recaptcha_response) {
        $verify_success = true;
        if (!$is_localhost) {
            $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
            $response_data = json_decode($verify_response);
            $verify_success = $response_data->success;
        }
        
        if (!$verify_success) {
            $error = "Vui lòng xác minh bạn không phải là robot!";
        } else {
            if ($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp!";
            } else {
                $check_username = $conn->query("SELECT id FROM account WHERE username = '$username'");
                if ($check_username->num_rows > 0) {
                    $error = "Tên đăng nhập đã tồn tại!";
                } else {
                    $check_email = $conn->query("SELECT id FROM account WHERE email = '$email'");
                    if ($check_email->num_rows > 0) {
                        $error = "Email đã được sử dụng!";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $query = "INSERT INTO account (username, password, email, fullname, phone, address, created_at) 
                                 VALUES ('$username', '$hashed_password', '$email', '$fullname', '$phone', '$address', NOW())";
                        
                        if ($conn->query($query)) {
                            $_SESSION['register_success'] = true;
                            header('Location: login.php');
                            exit();
                        } else {
                            $error = "Lỗi: " . $conn->error;
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Web Bán Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .register-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header img {
            max-width: 150px;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #4e73df;
        }
        .btn-primary {
            background: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background: #2e59d9;
            border-color: #2e59d9;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="register-container">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="register-header">
                    <img src="/images/logo.png" alt="Logo" class="img-fluid">
                        <h4>Đăng ký tài khoản</h4>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user'></i></span>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user-circle'></i></span>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map'></i></span>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>
                        </div>

                        

                        <div class="mb-3">
                            <div class="g-recaptcha text-center" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>

                    <div class="text-center mt-3">
                        Đã có tài khoản? <a href="login.php" class="text-decoration-none">Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 