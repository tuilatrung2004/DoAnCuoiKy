<?php
require_once 'Header.php';
require_once './config/config.php';
require_once './functions/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';



    if (!isset($error)) {
        $sql = "UPDATE account SET 
                fullname = ?, 
                email = ?, 
                phone = ?,
                address = ? 
                $password_query
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $fullname, $email, $phone, $address, $user_id);

        if ($stmt->execute()) {
            $_SESSION['fullname'] = $fullname;
            $success = "Cập nhật thông tin thành công!";
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại!";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM account WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Avatar">
                    <h5 class="card-title"><?= htmlspecialchars($user['fullname']) ?></h5>
                    <p class="text-muted"><?= $user['email'] ?></p>

                    <div class="mt-3">
                        <a href="purchase-history.php" class="btn btn-outline-primary">
                            <i class="bx bx-history"></i> Lịch sử mua hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin tài khoản</h5>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="fullname" class="form-control"
                                value="<?= htmlspecialchars($user['fullname']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address" class="form-control"
                                rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>



                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'Footer.php'; ?>