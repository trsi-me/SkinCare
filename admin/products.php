<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// التحقق من صلاحيات المدير
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$error = '';
$success = '';

// معالجة إضافة/تعديل/حذف منتج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $productId = $action === 'edit' ? intval($_POST['product_id'] ?? 0) : 0;
        
        // معالجة رفع الصورة
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];
            
            if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) { // 5MB
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '_' . uniqid() . '.' . $extension;
                $uploadPath = __DIR__ . '/../assets/images/' . $imageName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $error = 'فشل رفع الصورة';
                }
            } else {
                $error = 'نوع الملف غير مدعوم أو الحجم كبير جداً';
            }
        }
        
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO products (sku, title, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$sku, $title, $description, $price, $stock, $imageName ?: null]);
                    $success = 'تم إضافة المنتج بنجاح';
                } else {
                    if ($imageName) {
                        $stmt = $pdo->prepare("UPDATE products SET sku = ?, title = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
                        $stmt->execute([$sku, $title, $description, $price, $stock, $imageName, $productId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE products SET sku = ?, title = ?, description = ?, price = ?, stock = ? WHERE id = ?");
                        $stmt->execute([$sku, $title, $description, $price, $stock, $productId]);
                    }
                    $success = 'تم تحديث المنتج بنجاح';
                }
            } catch (PDOException $e) {
                $error = 'حدث خطأ: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $productId = intval($_POST['product_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $success = 'تم حذف المنتج بنجاح';
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء الحذف';
        }
    }
}

// جلب المنتجات
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

// جلب منتج للتعديل
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

$pageTitle = 'إدارة المنتجات';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <h1 class="page-title">إدارة المنتجات</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <div class="admin-form-section">
                <h2><?php echo $editProduct ? 'تعديل منتج' : 'إضافة منتج جديد'; ?></h2>
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="sku">رمز المنتج (SKU)</label>
                        <input type="text" id="sku" name="sku" required value="<?php echo htmlspecialchars($editProduct['sku'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="title">اسم المنتج</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editProduct['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">السعر (ر.س)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $editProduct['price'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">المخزون</label>
                            <input type="number" id="stock" name="stock" min="0" required value="<?php echo $editProduct['stock'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">صورة المنتج</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                        <?php if ($editProduct && $editProduct['image']): ?>
                            <p>الصورة الحالية: <?php echo htmlspecialchars($editProduct['image']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $editProduct ? 'تحديث' : 'إضافة'; ?></button>
                    <?php if ($editProduct): ?>
                        <a href="products.php" class="btn btn-secondary">إلغاء</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="admin-list-section">
                <h2>قائمة المنتجات</h2>
                <?php if (empty($products)): ?>
                    <p>لا توجد منتجات</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>الاسم</th>
                                <th>SKU</th>
                                <th>السعر</th>
                                <th>المخزون</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $basePath; ?>assets/images/<?php echo htmlspecialchars($product['image'] ?? 'img_01.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             onerror="this.src='<?php echo $basePath; ?>assets/images/img_01.jpg'">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?> ر.س</td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-secondary btn-small">تعديل</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

