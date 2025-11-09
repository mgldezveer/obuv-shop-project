<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'manager' && $_SESSION['role'] != 'admin')) {
    header('Location: login.php');
    exit;
}

$orders = $pdo->query("
    SELECT o.*, u.login, u.fio, p.name as product_name, pp.address 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN products p ON o.product_id = p.id 
    LEFT JOIN pickup_points pp ON o.pickup_point_id = pp.id 
    ORDER BY o.order_date DESC
")->fetchAll();

$page_title = "Управление заказами - ООО 'Обувь'";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo">
    </div>

    <div class="nav">
        <h2>📦 Управление заказами</h2>
        <div class="nav-links">
            <span style="margin-right: 15px; color: #2c3e50; font-weight: bold;">
                👤 <?= htmlspecialchars($_SESSION['fio']) ?>
            </span>
            <a href="products.php" class="btn btn-primary">🛍️ Товары</a>
            <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
        </div>
    </div>

    <div style="padding: 20px;">
        <h3>Список заказов</h3>
        <?php if (empty($orders)): ?>
            <div class="alert alert-error">Заказов нет</div>
        <?php else: ?>
            <table class="data-table">
                <tr>
                    <th>ID</th><th>Клиент</th><th>Товар</th><th>Кол-во</th>
                    <th>Дата</th><th>Пункт выдачи</th><th>Статус</th>
                </tr>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['fio']) ?></td>
                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                    <td><?= $order['quantity'] ?></td>
                    <td><?= $order['order_date'] ?></td>
                    <td><?= htmlspecialchars($order['address']) ?></td>
                    <td><?= $order['status'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="margin: 0; color: #7f8c8d;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
    </div>
</body>
</html>