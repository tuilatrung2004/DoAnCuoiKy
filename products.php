<?php
require_once './config/config.php';
require_once './functions/functions.php';
require_once 'Header.php';

$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$where = "WHERE 1=1";
$params = array();

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where .= " AND p.type_product = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where .= " AND p.brand_id = ?";
    $params[] = $_GET['brand'];
}

if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
    $where .= " AND p.price_product >= ?";
    $params[] = $_GET['price_min'];
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $where .= " AND p.price_product <= ?";
    $params[] = $_GET['price_max'];
}

$query = "SELECT p.*, b.name as brand_name, c.ten_theloai as category_name 
          FROM all_product p 
          LEFT JOIN brands b ON p.brand_id = b.id
          LEFT JOIN product_categories c ON p.type_product = c.id
          $where
          ORDER BY p.created_at DESC 
          LIMIT $offset, $items_per_page";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$count_query = "SELECT COUNT(*) as total FROM all_product p $where";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

$categories = $conn->query("SELECT * FROM product_categories ORDER BY ten_theloai");
$brands = $conn->query("SELECT * FROM brands ORDER BY name");
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar filters -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Bộ lọc sản phẩm</h5>
                    <form action="" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= isset($_GET['category']) && $_GET['category'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= $cat['ten_theloai'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thương hiệu</label>
                            <select name="brand" class="form-select">
                                <option value="">Tất cả thương hiệu</option>
                                <?php while ($brand = $brands->fetch_assoc()): ?>
                                    <option value="<?= $brand['id'] ?>"
                                            <?= isset($_GET['brand']) && $_GET['brand'] == $brand['id'] ? 'selected' : '' ?>>
                                        <?= $brand['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giá</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" name="price_min" class="form-control" 
                                           placeholder="Từ" value="<?= $_GET['price_min'] ?? '' ?>">
                                </div>
                                <div class="col">
                                    <input type="number" name="price_max" class="form-control" 
                                           placeholder="Đến" value="<?= $_GET['price_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product grid -->
        <div class="col-md-9">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="<?= $product['link_product'] ?>" 
                                 class="card-img-top" alt="<?= $product['name_product'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $product['name_product'] ?></h5>
                                <p class="card-text text-muted"><?= $product['brand_name'] ?></p>
                                <p class="card-text">
                                    <strong class="text-primary"><?= format_currency($product['price_product']) ?></strong>
                                    <?php if ($product['discount_percent'] > 0): ?>
                                        <span class="text-danger ms-2">-<?= $product['discount_percent'] ?>%</span>
                                    <?php endif; ?>
                                </p>
                                <a href="product-detail.php?id=<?= $product['id_product'] ?>" 
                                   class="btn btn-primary">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['category']) ? '&category='.$_GET['category'] : '' ?><?= isset($_GET['brand']) ? '&brand='.$_GET['brand'] : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
require_once 'Footer.php';
?> 