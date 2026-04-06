<?php
require_once '../functions/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/header-admin.php';
checkAdmin();



if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    $check_products = $conn->query("SELECT COUNT(*) as count FROM all_product WHERE type_product = $id")->fetch_assoc();
    
    $check_children = $conn->query("SELECT COUNT(*) as count FROM product_categories WHERE parent_id = $id")->fetch_assoc();
    
    if ($check_products['count'] > 0) {
        $error = "Không thể xóa danh mục này vì có sản phẩm thuộc danh mục!";
    } elseif ($check_children['count'] > 0) {
        $error = "Không thể xóa danh mục này vì có danh mục con thuộc danh mục!";
    } else {
        if ($conn->query("DELETE FROM product_categories WHERE id = $id")) {
            $success = "Xóa danh mục thành công!";
        } else {
            $error = "Có lỗi xảy ra: " . $conn->error;
        }
    }
}

if (isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $slug = create_slug($name);
    $description = $conn->real_escape_string($_POST['description']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/categories/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = time() . '_' . $_FILES['image']['name'];
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = 'uploads/categories/' . $filename;
        } else {
            $error = "Không thể upload ảnh!";
        }
    }
    
    $check_slug = $conn->query("SELECT COUNT(*) as count FROM product_categories WHERE slug = '$slug'")->fetch_assoc();
    if ($check_slug['count'] > 0) {
        $error = "Tên danh mục đã tồn tại!";
    } else if (empty($error)) {
        $parent_id_value = $parent_id === 'NULL' ? NULL : $parent_id;
        $add_query = "INSERT INTO product_categories (ten_theloai, slug, description, parent_id, image) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($add_query);
        $stmt->bind_param("sssss", $name, $slug, $description, $parent_id_value, $image_path);
        
        if ($stmt->execute()) {
            $success = "Thêm danh mục thành công!";
        } else {
            $error = "Có lỗi xảy ra: " . $stmt->error;
        }
    }
}

if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['category_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $slug = create_slug($name);
    $description = $conn->real_escape_string($_POST['description']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    
    if ($parent_id == $id) {
        $error = "Không thể chọn chính danh mục này làm danh mục cha!";
    } else {
        $check_slug = $conn->query("SELECT COUNT(*) as count FROM product_categories WHERE slug = '$slug' AND id != $id")->fetch_assoc();
        if ($check_slug['count'] > 0) {
            $error = "Tên danh mục đã tồn tại!";
        } else {
            $image_path = $_POST['current_image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = '../uploads/categories/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $filename = time() . '_' . $_FILES['image']['name'];
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = 'uploads/categories/' . $filename;
                } else {
                    $error = "Không thể upload ảnh!";
                }
            }
            
            if (empty($error)) {
                $parent_id_value = $parent_id === 'NULL' ? NULL : $parent_id;
                $edit_query = "UPDATE product_categories SET 
                            ten_theloai = ?, 
                            slug = ?, 
                            description = ?, 
                            parent_id = ?,
                            image = ?
                            WHERE id = ?";
                $stmt = $conn->prepare($edit_query);
                $stmt->bind_param("sssssi", $name, $slug, $description, $parent_id_value, $image_path, $id);
                
                if ($stmt->execute()) {
                    $success = "Cập nhật danh mục thành công!";
                } else {
                    $error = "Có lỗi xảy ra: " . $stmt->error;
                }
            }
        }
    }
}




$categories_query = "SELECT c.*, p.ten_theloai as parent_name
                    FROM product_categories c
                    LEFT JOIN product_categories p ON c.parent_id = p.id
                    ORDER BY c.ten_theloai";
$categories = $conn->query($categories_query);

$parent_categories_query = "SELECT * FROM product_categories ORDER BY ten_theloai";
$parent_categories = $conn->query($parent_categories_query);
?>

<!-- Page content starts here -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý danh mục sản phẩm</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Thêm danh mục
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
                        <th>Tên danh mục</th>
                        <th>Slug</th>

                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td>
                                <?php if (!empty($category['image'])): ?>
                                    <img src="../<?= $category['image'] ?>" 
                                        alt="<?= $category['ten_theloai'] ?>"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $category['ten_theloai'] ?></td>
                            <td><?= $category['slug'] ?></td>
                            <td><?= $category['description'] ?: '<span class="text-muted">Không có</span>' ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#editCategoryModal<?= $category['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Modal Sửa danh mục -->
                        <div class="modal fade" id="editCategoryModal<?= $category['id'] ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Sửa danh mục</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                            <input type="hidden" name="current_image" value="<?= $category['image'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Tên danh mục</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?= $category['ten_theloai'] ?>" required>
                                            </div>
                                            
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Mô tả</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"><?= $category['description'] ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="image" class="form-label">Ảnh danh mục</label>
                                                <?php if (!empty($category['image'])): ?>
                                                    <div class="mb-2">
                                                        <img src="../<?= $category['image'] ?>" 
                                                            alt="<?= $category['ten_theloai'] ?>"
                                                            style="width: 100px; height: 100px; object-fit: cover;">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="image" name="image">
                                                <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                                            </div>
                                            
                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" name="edit_category" class="btn btn-primary">Lưu thay đổi</button>
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

<!-- Modal Thêm danh mục -->
<div class="modal fade" id="addCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_parent_id" class="form-label">Danh mục cha</label>
                        <select class="form-select" id="add_parent_id" name="parent_id">
                            <option value="">Không có</option>
                            <?php 
                            $parent_cats = $conn->query($parent_categories_query);
                            while ($parent = $parent_cats->fetch_assoc()): 
                            ?>
                                <option value="<?= $parent['id'] ?>"><?= $parent['ten_theloai'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_image" class="form-label">Ảnh danh mục</label>
                        <input type="file" class="form-control" id="add_image" name="image">
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Thêm danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


