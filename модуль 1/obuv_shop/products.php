<?php
include 'config.php';

// Проверка гостевого доступа
$is_guest = isset($_GET['guest']) && $_GET['guest'] == 'true';
$user_role = $is_guest ? 'guest' : ($_SESSION['role'] ?? 'guest');

if (!$is_guest && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Товары - ООО Обувь</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f5f5f5;
        }
        .filters { 
            background: #ffffff; 
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav { 
            margin-bottom: 20px; 
            padding: 15px; 
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        input[type="text"], select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        button {
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }
        button:hover {
            background: #2980b9;
        }
        .guest-btn {
            background: #27ae60;
        }
        .guest-btn:hover {
            background: #219a52;
        }
    </style>
</head>
<body>
    <div class="nav">
        <?php if ($is_guest || $user_role == 'client'): ?>
            <h2 style="color: #2c3e50; margin: 0;">Просмотр товаров</h2>
        <?php elseif ($user_role == 'manager'): ?>
            <h2 style="color: #2c3e50; margin: 0;">Панель менеджера</h2>
            <a href="orders.php" style="color: #3498db; text-decoration: none;">📋 Просмотр заказов</a> |
        <?php elseif ($user_role == 'admin'): ?>
            <h2 style="color: #2c3e50; margin: 0;">Панель администратора</h2>
            <a href="admin_products.php" style="color: #3498db; text-decoration: none;">🛍️ Управление товарами</a> |
            <a href="admin_orders.php" style="color: #3498db; text-decoration: none;">📦 Управление заказами</a> |
        <?php endif; ?>
        
        <a href="products.php?guest=true" style="color: #27ae60; text-decoration: none;">👤 Гость</a> |
        
        <?php if (!$is_guest): ?>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Выйти (<?= $_SESSION['login'] ?>)</a>
        <?php else: ?>
            <a href="login.php" style="color: #3498db; text-decoration: none;">🔑 Войти</a>
        <?php endif; ?>
    </div>

    <?php if ($user_role == 'manager' || $user_role == 'admin'): ?>
    <div class="filters">
        <h3 style="margin-top: 0;">Фильтры и поиск</h3>
        <form method="GET">
            <input type="text" name="search" placeholder="Поиск по названию или описанию..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 300px;">
            
            <select name="sort">
                <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>По имени (А-Я)</option>
                <option value="name_desc" <?= ($_GET['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>По имени (Я-А)</option>
                <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>По цене (возр.)</option>
                <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>По цене (убыв.)</option>
            </select>
            
            <button type="submit">🔍 Применить</button>
            <a href="products.php" style="color: #7f8c8d; text-decoration: none; margin-left: 10px;">🔄 Сбросить</a>
        </form>
    </div>
    <?php endif; ?>

    <h3 style="color: #2c3e50;">Каталог обуви</h3>
    
    <?php
    // Построение запроса в зависимости от роли
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if (($user_role == 'manager' || $user_role == 'admin') && isset($_GET['search']) && !empty($_GET['search'])) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $search_term = "%{$_GET['search']}%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    // Сортировка только для менеджера и администратора
    if ($user_role == 'manager' || $user_role == 'admin') {
        $sort = $_GET['sort'] ?? 'name_asc';
        switch ($sort) {
            case 'name_desc': $sql .= " ORDER BY name DESC"; break;
            case 'price_asc': $sql .= " ORDER BY price ASC"; break;
            case 'price_desc': $sql .= " ORDER BY price DESC"; break;
            default: $sql .= " ORDER BY name ASC"; break;
        }
    } else {
        $sql .= " ORDER BY name ASC"; // Базовая сортировка для гостей и клиентов
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    ?>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                <h3>Товары не найдены</h3>
                <p>Попробуйте изменить параметры поиска</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="product" style="border: 1px solid #ddd; border-radius: 10px; padding: 15px; width: 280px; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.2s;">
                <div class="product-image" style="margin-bottom: 15px; text-align: center;">
                    <?php
                    $image_path = $product['image_path'];
                    $full_image_path = __DIR__ . '/' . $image_path;
                    ?>
                    
                    <?php if (!empty($image_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($image_path, '/'))): ?>
                        <img src="<?= $image_path ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                    <?php else: ?>
                        <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; font-size: 16px;">
                            <div style="font-size: 48px; margin-bottom: 10px;">👟</div>
                            <div><?= htmlspecialchars($product['name']) ?></div>
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.8;">
                                <?= $image_path ? 'Файл не найден' : 'Нет изображения' ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 18px; min-height: 50px;"><?= htmlspecialchars($product['name']) ?></h4>
                <p style="color: #7f8c8d; font-size: 14px; margin: 0 0 15px 0; line-height: 1.4; min-height: 60px;"><?= htmlspecialchars($product['description']) ?></p>
                
                <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 4px solid #3498db;">
                    <p style="margin: 8px 0; font-size: 16px;">
                        <strong>Цена:</strong> 
                        <span style="color: #e74c3c; font-weight: bold; font-size: 18px;"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
                    </p>
                    <p style="margin: 8px 0; font-size: 14px;"><strong>Размер:</strong> <?= $product['size'] ?></p>
                    <p style="margin: 8px 0; font-size: 14px;">
                        <strong>В наличии:</strong> 
                        <span style="color: <?= $product['quantity'] > 0 ? '#27ae60' : '#e74c3c' ?>; font-weight: bold;">
                            <?= $product['quantity'] ?> шт.
                        </span>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($products)): ?>
    <div style="text-align: center; margin-top: 30px; color: #7f8c8d;">
        <p>Показано <?= count($products) ?> товар(ов)</p>
    </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="margin: 0; color: #7f8c8d;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">ИС управления магазином обуви</p>
    </div>
</body>
</html>