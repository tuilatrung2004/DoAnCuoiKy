<?php
include_once 'Header.php';
include_once './config/config.php';

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
?>
<div class="container pt-4">
    <div class="card shadow-lg border-0 rounded">
        <div class="card-header bg-primary text-white text-center">
            <h5 class="mb-0"><i class="bx bx-history me-2"></i> Lịch sử nạp thẻ</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-center">
                    <thead class="table-dark text-white">
                        <tr>
                            <th>ID</th>
                            <th>Tài khoản</th>
                            <th>Mệnh giá</th>
                            <th>Loại thẻ</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM trans_log WHERE name = ? ORDER BY id DESC LIMIT 10");
                        $stmt->bind_param("s", $_SESSION['username']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status = match ($row['status']) {
                                    1 => '<span class="badge bg-success">Thành Công</span>',
                                    0 => '<span class="badge bg-danger">Thất Bại</span>',
                                    3 => '<span class="badge bg-warning text-dark">Sai Mệnh Giá</span>',
                                    default => '<span class="badge bg-secondary">Chờ Duyệt</span>',
                                };
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="text-success fw-bold"><?php echo number_format($row['amount']) . 'đ'; ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td><?php echo $status; ?></td>
                                    <td><?php echo date("d/m/Y H:i:s", strtotime($row['date'])); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="bx bx-folder-open" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Lịch sử trống</p>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/main.js"></script>
<?php
include_once 'Footer.php';
?>