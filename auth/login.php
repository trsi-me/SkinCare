<?php
require_once __DIR__ . '/../includes/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'البريد الإلكتروني وكلمة المرور مطلوبان';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                session_regenerate_id(true);
                
                // إعادة التوجيه حسب الدور
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            } else {
                $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء تسجيل الدخول';
        }
    }
}

$pageTitle = 'تسجيل الدخول';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>تسجيل الدخول</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">دخول</button>
        </form>
        <p class="auth-link">ليس لديك حساب؟ <a href="register.php">سجل الآن</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

