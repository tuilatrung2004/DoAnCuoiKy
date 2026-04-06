<?php
require_once '../functions/functions.php';
require_once __DIR__ . '/../config/config.php';

// Kiểm tra quyền admin
checkAdmin();

require_once __DIR__ . '/header-admin.php';

// Xử lý cập nhật vai trò người dùng
if (isset($_POST['update_role'])) {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];
    
    // Chuyển đổi role thành giá trị is_admin
    $is_admin = 0;
    if ($role === 'admin') {
        $is_admin = 1;
    }
    
    if ($conn->query("UPDATE account SET is_admin = $is_admin WHERE id = $id")) {
        $success = "Cập nhật vai trò thành công!";
    } else {
        $error = "Có lỗi xảy ra khi cập nhật vai trò!";
    }
}

// Xử lý cập nhật số dư
if (isset($_POST['update_balance'])) {
    $id = (int)$_POST['id'];
    $amount = (float)$_POST['amount'];
    $operation = $_POST['operation'];
    
    // Lấy số dư hiện tại
    $balance_query = "SELECT money FROM account WHERE id = $id";
    $balance_result = $conn->query($balance_query);
    $current_balance = $balance_result->fetch_assoc()['money'];
    
    $new_balance = $current_balance;
    if ($operation === 'add') {
        $new_balance += $amount;
    } else if ($operation === 'subtract') {
        $new_balance = max(0, $current_balance - $amount);
    } else if ($operation === 'set') {
        $new_balance = $amount;
    }
    
    if ($conn->query("UPDATE account SET money = $new_balance WHERE id = $id")) {
        $success = "Cập nhật số dư thành công!";
    } else {
        $error = "Có lỗi xảy ra!";
    }
}

// Lấy danh sách người dùng bao gồm cả admin
$users_query = "SELECT * FROM account ORDER BY created_at DESC";
$users = $conn->query($users_query);
?>

<!-- Page content starts here -->
<h2 class="mb-4">Quản lý người dùng</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Số dư</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['fullname'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['phone'] ?></td>
                            <td><?= format_currency($user['money']) ?></td>
                            <td>
                                <span class="badge bg-<?= $user['is_admin'] ? 'success' : 'primary' ?>">
                                    <?= $user['is_admin'] ? 'Admin' : 'Khách hàng' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#userDetail<?= $user['id'] ?>">
                                    <i class="bi bi-eye"></i> Chi tiết
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal Chi tiết người dùng -->
                        <div class="modal fade" id="userDetail<?= $user['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi tiết người dùng</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="user-info mb-4">
                                            <p><strong>Họ tên:</strong> <?= $user['fullname'] ?></p>
                                            <p><strong>Email:</strong> <?= $user['email'] ?></p>
                                            <p><strong>Số điện thoại:</strong> <?= $user['phone'] ?></p>
                                            <p><strong>Địa chỉ:</strong> <?= $user['address'] ?></p>
                                            <p><strong>Số dư:</strong> <?= format_currency($user['money']) ?></p>
                                            <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                                            <p>
                                                <strong>Vai trò hiện tại:</strong> 
                                                <span class="badge bg-<?= $user['is_admin'] ? 'success' : 'primary' ?>">
                                                    <?= $user['is_admin'] ? 'Admin' : 'Khách hàng' ?>
                                                </span>
                                            </p>
                                        </div>
                                        
                                        <hr>
                                        
                                        <!-- Cập nhật vai trò -->
                                        <div class="role-update mb-4">
                                            <h6>Phân quyền người dùng</h6>
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <div class="input-group mb-3">
                                                    <select class="form-select" name="role">
                                                        <option value="customer" <?= !$user['is_admin'] ? 'selected' : '' ?>>Khách hàng</option>
                                                        <option value="admin" <?= $user['is_admin'] ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                    <button class="btn btn-primary" type="submit" name="update_role">
                                                        Cập nhật
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <hr>
                                        
                                        <!-- Cập nhật số dư -->
                                        <div class="balance-update">
                                            <h6>Cập nhật số dư</h6>
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <div class="input-group mb-3">
                                                    <select class="form-select" name="operation">
                                                        <option value="add">Cộng thêm</option>
                                                        <option value="subtract">Trừ đi</option>
                                                        <option value="set">Đặt giá trị</option>
                                                    </select>
                                                    <input type="number" class="form-control" name="amount" 
                                                           placeholder="Số tiền" min="0" required>
                                                    <button class="btn btn-primary" type="submit" name="update_balance">
                                                        Cập nhật
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


