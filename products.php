<?php
require_once '../functions/functions.php';
require_once __DIR__ . '/../config/config.php';

// Kiểm tra quyền admin
checkAdmin();

require_once __DIR__ . '/header-admin.php';

// Xử lý xóa sản phẩm
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM all_product WHERE id_product = $id")) {
        $success = "Xóa sản phẩm thành công!";
    } else {
        $error = "Có lỗi xảy ra!";
    }
}

// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $category = (int)$_POST['category'];
    $brand = (int)$_POST['brand'];
    $description = $conn->real_escape_string($_POST['description']);
    
    // Xử lý upload ảnh
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = time() . '_' . $_FILES['image']['name'];
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = 'uploads/products/' . $filename;
        } else {
            $error = "Không thể upload ảnh!";
        }
    }
    
    if (empty($error)) {
        $add_query = "INSERT INTO all_product (name_product, price_product, quantity, type_product, brand_id, describe_product, link_product) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($add_query);
        $stmt->bind_param("sdiiiss", $name, $price, $quantity, $category, $brand, $description, $image_path);
        
        if ($stmt->execute()) {
            $success = "Thêm sản phẩm thành công!";
        } else {
            $error = "Có lỗi xảy ra: " . $stmt->error;
        }
    }
}

// Xử lý sửa sản phẩm
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $category = (int)$_POST['category'];
    $brand = (int)$_POST['brand'];
    $description = $conn->real_escape_string($_POST['description']);
    
    // Kiểm tra có upload ảnh mới không
    $image_path = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = time() . '_' . $_FILES['image']['name'];
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = 'uploads/products/' . $filename;
        } else {
            $error = "Không thể upload ảnh!";
        }
    }
    
    if (empty($error)) {
        $edit_query = "UPDATE all_product SET 
                     name_product = ?, 
                     price_product = ?, 
                     quantity = ?, 
                     type_product = ?,
                     brand_id = ?,
                     describe_product = ?,
                     link_product = ?
                     WHERE id_product = ?";
        $stmt = $conn->prepare($edit_query);
        $stmt->bind_param("sdiiissi", $name, $price, $quantity, $category, $brand, $description, $image_path, $id);
        
        if ($stmt->execute()) {
            $success = "Cập nhật sản phẩm thành công!";
        } else {
            $error = "Có lỗi xảy ra: " . $stmt->error;
        }
    }
}

// Lấy danh sách sản phẩm
$products_query = "SELECT p.*, c.ten_theloai, b.name as brand_name 
                  FROM all_product p
                  LEFT JOIN product_categories c ON p.type_product = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  ORDER BY p.created_at DESC";
$products = $conn->query($products_query);

// Lấy danh sách danh mục
$categories_query = "SELECT * FROM product_categories ORDER BY ten_theloai";
$categories = $conn->query($categories_query);

// Lấy danh sách thương hiệu
$brands_query = "SELECT * FROM brands ORDER BY name";
$brands = $conn->query($brands_query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý sản phẩm</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Thêm sản phẩm
    </button>
</div>

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
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Thương hiệu</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?= $product['id_product'] ?></td>
                            <td>
                                <img src="../<?= $product['link_product'] ?>" 
                                     alt="<?= $product['name_product'] ?>"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?= $product['name_product'] ?></td>
                            <td><?= $product['ten_theloai'] ?></td>
                            <td><?= $product['brand_name'] ?></td>
                            <td><?= format_currency($product['price_product']) ?></td>
                            <td><?= $product['quantity'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal<?= $product['id_product'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <input type="hidden" name="id" value="<?= $product['id_product'] ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Modal Sửa sản phẩm -->
                        <div class="modal fade" id="editProductModal<?= $product['id_product'] ?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Sửa sản phẩm</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="product_id" value="<?= $product['id_product'] ?>">
                                            <input type="hidden" name="current_image" value="<?= $product['link_product'] ?>">
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label for="name" class="form-label">Tên sản phẩm</label>
                                                        <input type="text" class="form-control" name="name" 
                                                               value="<?= $product['name_product'] ?>" required>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="price" class="form-label">Giá</label>
                                                            <input type="number" class="form-control" name="price" 
                                                                   value="<?= $product['price_product'] ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="quantity" class="form-label">Số lượng</label>
                                                            <input type="number" class="form-control" name="quantity" 
                                                                   value="<?= $product['quantity'] ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="category" class="form-label">Danh mục</label>
                                                            <select class="form-select" name="category" required>
                                                                <?php 
                                                                $categories->data_seek(0);
                                                                while ($cat = $categories->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?= $cat['id'] ?>" 
                                                                            <?= $product['type_product'] == $cat['id'] ? 'selected' : '' ?>>
                                                                        <?= $cat['ten_theloai'] ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="brand" class="form-label">Thương hiệu</label>
                                                            <select class="form-select" name="brand" required>
                                                                <?php 
                                                                $brands->data_seek(0);
                                                                while ($brand = $brands->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?= $brand['id'] ?>" 
                                                                            <?= $product['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                                                                        <?= $brand['name'] ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="image" class="form-label">Ảnh sản phẩm</label>
                                                        <input type="file" class="form-control" name="image" accept="image/*">
                                                        <div class="mt-2">
                                                            <img src="../<?= $product['link_product'] ?>" 
                                                                 alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                                            <div class="small text-muted">Để trống nếu không muốn thay đổi ảnh</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Mô tả</label>
                                                <textarea class="form-control" name="description" rows="10" style="font-family: monospace;"><?= $product['describe_product'] ?></textarea>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" name="edit_product" class="btn btn-primary">Lưu thay đổi</button>
                                            </div>
                                        </form>
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

<!-- Modal Thêm sản phẩm -->
<div class="modal fade" id="addProductModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên sản phẩm</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Giá</label>
                                    <input type="number" class="form-control" name="price" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quantity" class="form-label">Số lượng</label>
                                    <input type="number" class="form-control" name="quantity" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Danh mục</label>
                                    <select class="form-select" name="category" required>
                                        <?php 
                                        $categories->data_seek(0);
                                        while ($cat = $categories->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $cat['id'] ?>"><?= $cat['ten_theloai'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="brand" class="form-label">Thương hiệu</label>
                                    <select class="form-select" name="brand" required>
                                        <?php 
                                        $brands->data_seek(0);
                                        while ($brand = $brands->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $brand['id'] ?>"><?= $brand['name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label">Ảnh sản phẩm</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="10" style="font-family: monospace;"></textarea>
                        <small class="text-muted">Mỗi dòng sẽ được hiển thị riêng biệt. Bạn có thể sử dụng định dạng "Thuộc tính" và "Giá trị" trên các dòng riêng biệt.</small>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


