<?php
require_once __DIR__ . '/includes/database.php';

// جلب منتجات مميزة
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 6");
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredProducts = [];
}

$pageTitle = 'سكن كير - متجر العناية بالبشرة';
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-page">
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>مرحباً بك في سكن كير</h1>
                <p>اكتشف مجموعتنا المميزة من منتجات العناية بالبشرة الطبيعية</p>
                <a href="products/index.php" class="btn btn-primary btn-large">تصفح المنتجات</a>
            </div>
        </div>
    </section>
    
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">منتجات مميزة</h2>
            <?php if (empty($featuredProducts)): ?>
                <p>لا توجد منتجات متاحة حالياً</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="products/view.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo $basePath; ?>assets/images/<?php echo htmlspecialchars($product['image'] ?? 'img_01.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                                         onerror="this.src='<?php echo $basePath; ?>assets/images/img_01.jpg'">
                                </a>
                            </div>
                            <div class="product-info">
                                <h3><a href="products/view.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['title']); ?></a></h3>
                                <p class="product-price"><?php echo number_format($product['price'], 2); ?> ر.س</p>
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars($product['title']); ?>">
                                    أضف للعربة
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="section-cta">
                    <a href="products/index.php" class="btn btn-secondary">عرض جميع المنتجات</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-item">
                    <h3>منتجات طبيعية</h3>
                    <p>جميع منتجاتنا مصنوعة من مكونات طبيعية آمنة</p>
                </div>
                <div class="feature-item">
                    <h3>توصيل سريع</h3>
                    <p>نوصل طلباتك بسرعة وأمان</p>
                </div>
                <div class="feature-item">
                    <h3>ضمان الجودة</h3>
                    <p>نضمن جودة جميع منتجاتنا</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

