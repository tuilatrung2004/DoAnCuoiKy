<?php
require_once 'Header.php';
require_once './config/config.php';
require_once './functions/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('purchase-history.php');
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Kiểm tra đơn hàng thuộc về người dùng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('purchase-history.php');
}

// Lấy chi tiết sản phẩm trong đơn hàng
$stmt = $conn->prepare("SELECT oi.*, p.name_product, p.link_product 
                       FROM order_items oi 
                       LEFT JOIN all_product p ON oi.product_id = p.id_product 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

// Tính tổng tiền đơn hàng
$total = 0;
$items = array();
while ($item = $order_items->fetch_assoc()) {
    $total += $item['price'] * $item['quantity'];
    $items[] = $item;
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chi tiết đơn hàng #<?= $order_id ?></h2>
        <a href="purchase-history.php" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Quay lại
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card1 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-package me-2"></i> Sản phẩm đã mua</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['link_product']): ?>
                                                    <img src="<?= $item['link_product'] ?>" alt="<?= $item['name_product'] ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light me-3" style="width: 60px; height: 60px;"></div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?= $item['name_product'] ?></h6>
                                                    <a href="product-detail.php?id=<?= $item['product_id'] ?>" class="text-muted small">
                                                        <i class="bx bx-link-external"></i> Xem sản phẩm
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= format_currency($item['price']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="fw-bold"><?= format_currency($item['price'] * $item['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng tiền:</td>
                                    <td class="fw-bold text-primary"><?= format_currency($total) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card1 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i> Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Trạng thái:</span>
                            <span>
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
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Ngày đặt:</span>
                            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Phương thức thanh toán:</span>
                            <span>
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
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card1 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-map me-2"></i> Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <p><strong>Địa chỉ:</strong> <?= $order['shipping_address'] ?></p>
                        <p><strong>Số điện thoại:</strong> <?= $order['phone'] ?></p>
                        <?php if ($order['note']): ?>
                            <p><strong>Ghi chú:</strong> <?= $order['note'] ?></p>
                        <?php endif; ?>
                    </address>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'Footer.php'; ?> 