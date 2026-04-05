<?php
require_once '../functions/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/header-admin.php';

checkAdmin();



// Lấy thống kê
$stats = [
    'products' => $conn->query("SELECT COUNT(*) as count FROM all_product")->fetch_assoc()['count'],
    'orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'users' => $conn->query("SELECT COUNT(*) as count FROM account WHERE is_admin = 0")->fetch_assoc()['count'],
    'revenue' => $conn->query("SELECT SUM(price) as total FROM orders INNER JOIN order_items ON orders.id = order_items.order_id WHERE order_status = 'delivered'")->fetch_assoc()['total'] ?? 0
];

// Lấy đơn hàng mới nhất
$recent_orders = $conn->query("
    SELECT o.*, a.fullname ,oi.price
    FROM orders o 
    INNER JOIN account a ON o.user_id = a.id 
    INNER JOIN order_items oi ON o.id = oi.order_id
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Lấy sản phẩm bán chạy
$top_products = $conn->query("
    SELECT p.*, COUNT(oi.id) as sold_count 
    FROM all_product p
    LEFT JOIN order_items oi ON p.id_product = oi.product_id
    GROUP BY p.id_product
    ORDER BY sold_count DESC
    LIMIT 5
");

// Lấy dữ liệu doanh thu theo tháng cho biểu đồ doanh thu
$revenue_data = [];
$revenue_query = "SELECT 
    MONTH(created_at) as month,
    SUM(price) as total
    FROM orders 
    INNER JOIN order_items ON orders.id = order_items.order_id
    WHERE order_status = 'delivered'
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(created_at)
    ORDER BY month";
$revenue_result = $conn->query($revenue_query);

// Khởi tạo mảng với 12 tháng, giá trị mặc định là 0
for ($i = 1; $i <= 12; $i++) {
    $revenue_data[$i] = 0;
}

// Cập nhật dữ liệu từ database
while ($row = $revenue_result->fetch_assoc()) {
    $revenue_data[$row['month']] = (float)$row['total'];
}

// Lấy dữ liệu trạng thái đơn hàng cho biểu đồ tròn
$status_query = "SELECT 
    order_status,
    COUNT(*) as count
    FROM orders 
    GROUP BY order_status";
$status_result = $conn->query($status_query);

$status_data = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

while ($row = $status_result->fetch_assoc()) {
    $status_data[$row['order_status']] = (int)$row['count'];
}
?>
<h2>Tổng Quan</h2>
<!-- Thống kê -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stat-card">
            <div class="stat-icon" style="background-color: #4361ee;">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">DOANH THU</div>
                <div class="stat-value"><?= format_currency($stats['revenue']) ?></div>
                <div class="stat-percent increase">
                    <i class="fas fa-arrow-up"></i> 8.3% từ tháng trước
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stat-card">
            <div class="stat-icon" style="background-color: #4cc9f0;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">ĐƠN HÀNG</div>
                <div class="stat-value"><?= $stats['orders'] ?></div>
                <div class="stat-percent increase">
                    <i class="fas fa-arrow-up"></i> 5.7% từ tháng trước
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stat-card">
            <div class="stat-icon" style="background-color: #f72585;">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">KHÁCH HÀNG</div>
                <div class="stat-value"><?= $stats['users'] ?></div>
                <div class="stat-percent increase">
                    <i class="fas fa-arrow-up"></i> 10.2% từ tháng trước
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stat-card">
            <div class="stat-icon" style="background-color: #f6c23e;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">SẢN PHẨM</div>
                <div class="stat-value"><?= $stats['products'] ?></div>
                <div class="stat-percent increase">
                    <i class="fas fa-arrow-up"></i> 12.5% từ tháng trước
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Biểu đồ và bảng -->
<div class="row g-4 mb-4">
    <!-- Biểu đồ doanh thu -->
    <div class="col-xl-8 col-lg-7">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h6 class="dashboard-card-title">Biểu đồ doanh thu</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Xuất báo cáo</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sync-alt me-2"></i> Cập nhật</a></li>
                    </ul>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ trạng thái đơn hàng -->
    <div class="col-xl-4 col-lg-5">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h6 class="dashboard-card-title">Trạng thái đơn hàng</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Xuất báo cáo</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sync-alt me-2"></i> Cập nhật</a></li>
                    </ul>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div class="chart-container">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Đơn hàng gần đây và sản phẩm bán chạy -->
<div class="row g-4">
    <!-- Đơn hàng gần đây -->
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h6 class="dashboard-card-title">Đơn hàng gần đây</h6>
                <a href="carts.php" class="btn btn-sm btn-primary">Xem tất cả</a>
            </div>
            <div class="dashboard-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th>Tổng tiền</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= $order['fullname'] ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusText = '';
                                            switch ($order['order_status']) {
                                                case 'pending':
                                                    $statusClass = 'status-badge-pending';
                                                    $statusText = 'Chờ xử lý';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'status-badge-processing';
                                                    $statusText = 'Đang xử lý';
                                                    break;
                                                case 'shipped':
                                                    $statusClass = 'status-badge-shipped';
                                                    $statusText = 'Đang giao';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'status-badge-delivered';
                                                    $statusText = 'Đã giao';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'status-badge-cancelled';
                                                    $statusText = 'Đã hủy';
                                                    break;
                                            }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td><?= format_currency($order['price']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Không có đơn hàng nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm bán chạy -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h6 class="dashboard-card-title">Sản phẩm bán chạy</h6>
                <a href="products.php" class="btn btn-sm btn-primary">Xem tất cả</a>
            </div>
            <div class="dashboard-card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if ($top_products->num_rows > 0): ?>
                        <?php while ($product = $top_products->fetch_assoc()): ?>
                            <div class="list-group-item d-flex align-items-center p-3">
                                <div class="me-3">
                                    <img src="../<?= $product['link_product'] ?>" class="rounded" width="40" height="40" style="object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 text-truncate" style="max-width: 180px;"><?= $product['name_product'] ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted small"><?= format_currency($product['price_product']) ?></span>
                                        <span class="badge bg-success"><?= $product['sold_count'] ?> đã bán</span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center py-3">
                            <span class="text-muted">Không có sản phẩm nào</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- Chart Scripts -->
<script>

const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const gradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(67, 97, 238, 0.3)');
gradient.addColorStop(1, 'rgba(67, 97, 238, 0)');

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        datasets: [{
            label: 'Doanh thu',
            data: [
                <?php echo implode(',', array_values($revenue_data)); ?>
            ],
            borderColor: '#4361ee',
            backgroundColor: gradient,
            tension: 0.3,
            pointBackgroundColor: '#4361ee',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 10
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + context.raw.toLocaleString('vi-VN') + ' đ';
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' đ';
                    }
                }
            }
        }
    }
});

// Order Status Chart
const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao', 'Đã giao', 'Đã hủy'],
        datasets: [{
            data: [
                <?php echo implode(',', [
                    $status_data['pending'],
                    $status_data['processing'],
                    $status_data['shipped'],
                    $status_data['delivered'],
                    $status_data['cancelled']
                ]); ?>
            ],
            backgroundColor: ['#f6c23e', '#4cc9f0', '#4361ee', '#1cc88a', '#e74a3b'],
            borderWidth: 0,
            hoverOffset: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 10
        },
        cutout: '70%',
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 12,
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.raw || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = Math.round((value * 100) / total);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>

