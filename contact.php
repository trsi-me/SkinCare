<?php
require_once __DIR__ . '/includes/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } else {
        // حفظ الرسالة في قاعدة البيانات (اختياري - يمكن إنشاء جدول messages)
        // أو إرسال بريد إلكتروني
        $success = 'شكراً لتواصلك معنا. سنرد عليك قريباً.';
    }
}

$pageTitle = 'تواصل معنا';
require_once __DIR__ . '/includes/header.php';
?>

<div class="contact-page">
    <div class="container">
        <h1 class="page-title">تواصل معنا</h1>
        
        <div class="contact-content">
            <div class="contact-form-section">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">الاسم</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">الرسالة</label>
                        <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">إرسال</button>
                </form>
            </div>
            
            <div class="contact-info-section">
                <h3>معلومات التواصل</h3>
                <div class="contact-info">
                    <p><strong>البريد الإلكتروني:</strong> info@skincare.com</p>
                    <p><strong>الهاتف:</strong> +966 50 123 4567</p>
                    <p><strong>العنوان:</strong> المملكة العربية السعودية</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

