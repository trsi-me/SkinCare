<?php
session_start();
require_once __DIR__ . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$error = '';
$success = '';

// جلب بيانات المستخدم
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}

if (!$user) {
    header('Location: auth/login.php');
    exit;
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $error = 'الاسم مطلوب';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$name, $_SESSION['user_id']]);
                $_SESSION['user_name'] = $name;
                $user['name'] = $name;
                $success = 'تم تحديث البيانات بنجاح';
            } catch (PDOException $e) {
                $error = 'حدث خطأ أثناء التحديث';
            }
        }
    } elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'جميع الحقول مطلوبة';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'كلمة المرور الحالية غير صحيحة';
        } elseif (strlen($newPassword) < 6) {
            $error = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'كلمات المرور غير متطابقة';
        } else {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                $success = 'تم تحديث كلمة المرور بنجاح';
            } catch (PDOException $e) {
                $error = 'حدث خطأ أثناء التحديث';
            }
        }
    }
}

// جلب طلبات المستخدم
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

$pageTitle = 'الملف الشخصي';
require_once __DIR__ . '/includes/header.php';
?>

<div class="profile-page">
    <div class="container">
        <h1 class="page-title">الملف الشخصي</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="profile-content">
            <div class="profile-section">
                <h2>معلومات الحساب</h2>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label for="name">الاسم</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small>لا يمكن تغيير البريد الإلكتروني</small>
                    </div>
                    <button type="submit" class="btn btn-primary">تحديث البيانات</button>
                </form>
            </div>
            
            <div class="profile-section">
                <h2>تغيير كلمة المرور</h2>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_password">
                    <div class="form-group">
                        <label for="current_password">كلمة المرور الحالية</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">كلمة المرور الجديدة</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">تحديث كلمة المرور</button>
                </form>
            </div>
            
            <div class="profile-section">
                <h2>طلباتي</h2>
                <?php if (empty($orders)): ?>
                    <p>لا توجد طلبات</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-header">
                                    <span><strong>طلب #<?php echo $order['id']; ?></strong></span>
                                    <span class="order-date"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-details">
                                    <span>المجموع: <?php echo number_format($order['total'], 2); ?> ر.س</span>
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

