<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_POST && isset($_POST['add_product'])) {
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, size, quantity) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['size'],
        $_POST['quantity']
    ]);
    header('Location: admin_products.php');
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_products.php');
    exit;
}

if ($_POST && isset($_POST['edit_product'])) {
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, size = ?, quantity = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['size'],
        $_POST['quantity'],
        $_POST['product_id']
    ]);
    header('Location: admin_products.php');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление товарами</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        input, textarea { width: 100%; padding: 5px; box-sizing: border-box; }
        .add-form { background: #f8f9fa; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Управление товарами</h2>
    <a href="products.php">← Назад к товарам</a>

    <div class="add-form">
        <h3>Добавить товар</h3>
        <form method="POST">
            <input type="hidden" name="add_product" value="1">
            <div class="form-group">
                <input type="text" name="name" placeholder="Название" required>
            </div>
            <div class="form-group">
                <textarea name="description" placeholder="Описание" required></textarea>
            </div>
            <div class="form-group">
                <input type="number" name="price" placeholder="Цена" step="0.01" required>
            </div>
            <div class="form-group">
                <input type="number" name="size" placeholder="Размер" required>
            </div>
            <div class="form-group">
                <input type="number" name="quantity" placeholder="Количество" required>
            </div>
            <button type="submit">Добавить товар</button>
        </form>
    </div>

    <h3>Список товаров</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Цена</th>
            <th>Размер</th>
            <th>Количество</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="edit_product" value="1">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <td><?= $product['id'] ?></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required></td>
                <td><textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea></td>
                <td><input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" required></td>
                <td><input type="number" name="size" value="<?= $product['size'] ?>" required></td>
                <td><input type="number" name="quantity" value="<?= $product['quantity'] ?>" required></td>
                <td>
                    <button type="submit">Сохранить</button>
                    <a href="admin_products.php?delete=<?= $product['id'] ?>" onclick="return confirm('Удалить товар?')">Удалить</a>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>