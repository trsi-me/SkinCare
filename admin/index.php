<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// التحقق من صلاحيات المدير
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// إحصائيات
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productsCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $ordersCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed'");
    $revenue = $stmt->fetch()['total'] ?? 0;
    
    // الطلبات الأخيرة
    $stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o 
                         JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC LIMIT 10");
    $recentOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    $productsCount = $ordersCount = $usersCount = $revenue = 0;
    $recentOrders = [];
}

$pageTitle = 'لوحة الإدارة';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <h1 class="page-title">لوحة الإدارة</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>المنتجات</h3>
                <p class="stat-number"><?php echo $productsCount; ?></p>
                <a href="products.php" class="btn btn-secondary btn-small">إدارة المنتجات</a>
            </div>
            <div class="stat-card">
                <h3>الطلبات</h3>
                <p class="stat-number"><?php echo $ordersCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>المستخدمون</h3>
                <p class="stat-number"><?php echo $usersCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>الإيرادات</h3>
                <p class="stat-number"><?php echo number_format($revenue, 2); ?> ر.س</p>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>الطلبات الأخيرة</h2>
            <?php if (empty($recentOrders)): ?>
                <p>لا توجد طلبات</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>المستخدم</th>
                            <th>المجموع</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?> ر.س</td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php
                                        $statusLabels = [
                                            'pending' => 'قيد الانتظار',
                                            'processing' => 'قيد المعالجة',
                                            'completed' => 'مكتمل',
                                            'cancelled' => 'ملغي'
                                        ];
                                        echo $statusLabels[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" action="update_order.php" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

