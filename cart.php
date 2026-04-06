<?php
require_once './config/config.php';
require_once './functions/functions.php';
require_once 'Header.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("SELECT * FROM all_product WHERE id_product = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = array(
                'name' => $product['name_product'],
                'price' => $product['price_product'],
                'quantity' => $quantity,
                'image' => $product['link_product']
            );
        }
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: 'Đã thêm sản phẩm vào giỏ hàng',
                showConfirmButton: false,
                timer: 1500
            });
        </script>";
    }
}

if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<div class="container py-5">
    <h1 class="mb-4">Giỏ hàng của bạn</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">
            Giỏ hàng của bạn đang trống. 
            <a href="index.php" class="alert-link">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form method="post" action="cart.php">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>" 
                                             style="width: 50px; height: 50px; object-fit: contain;" class="me-3">
                                        <span><?= $item['name'] ?></span>
                                    </div>
                                </td>
                                <td><?= format_currency($item['price']) ?></td>
                                <td>
                                    <input type="number" name="quantity[<?= $product_id ?>]" 
                                           value="<?= $item['quantity'] ?>" min="0" max="10" 
                                           class="form-control" style="width: 80px;">
                                </td>
                                <td><?= format_currency($item['price'] * $item['quantity']) ?></td>
                                <td>
                                    <a href="cart.php?remove=<?= $product_id ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong><?= format_currency($total) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Tiếp tục mua sắm
                </a>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-primary me-2">
                        <i class="bx bx-refresh"></i> Cập nhật giỏ hàng
                    </button>
                    <a href="checkout.php" class="btn btn-success">
                        <i class="bx bx-check"></i> Thanh toán
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>


<?php require_once 'Footer.php'; ?>
