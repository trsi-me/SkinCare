<?php
require_once __DIR__ . '/../includes/database.php';

$productId = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    $product = null;
}

if (!$product) {
    header('Location: index.php');
    exit;
}

$pageTitle = $product['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="product-detail-page">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?php echo $basePath; ?>assets/images/<?php echo htmlspecialchars($product['image'] ?? 'img_01.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                     onerror="this.src='<?php echo $basePath; ?>assets/images/img_01.jpg'">
            </div>
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['title']); ?></h1>
                <p class="product-sku">رمز المنتج: <?php echo htmlspecialchars($product['sku']); ?></p>
                <p class="product-price-large"><?php echo number_format($product['price'], 2); ?> ر.س</p>
                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'لا يوجد وصف متاح')); ?></p>
                </div>
                <div class="product-stock-info">
                    <p>المخزون: <strong><?php echo $product['stock']; ?> قطعة</strong></p>
                </div>
                <div class="product-add-form">
                    <form id="addToCartForm">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label for="quantity">الكمية:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large" 
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <?php echo $product['stock'] > 0 ? 'أضف للعربة' : 'غير متوفر'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const productId = <?php echo $product['id']; ?>;
    const quantity = parseInt(document.getElementById('quantity').value);
    const productName = '<?php echo htmlspecialchars($product['title'], ENT_QUOTES); ?>';
    
    addToCart(productId, quantity, productName);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

