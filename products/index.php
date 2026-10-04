<?php
require_once __DIR__ . '/../includes/database.php';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE title LIKE ? OR description LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm];
}

$orderBy = 'created_at DESC';
switch ($sort) {
    case 'price_low':
        $orderBy = 'price ASC';
        break;
    case 'price_high':
        $orderBy = 'price DESC';
        break;
    case 'name':
        $orderBy = 'title ASC';
        break;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products $whereClause ORDER BY $orderBy");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$pageTitle = 'المنتجات';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="products-page">
    <div class="container">
        <h1 class="page-title">منتجاتنا</h1>
        
        <div class="products-filters">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="ابحث عن منتج..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">بحث</button>
            </form>
            <form method="GET" class="sort-form">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>السعر: منخفض إلى عالي</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>السعر: عالي إلى منخفض</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>الاسم: أ-ي</option>
                </select>
            </form>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>لا توجد منتجات متاحة</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="view.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo $basePath; ?>assets/images/<?php echo htmlspecialchars($product['image'] ?? 'img_01.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     onerror="this.src='<?php echo $basePath; ?>assets/images/img_01.jpg'">
                            </a>
                        </div>
                        <div class="product-info">
                            <h3><a href="view.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['title']); ?></a></h3>
                            <p class="product-price"><?php echo number_format($product['price'], 2); ?> ر.س</p>
                            <p class="product-stock"><?php echo $product['stock'] > 0 ? 'متوفر' : 'غير متوفر'; ?></p>
                            <button class="btn btn-primary add-to-cart" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['title']); ?>"
                                    <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo $product['stock'] > 0 ? 'أضف للعربة' : 'غير متوفر'; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

