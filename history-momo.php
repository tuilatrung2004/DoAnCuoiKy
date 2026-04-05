<?php
require_once 'Header.php';
require_once './config/config.php';
require_once './functions/functions.php';
?>

<div class="card mt-4 shadow-lg border-0 rounded">
    <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0"><i class="bx bx-history me-2"></i> Lịch sử nạp tiền</h5>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 text-center">
                <thead class="table-dark text-white">
                    <tr>
                        <th>ID</th>
                        <th>Tài khoản</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($_SESSION['username'])) {
                        $stmt = $conn->prepare("SELECT * FROM nap_momo WHERE name = ? ORDER BY id_momo DESC LIMIT 10");
                        $stmt->bind_param("s", $_SESSION['username']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status = ($row['result_code'] == 0) 
                                    ? '<span class="badge bg-success">Thành Công</span>' 
                                    : '<span class="badge bg-danger">Thất Bại</span>';
                                ?>
                                <tr>
                                    <td><?php echo $row['id_momo']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="text-success fw-bold"><?php echo number_format($row['amount']) . 'đ'; ?></td>
                                    <td><?php echo $status; ?></td>
                                    <td><?php echo date("d/m/Y H:i:s", strtotime($row['created_at'])); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="bx bx-folder-open" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Lịch sử trống</p>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center text-danger">Chưa có tên người dùng được cung cấp.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'Footer.php'; ?>
