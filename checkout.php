<?php
require_once 'Header.php';
require_once './config/config.php';
require_once './functions/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM account WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Tính tổng tiền
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $note = $_POST['note'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'wallet';
    
    $error = null;
    try {
        $conn->begin_transaction();
        
        $total_amount = $total;
        if ($payment_method === 'cod') {
            $total_amount = 'Khi nhận hàng';
        } elseif ($payment_method === 'wallet' && $total > $user['money']) {
            throw new Exception("Số dư không đủ để thanh toán!");
        }
        
        // Tạo đơn hàng
        $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_address, phone, note, payment_method, order_status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("isssss", $user_id, $total_amount, $address, $phone, $note, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        // Thêm chi tiết đơn hàng và cập nhật số lượng sản phẩm
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        // Chuẩn bị câu lệnh cập nhật số lượng
        $update_quantity_sql = "UPDATE all_product SET quantity = quantity - ? WHERE id_product = ?";
        $update_quantity_stmt = $conn->prepare($update_quantity_sql);
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $item_stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $item_stmt->execute();
            
            $update_quantity_stmt->bind_param("ii", $item['quantity'], $product_id);
            $update_quantity_stmt->execute();
            
            $check_quantity = $conn->query("SELECT quantity FROM all_product WHERE id_product = $product_id");
            $remaining = $check_quantity->fetch_assoc()['quantity'];
            
            if ($remaining < 0) {
                throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng!");
            }
        }
        

        if ($payment_method === 'wallet') {
            $new_balance = $user['money'] - $total;
            $update_money = "UPDATE account SET money = ? WHERE id = ?";
            $money_stmt = $conn->prepare($update_money);
            $money_stmt->bind_param("di", $new_balance, $user_id);
            $money_stmt->execute();
            $_SESSION['money'] = $new_balance;
        }

        unset($_SESSION['cart']);
        
        $conn->commit();
        $success = "Đặt hàng thành công!";
        
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4">Thanh toán</h2>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?= $success ?>
            <a href="index.php" class="alert-link">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Thông tin giao hàng</h5>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" class="form-control" value="<?= $user['fullname'] ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ giao hàng</label>
                                <textarea name="address" class="form-control" rows="3" required><?= $user['address'] ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" value="<?= $user['phone'] ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3">Phương thức thanh toán</h5>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="wallet" value="wallet" checked>
                                    <label class="form-check-label" for="wallet">
                                        <i class='bx bx-wallet text-primary'></i> Thanh toán bằng số dư
                                        <span class="text-success ms-2">(Số dư: <?= format_currency($user['money']) ?>)</span>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod">
                                    <label class="form-check-label" for="cod">
                                        <i class='bx bx-money text-success'></i> Thanh toán khi nhận hàng (COD)
                                    </label>
                                </div> 
                            </div>
        
                            
                            <div class="d-flex justify-content-between">
                                <a href="cart.php" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Quay lại giỏ hàng
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-check"></i> Đặt hàng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Đơn hàng của bạn</h5>
                        
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0"><?= $item['name'] ?></h6>
                                    <small class="text-muted">Số lượng: <?= $item['quantity'] ?></small>
                                </div>
                                <span><?= format_currency($item['price'] * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng tiền:</strong>
                            <strong class="text-primary"><?= format_currency($total) ?></strong>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <span>Số dư hiện tại:</span>
                            <span class="text-success"><?= format_currency($user['money']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php require_once 'Footer.php'; ?>
