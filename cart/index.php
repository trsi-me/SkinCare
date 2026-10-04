<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;

if (!empty($cart)) {
    $placeholders = str_repeat('?,', count($cart) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $qty = $cart[$product['id']];
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = [
            'product' => $product,
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

$tax = $total * 0.15; // ضريبة 15%
$grandTotal = $total + $tax;

// معالجة تحديث الكمية أو الحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = intval($_POST['product_id'] ?? 0);
    
    if ($action === 'update') {
        $qty = intval($_POST['qty'] ?? 0);
        if ($qty > 0) {
            $_SESSION['cart'][$productId] = $qty;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
    }
    
    header('Location: index.php');
    exit;
}

$pageTitle = 'عربة التسوق';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cart-page">
    <div class="container">
        <h1 class="page-title">عربة التسوق</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>عربة التسوق فارغة</p>
                <a href="<?php echo $basePath; ?>products/index.php" class="btn btn-primary">تصفح المنتجات</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="<?php echo $basePath; ?>assets/images/<?php echo htmlspecialchars($item['product']['image'] ?? 'img_01.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product']['title']); ?>"
                                     onerror="this.src='<?php echo $basePath; ?>assets/images/img_01.jpg'">
                            </div>
                            <div class="cart-item-info">
                                <h3><?php echo htmlspecialchars($item['product']['title']); ?></h3>
                                <p class="cart-item-price"><?php echo number_format($item['product']['price'], 2); ?> ر.س</p>
                            </div>
                            <div class="cart-item-actions">
                                <form method="POST" class="cart-item-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="number" name="qty" value="<?php echo $item['qty']; ?>" 
                                           min="1" max="<?php echo $item['product']['stock']; ?>" 
                                           onchange="this.form.submit()">
                                </form>
                                <form method="POST" class="cart-item-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger btn-small">حذف</button>
                                </form>
                            </div>
                            <div class="cart-item-subtotal">
                                <p><?php echo number_format($item['subtotal'], 2); ?> ر.س</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
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
                    <a href="checkout.php" class="btn btn-primary btn-large btn-block">إتمام الطلب</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

