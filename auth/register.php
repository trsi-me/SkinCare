<?php
require_once __DIR__ . '/../includes/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // التحقق من البيانات
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirmPassword) {
        $error = 'كلمات المرور غير متطابقة';
    } else {
        // التحقق من وجود البريد الإلكتروني
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'البريد الإلكتروني مستخدم بالفعل';
            } else {
                // إنشاء حساب جديد
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword]);
                
                // تسجيل الدخول تلقائياً
                session_start();
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                session_regenerate_id(true);
                
                header('Location: ../profile.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء التسجيل';
        }
    }
}

$pageTitle = 'تسجيل حساب جديد';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>تسجيل حساب جديد</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="name">الاسم الكامل</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">تسجيل</button>
        </form>
        <p class="auth-link">لديك حساب بالفعل؟ <a href="login.php">سجل الدخول</a></p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password.length < 6) {
        e.preventDefault();
        alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('كلمات المرور غير متطابقة');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

