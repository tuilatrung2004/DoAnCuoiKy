<?php
require_once './config/config.php';
require_once './functions/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM account WHERE username = '$username'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $user_query = "SELECT * FROM account WHERE id = " . $user['id'];
            $user_result = $conn->query($user_query);
            $user_data = $user_result->fetch_assoc();
            
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['is_admin'] = $user_data['is_admin'];
            $_SESSION['fullname'] = $user_data['fullname'];
            $_SESSION['money'] = floatval($user_data['money']);

            if ($user_data['is_admin']) {
                header('Location: ../admin/');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Web Bán Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
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
        .social-login {
            text-align: center;
            margin-top: 2rem;
        }
        .social-login .btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
            line-height: 40px;
            margin: 0 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="login-container">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="login-header">
                        <a href="index.php"><img src="/images/logo.png" alt="Logo" class="img-fluid"></a>
                        <h4>Đăng nhập</h4>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['register_success']) && $_SESSION['register_success']): ?>
                        <div class="alert alert-success">Đăng ký tài khoản thành công! Vui lòng đăng nhập để tiếp tục.</div>
                        <?php unset($_SESSION['register_success']); ?>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="forgot-password.php" class="text-decoration-none">Quên mật khẩu?</a>
                    </div>

                    <hr>

                    <div class="text-center">
                        Chưa có tài khoản? <a href="register.php" class="text-decoration-none">Đăng ký ngay</a>
                    </div>

                    <div class="social-login">
                        <p class="text-muted">Hoặc đăng nhập với</p>
                        <div>
                            <a href="#" class="btn btn-outline-primary"><i class='bx bxl-facebook'></i></a>
                            <a href="#" class="btn btn-outline-danger"><i class='bx bxl-google'></i></a>
                            <a href="#" class="btn btn-outline-info"><i class='bx bxl-twitter'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 