<?php
require_once 'Header.php';
require_once 'config/config.php';
require_once 'functions/functions.php';

$products_per_page = 8;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($current_page - 1) * $products_per_page;

$new_products_query = "SELECT p.*, b.name as brand_name 
                      FROM all_product p
                      LEFT JOIN brands b ON p.brand_id = b.id
                      ORDER BY p.created_at DESC
                      LIMIT $products_per_page OFFSET $offset";
$new_products = $conn->query($new_products_query);

$featured_products_query = "SELECT p.*, b.name as brand_name 
                          FROM all_product p
                          LEFT JOIN brands b ON p.brand_id = b.id
                          ORDER BY p.views DESC
                          LIMIT $products_per_page";
$featured_products = $conn->query($featured_products_query);

$total_new_products = $conn->query("SELECT COUNT(*) as count FROM all_product")->fetch_assoc()['count'];
$total_pages = ceil($total_new_products / $products_per_page);

$start_page = max(1, $current_page - 1);
$end_page = min($total_pages, $current_page + 1);
?>

<!-- Banner/Slider -->
<div id="mainSlider" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="3"></button>
    </div>
    
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="images/banner1.jpg" class="d-block w-100" alt="Banner 1">
        </div>
        <div class="carousel-item">
            <img src="images/banner2.jpg" class="d-block w-100" alt="Banner 2">
        </div>
        <div class="carousel-item">
            <img src="images/banner3.jpg" class="d-block w-100" alt="Banner 3">
        </div>
        <div class="carousel-item">
            <img src="images/banner4.jpg" class="d-block w-100" alt="Banner 4">
        </div>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#mainSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<section class="new-products-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-4">Sản phẩm mới</h2>
        <div class="row">
            <?php while($product = $new_products->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 modern-card">
                        <img src="<?= $product['link_product'] ?>" 
                             class="card-img-top" alt="<?= $product['name_product'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name_product'] ?></h5>
                            <p class="card-text text-muted"><?= $product['brand_name'] ?></p>
                            <p class="card-text">
                                <strong class="price"><?= format_currency($product['price_product']) ?></strong>
                                <?php if($product['discount_percent'] > 0): ?>
                                    <span class="text-danger ms-2">-<?= $product['discount_percent'] ?>%</span>
                                <?php endif; ?>
                            </p>
                            <a href="product-detail.php?id=<?= $product['id_product'] ?>" 
                               class="btn btn-modern">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php if ($start_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($page = $start_page; $page <= $end_page; $page++): ?>
                    <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $page ?>"><?= $page ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<section class="featured-products-section py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">Sản phẩm nổi bật</h2>
        <div class="row">
            <?php while($product = $featured_products->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 modern-card">
                        <img src="<?= $product['link_product'] ?>" 
                             class="card-img-top" alt="<?= $product['name_product'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name_product'] ?></h5>
                            <p class="card-text text-muted"><?= $product['brand_name'] ?></p>
                            <p class="card-text">
                                <strong class="price"><?= format_currency($product['price_product']) ?></strong>
                                <?php if($product['discount_percent'] > 0): ?>
                                    <span class="text-danger ms-2">-<?= $product['discount_percent'] ?>%</span>
                                <?php endif; ?>
                            </p>
                            <a href="product-detail.php?id=<?= $product['id_product'] ?>" 
                               class="btn btn-modern">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'Footer.php'; ?>

