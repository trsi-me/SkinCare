<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=checkout');
    exit;
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// حساب المجموع
$placeholders = str_repeat('?,', count($cart) - 1) . '?';
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$cartItems = [];
foreach ($products as $product) {
    $qty = $cart[$product['id']];
    // التحقق من المخزون
    if ($qty > $product['stock']) {
        $error = "الكمية المطلوبة من {$product['title']} غير متوفرة في المخزون";
        break;
    }
    $subtotal = $product['price'] * $qty;
    $total += $subtotal;
    $cartItems[] = [
        'product' => $product,
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

$tax = $total * 0.15;
$grandTotal = $total + $tax;

// معالجة الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    try {
        $pdo->beginTransaction();
        
        // إنشاء الطلب
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $grandTotal]);
        $orderId = $pdo->lastInsertId();
        
        // إضافة عناصر الطلب وتحديث المخزون
        foreach ($cartItems as $item) {
            // إضافة عنصر الطلب
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product']['id'], $item['qty'], $item['product']['price']]);
            
            // تحديث المخزون
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['qty'], $item['product']['id']]);
        }
        
        $pdo->commit();
        
        // تفريغ العربة
        $_SESSION['cart'] = [];
        $success = "تم إنشاء الطلب بنجاح! رقم الطلب: $orderId";
        
        // إعادة التوجيه بعد 3 ثوان
        header("refresh:3;url=../profile.php");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'حدث خطأ أثناء معالجة الطلب';
    }
}

$pageTitle = 'إتمام الطلب';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="checkout-page">
    <div class="container">
        <h1 class="page-title">إتمام الطلب</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <p>سيتم توجيهك إلى صفحة الملف الشخصي...</p>
        <?php else: ?>
            <div class="checkout-content">
                <div class="checkout-items">
                    <h3>عناصر الطلب</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="checkout-item">
                            <span><?php echo htmlspecialchars($item['product']['title']); ?> × <?php echo $item['qty']; ?></span>
                            <span><?php echo number_format($item['subtotal'], 2); ?> ر.س</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="checkout-summary">
                    <h3>ملخص الطلب</h3>
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span><?php echo number_format($total, 2); ?> ر.س</span>
                    </div>
                    <div class="summary-row">
                        <span>الضريبة (15%):</span>
                        <span><?php echo number_format($tax, 2); ?> ر.س</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>المجموع الكلي:</span>
                        <span><?php echo number_format($grandTotal, 2); ?> ر.س</span>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" class="btn btn-primary btn-large btn-block">تأكيد الطلب</button>
                    </form>
                    <p class="checkout-note">ملاحظة: هذا محاكاة للطلب. لن يتم خصم أي مبلغ.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

