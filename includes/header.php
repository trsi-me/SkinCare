<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

// حساب المسار النسبي من الملف الحالي إلى جذر المشروع
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);

// تنظيف المسار
$scriptDir = trim($scriptDir, '/');
$basePath = '';

// إذا كان الملف في مجلد فرعي (مثل /products أو /auth)
if (!empty($scriptDir) && $scriptDir !== '.') {
    // حساب عدد المستويات (مثلاً: products = 1 مستوى، admin/products = 2 مستويات)
    $depth = substr_count($scriptDir, '/') + 1;
    if ($depth > 0) {
        $basePath = str_repeat('../', $depth);
    }
}

// التحقق من حالة تسجيل الدخول
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'سكن كير - متجر العناية بالبشرة'; ?></title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    <style>
        @font-face {
            font-family: 'IBM Plex Sans Arabic';
            src: url('<?php echo $basePath; ?>assets/fonts/IBMPlexSansArabic-Regular.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'IBM Plex Sans Arabic';
            src: url('<?php echo $basePath; ?>assets/fonts/IBMPlexSansArabic-Bold.ttf') format('truetype');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'IBM Plex Sans Arabic';
            src: url('<?php echo $basePath; ?>assets/fonts/IBMPlexSansArabic-Medium.ttf') format('truetype');
            font-weight: 500;
            font-style: normal;
        }
        @font-face {
            font-family: 'IBM Plex Sans Arabic';
            src: url('<?php echo $basePath; ?>assets/fonts/IBMPlexSansArabic-Light.ttf') format('truetype');
            font-weight: 300;
            font-style: normal;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <a href="<?php echo $basePath; ?>index.php">سكن كير</a>
                </div>
                <ul class="nav-menu">
                    <li><a href="<?php echo $basePath; ?>index.php">الرئيسية</a></li>
                    <li><a href="<?php echo $basePath; ?>products/index.php">المنتجات</a></li>
                    <li><a href="<?php echo $basePath; ?>about.php">عنّا</a></li>
                    <li><a href="<?php echo $basePath; ?>contact.php">تواصل</a></li>
                    <li class="cart-icon">
                        <a href="<?php echo $basePath; ?>cart/index.php">
                            <span class="cart-badge" id="cartBadge">0</span>
                            🛒
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <?php if ($isAdmin): ?>
                            <li><a href="<?php echo $basePath; ?>admin/index.php">لوحة الإدارة</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $basePath; ?>profile.php"><?php echo htmlspecialchars($userName); ?></a></li>
                        <li><a href="<?php echo $basePath; ?>auth/logout.php">تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $basePath; ?>auth/login.php">دخول</a></li>
                        <li><a href="<?php echo $basePath; ?>auth/register.php">تسجيل</a></li>
                    <?php endif; ?>
                </ul>
                <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
            </div>
        </nav>
    </header>
    <main class="main-content">

