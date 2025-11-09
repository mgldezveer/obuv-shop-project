<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'manager' && $_SESSION['role'] != 'admin')) {
    header('Location: login.php');
    exit;
}

$orders = $pdo->query("
    SELECT o.*, u.login, p.name as product_name, pp.address 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN products p ON o.product_id = p.id 
    LEFT JOIN pickup_points pp ON o.pickup_point_id = pp.id 
    ORDER BY o.order_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказы</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Просмотр заказов</h2>
    <a href="products.php">← Назад к товарам</a>

    <h3>Список заказов</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Товар</th>
            <th>Количество</th>
            <th>Дата заказа</th>
            <th>Пункт выдачи</th>
            <th>Статус</th>
        </tr>
        <?php if (empty($orders)): ?>
            <tr><td colspan="7">Заказов нет</td></tr>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['login']) ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= $order['order_date'] ?></td>
                <td><?= htmlspecialchars($order['address']) ?></td>
                <td><?= $order['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="margin: 0; color: #7f8c8d;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
    </div>
</body>
</html>