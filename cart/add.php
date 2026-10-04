<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
    exit;
}

require_once __DIR__ . '/../includes/database.php';

try {
    // التحقق من وجود المنتج والمخزون
    $stmt = $pdo->prepare("SELECT id, stock, title FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة في المخزون']);
        exit;
    }
    
    // إضافة للعربة
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $newQty = $_SESSION['cart'][$productId] + $quantity;
        if ($newQty > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة في المخزون']);
            exit;
        }
        $_SESSION['cart'][$productId] = $newQty;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    echo json_encode(['success' => true, 'message' => 'تمت الإضافة بنجاح']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
}
?>

