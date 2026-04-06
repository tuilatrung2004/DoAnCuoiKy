<?php
require_once './config/config.php';
require_once './functions/functions.php';
require_once 'Header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product_query = "SELECT p.*, b.name as brand_name, c.ten_theloai as category_name
                 FROM all_product p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 LEFT JOIN product_categories c ON p.type_product = c.id
                 WHERE p.id_product = ?";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger'>Sản phẩm không tồn tại!</div>";
    exit();
}

$conn->query("UPDATE all_product SET views = views + 1 WHERE id_product = $product_id");

$related_query = "SELECT p.*, b.name as brand_name 
                 FROM all_product p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 WHERE p.type_product = ? AND p.id_product != ?
                 LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("ii", $product['type_product'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result();
?>

<div class="container pb-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card product-image-card">
                <img src="<?= $product['link_product'] ?>" class="card-img-top product-main-img" alt="<?= $product['name_product'] ?>">
            </div>
        </div>

        <div class="col-md-6 d-flex flex-column">
            <div class="product-info flex-grow-1">
                <h1 class="h2 mb-3"><?= $product['name_product'] ?></h1>
                
                <div class="mb-3">
                    <span class="badge bg-primary"><?= $product['brand_name'] ?></span>
                    <span class="badge bg-secondary"><?= $product['category_name'] ?></span>
                </div>

                <div class="mb-3">
                    <h3 class="text-primary mb-0"><?= format_currency($product['price_product']) ?></h3>
                    <?php if($product['discount_percent'] > 0): ?>
                        <small class="text-danger">Giảm <?= $product['discount_percent'] ?>%</small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <h5>Mô tả sản phẩm:</h5>
                    <p><?= nl2br($product['describe_product']) ?></p>
                </div>
            </div>

            <form action="cart.php" method="POST" class="mb-3">
                <input type="hidden" name="product_id" value="<?= $product['id_product'] ?>">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label">Số lượng:</label>
                    </div>
                    <div class="col-auto">
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="10">
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="bx bx-cart-add"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
            </form>

            <div class="card mb-3" style="height: 100%;">
                <div class="card-body">
                    <h5 class="card-title mb-3">Thông tin thêm về sản phẩm</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i> Bảo hành chính hãng 12 tháng</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i> Giao hàng toàn quốc</li>
                        <li><i class="bx bx-check text-success me-2"></i> Đổi trả trong 7 ngày</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="related-products-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-4">Sản phẩm liên quan</h2>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php while($related = $related_products->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?= $related['link_product'] ?>" 
                             class="card-img-top" alt="<?= $related['name_product'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $related['name_product'] ?></h5>
                            <p class="card-text text-muted"><?= $related['brand_name'] ?></p>
                            <p class="card-text">
                                <strong class="text-primary"><?= format_currency($related['price_product']) ?></strong>
                                <?php if($related['discount_percent'] > 0): ?>
                                    <span class="text-danger ms-2">-<?= $related['discount_percent'] ?>%</span>
                                <?php endif; ?>
                            </p>
                            <a href="product-detail.php?id=<?= $related['id_product'] ?>" 
                               class="btn btn-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>



<?php
require_once 'Footer.php';
?>
