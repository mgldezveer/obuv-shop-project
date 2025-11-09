<?php
include 'config.php';

// Проверка гостевого доступа
$is_guest = isset($_GET['guest']) && $_GET['guest'] == 'true';
$user_role = $is_guest ? 'guest' : ($_SESSION['role'] ?? 'guest');

if (!$is_guest && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

// Устанавливаем заголовок страницы
$page_title = "Каталог обуви - ООО 'Обувь'";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        .discount-badge {
            background-color: #2E8B57;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 14px;
        }
        .discount-high {
            border: 3px solid #2E8B57 !important;
            background: linear-gradient(135deg, #ffffff 0%, #f0fff0 100%);
        }
        .hot-deal {
            color: #e74c3c;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Шапка с логотипом -->
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
        <h1 id="logo-text" style="display: none; color: white; margin: 0;">ООО "Обувь"</h1>
    </div>

    <!-- Навигационная панель -->
    <div class="nav">
        <?php if ($is_guest || $user_role == 'client'): ?>
            <h2>Каталог обуви</h2>
        <?php elseif ($user_role == 'manager'): ?>
            <h2>Панель менеджера - Каталог обуви</h2>
        <?php elseif ($user_role == 'admin'): ?>
            <h2>Панель администратора - Каталог обуви</h2>
        <?php endif; ?>
        
        <div class="nav-links">
            <?php if ($user_role == 'manager'): ?>
                <a href="orders.php" class="btn btn-primary">📋 Просмотр заказов</a>
            <?php elseif ($user_role == 'admin'): ?>
                <a href="admin_products.php" class="btn btn-primary">🛍️ Управление товарами</a>
                <a href="admin_orders.php" class="btn btn-primary">📦 Управление заказами</a>
            <?php endif; ?>
            
            <a href="products.php?guest=true" class="btn btn-secondary">👤 Гость</a>
            
            <?php if (!$is_guest): ?>
                <a href="logout.php" class="btn btn-danger">🚪 Выйти (<?= $_SESSION['login'] ?>)</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">🔑 Войти</a>
            <?php endif; ?>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Панель фильтров и поиска (только для менеджера и администратора) -->
    <?php if ($user_role == 'manager' || $user_role == 'admin'): ?>
    <div class="filters-panel">
        <h3>Поиск и фильтрация</h3>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Поиск по названию или описанию..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control" style="flex: 1;">
            
            <select name="sort" class="form-control" style="width: 200px;">
                <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>По имени (А-Я)</option>
                <option value="name_desc" <?= ($_GET['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>По имени (Я-А)</option>
                <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>По цене (возр.)</option>
                <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>По цене (убыв.)</option>
                <option value="discount_desc" <?= ($_GET['sort'] ?? '') == 'discount_desc' ? 'selected' : '' ?>>По скидке (убыв.)</option>
            </select>
            
            <button type="submit" class="btn btn-primary">🔍 Применить</button>
            <a href="products.php" class="btn btn-secondary">🔄 Сбросить</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- Основной контент - каталог товаров -->
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <h3 style="color: #7f8c8d; margin-bottom: 20px;">Товары не найдены</h3>
                <p style="color: #95a5a6; margin-bottom: 30px;">Попробуйте изменить параметры поиска или фильтры</p>
                <a href="products.php" class="btn btn-primary">Показать все товары</a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <?php
            // Вычисляем цену со скидкой
            $discounted_price = $product['price'];
            if (isset($product['discount']) && $product['discount'] > 0) {
                $discounted_price = $product['price'] * (1 - $product['discount'] / 100);
            }
            ?>
            <div class="product-card <?= (isset($product['discount']) && $product['discount'] > 15) ? 'discount-high' : '' ?>">
                <div class="product-image" style="position: relative;">
                    <!-- Бейдж скидки -->
                    <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                        <div class="discount-badge">
                            -<?= $product['discount'] ?>%
                        </div>
                    <?php endif; ?>
                    
                    <!-- Изображение товара -->
                    <?php
                    $image_path = $product['image_path'];
                    $full_image_path = __DIR__ . '/' . $image_path;
                    ?>
                    
                    <?php if (!empty($image_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($image_path, '/'))): ?>
                        <img src="<?= $image_path ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <div style="width: 100%; height: 220px; background: linear-gradient(135deg, #7FFF00 0%, #00FA9A 100%); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; font-size: 16px;">
                            <div style="font-size: 48px; margin-bottom: 10px;">👟</div>
                            <div style="text-align: center; padding: 0 10px;"><?= htmlspecialchars($product['name']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Информация о товаре -->
                <div class="product-info">
                    <h4 style="margin: 0 0 10px 0; color: #2c3e50; min-height: 50px; line-height: 1.3;">
                        <?= htmlspecialchars($product['name']) ?>
                    </h4>
                    
                    <p style="color: #666; font-size: 14px; margin: 0 0 15px 0; line-height: 1.4; min-height: 60px;">
                        <?= htmlspecialchars($product['description']) ?>
                    </p>
                    
                    <!-- Блок с ценой и деталями -->
                    <div class="product-meta">
                        <!-- Цена -->
                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span class="original-price">
                                    <?= number_format($product['price'], 0, '', ' ') ?> ₽
                                </span>
                                <span style="color: #e74c3c; font-weight: bold; font-size: 20px;">
                                    <?= number_format($discounted_price, 0, '', ' ') ?> ₽
                                </span>
                            </div>
                            
                            <!-- Сообщение о скидке -->
                            <div class="hot-deal" style="text-align: center; margin: 10px 0;">
                                🔥 Экономия <?= number_format($product['price'] - $discounted_price, 0, '', ' ') ?> ₽
                            </div>
                        <?php else: ?>
                            <div class="product-price" style="text-align: center; margin: 10px 0;">
                                <?= number_format($product['price'], 0, '', ' ') ?> ₽
                            </div>
                        <?php endif; ?>
                        
                        <!-- Детали товара -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                            <div style="text-align: center;">
                                <strong style="display: block; color: #7f8c8d; font-size: 12px;">Размер</strong>
                                <span style="font-weight: bold; color: #2c3e50;"><?= $product['size'] ?></span>
                            </div>
                            <div style="text-align: center;">
                                <strong style="display: block; color: #7f8c8d; font-size: 12px;">В наличии</strong>
                                <span style="font-weight: bold; color: <?= $product['quantity'] > 0 ? '#27ae60' : '#e74c3c' ?>;">
                                    <?= $product['quantity'] ?> шт.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Статистика внизу -->
    <?php if (!empty($products)): ?>
    <div class="text-center mt-20 p-20" style="background-color: #f8fff0; border-radius: 10px; margin: 30px 20px;">
        <h3 style="color: #2E8B57; margin-bottom: 15px;">Статистика каталога</h3>
        <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #00FA9A;"><?= count($products) ?></div>
                <div style="color: #7f8c8d;">Всего товаров</div>
            </div>
            <?php
            $discounted_products = array_filter($products, function($product) {
                return isset($product['discount']) && $product['discount'] > 0;
            });
            ?>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #2E8B57;"><?= count($discounted_products) ?></div>
                <div style="color: #7f8c8d;">Товаров со скидкой</div>
            </div>
            <div style="text-align: center;">
                <?php
                $high_discount_products = array_filter($products, function($product) {
                    return isset($product['discount']) && $product['discount'] > 15;
                });
                ?>
                <div style="font-size: 24px; font-weight: bold; color: #e74c3c;"><?= count($high_discount_products) ?></div>
                <div style="color: #7f8c8d;">Скидка >15%</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Подвал -->
    <div class="text-center mt-20 p-20" style="background-color: #7FFF00; margin-top: 40px;">
        <p style="margin: 0; color: #2c3e50;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0; color: #2c3e50; font-size: 14px;">ИС управления магазином обуви</p>
    </div>

</body>
</html>