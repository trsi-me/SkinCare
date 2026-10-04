<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// التحقق من صلاحيات المدير
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowedStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
        } catch (PDOException $e) {
            // خطأ صامت
        }
    }
}

header('Location: index.php');
exit;
?>

