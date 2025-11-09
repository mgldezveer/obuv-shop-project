<?php
include 'config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Обработка действий с заказами
if (isset($_GET['delete_order'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$_GET['delete_order']]);
    header('Location: admin_orders.php');
    exit;
}

if (isset($_GET['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_GET['status'], $_GET['update_status']]);
    header('Location: admin_orders.php');
    exit;
}

// Добавление нового заказа
if ($_POST && isset($_POST['add_order'])) {
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, pickup_point_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['user_id'],
        $_POST['product_id'],
        $_POST['quantity'],
        $_POST['pickup_point_id'],
        $_POST['status']
    ]);
    header('Location: admin_orders.php');
    exit;
}

// Редактирование заказа
if ($_POST && isset($_POST['edit_order'])) {
    $stmt = $pdo->prepare("UPDATE orders SET user_id = ?, product_id = ?, quantity = ?, pickup_point_id = ?, status = ? WHERE id = ?");
    $stmt->execute([
        $_POST['user_id'],
        $_POST['product_id'],
        $_POST['quantity'],
        $_POST['pickup_point_id'],
        $_POST['status'],
        $_POST['order_id']
    ]);
    header('Location: admin_orders.php');
    exit;
}

// Получение данных для форм
$users = $pdo->query("SELECT id, login FROM users")->fetchAll();
$products = $pdo->query("SELECT id, name FROM products")->fetchAll();
$pickup_points = $pdo->query("SELECT id, address FROM pickup_points")->fetchAll();

// Получение заказов с дополнительной информацией
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
    <title>Управление заказами</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        .add-form { background: #f8f9fa; padding: 15px; margin-bottom: 20px; }
        select, input { padding: 5px; margin: 2px; }
        .status-pending { color: orange; }
        .status-completed { color: green; }
        .status-cancelled { color: red; }
    </style>
</head>
<body>
    <h2>Управление заказами</h2>
    <a href="products.php">← Назад к товарам</a>

    <div class="add-form">
        <h3>Добавить заказ</h3>
        <form method="POST">
            <input type="hidden" name="add_order" value="1">
            
            <div class="form-group">
                <label>Пользователь:</label>
                <select name="user_id" required>
                    <option value="">Выберите пользователя</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['login']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Товар:</label>
                <select name="product_id" required>
                    <option value="">Выберите товар</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Количество:</label>
                <input type="number" name="quantity" min="1" required>
            </div>

            <div class="form-group">
                <label>Пункт выдачи:</label>
                <select name="pickup_point_id" required>
                    <option value="">Выберите пункт выдачи</option>
                    <?php foreach ($pickup_points as $point): ?>
                        <option value="<?= $point['id'] ?>"><?= htmlspecialchars($point['address']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Статус:</label>
                <select name="status" required>
                    <option value="pending">Ожидание</option>
                    <option value="completed">Завершен</option>
                    <option value="cancelled">Отменен</option>
                </select>
            </div>

            <button type="submit">Добавить заказ</button>
        </form>
    </div>

    <h3>Список заказов</h3>
    
    <?php if (empty($orders)): ?>
        <p>Заказов нет</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Товар</th>
                <th>Количество</th>
                <th>Дата заказа</th>
                <th>Пункт выдачи</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($orders as $order): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="edit_order" value="1">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    
                    <td><?= $order['id'] ?></td>
                    
                    <td>
                        <select name="user_id" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $user['id'] == $order['user_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['login']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    
                    <td>
                        <select name="product_id" required>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= $product['id'] == $order['product_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    
                    <td><input type="number" name="quantity" value="<?= $order['quantity'] ?>" min="1" required></td>
                    
                    <td><?= $order['order_date'] ?></td>
                    
                    <td>
                        <select name="pickup_point_id" required>
                            <?php foreach ($pickup_points as $point): ?>
                                <option value="<?= $point['id'] ?>" <?= $point['id'] == $order['pickup_point_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($point['address']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    
                    <td>
                        <select name="status" required>
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Ожидание</option>
                            <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Завершен</option>
                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                        </select>
                    </td>
                    
                    <td>
                        <button type="submit">Сохранить</button>
                        <a href="admin_orders.php?delete_order=<?= $order['id'] ?>" 
                           onclick="return confirm('Удалить заказ?')">Удалить</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <h4>Быстрое изменение статуса:</h4>
        <?php foreach ($orders as $order): ?>
        <div style="display: inline-block; margin: 5px; padding: 10px; border: 1px solid #ddd;">
            Заказ #<?= $order['id'] ?> 
            (<span class="status-<?= $order['status'] ?>"><?= $order['status'] ?></span>)
            <br>
            <a href="admin_orders.php?update_status=<?= $order['id'] ?>&status=completed">✅ Завершить</a> |
            <a href="admin_orders.php?update_status=<?= $order['id'] ?>&status=cancelled">❌ Отменить</a> |
            <a href="admin_orders.php?update_status=<?= $order['id'] ?>&status=pending">⏳ В ожидание</a>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>