<?php
require_once 'Header.php';
require_once './config/config.php';
require_once './functions/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$stmt = $conn->prepare("SELECT * , order_items.price as price_detail FROM orders inner join order_items on orders.id = order_items.order_id WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="container py-5">
    <h2 class="mb-4">Lịch sử mua hàng</h2>
    
    <div class="card shadow-lg border-0 rounded">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bx bx-history me-2"></i> Danh sách đơn hàng</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark text-white">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <span class="fw-bold text-success"><?= format_currency($order['price'] * $order['quantity']) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            switch ($order['payment_method']) {
                                                case 'wallet':
                                                    echo '<span class="badge bg-info"><i class="bx bx-wallet me-1"></i> Số dư</span>';
                                                    break;
                                                case 'cod':
                                                    echo '<span class="badge bg-warning text-dark"><i class="bx bx-money me-1"></i> COD</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary">' . $order['payment_method'] . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            switch ($order['order_status']) {
                                                case 'pending':
                                                    echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                                                    break;
                                                case 'processing':
                                                    echo '<span class="badge bg-info">Đang xử lý</span>';
                                                    break;
                                                case 'shipped':
                                                    echo '<span class="badge bg-primary">Đang giao</span>';
                                                    break;
                                                case 'delivered':
                                                    echo '<span class="badge bg-success">Đã giao</span>';
                                                    break;
                                                case 'cancelled':
                                                    echo '<span class="badge bg-danger">Đã hủy</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary">' . $order['order_status'] . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-detail"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bx bx-package" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="mt-3 text-muted">Bạn chưa có đơn hàng nào</p>
                    <a href="products.php" class="btn btn-primary mt-2">
                        <i class="bx bx-shopping-bag me-1"></i> Mua sắm ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'Footer.php'; ?> 