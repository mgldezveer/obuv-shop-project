<?php
include 'config.php';

// Проверка гостевого доступа
$is_guest = isset($_GET['guest']) && $_GET['guest'] == 'true';
$user_role = $is_guest ? 'guest' : ($_SESSION['role'] ?? 'guest');

if (!$is_guest && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Построение запроса
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (($user_role == 'manager' || $user_role == 'admin') && isset($_GET['search']) && !empty($_GET['search'])) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ? OR manufacturer LIKE ?)";
    $search_term = "%{$_GET['search']}%";
    $params = array_fill(0, 4, $search_term);
}

// Сортировка для менеджера и администратора
if ($user_role == 'manager' || $user_role == 'admin') {
    $sort = $_GET['sort'] ?? 'name_asc';
    switch ($sort) {
        case 'name_desc': $sql .= " ORDER BY name DESC"; break;
        case 'price_asc': $sql .= " ORDER BY price ASC"; break;
        case 'price_desc': $sql .= " ORDER BY price DESC"; break;
        case 'discount_desc': $sql .= " ORDER BY discount DESC"; break;
        case 'quantity_asc': $sql .= " ORDER BY quantity ASC"; break;
        default: $sql .= " ORDER BY name ASC"; break;
    }
} else {
    $sql .= " ORDER BY name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

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
        .out-of-stock {
            background-color: #e0f7ff !important;
        }
        .discount-high {
            background-color: #2E8B57 !important;
            color: white !important;
        }
        .discount-high td {
            color: white !important;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
        }
        .discount-price {
            color: #e74c3c;
            font-weight: bold;
        }
        .final-price {
            color: #2c3e50;
            font-weight: bold;
        }
        .product-image-small {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .no-image {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
        <h1 id="logo-text" style="display: none; color: white; margin: 0;">ООО "Обувь"</h1>
    </div>

    <div class="nav">
        <?php if ($is_guest || $user_role == 'client'): ?>
            <h2>Каталог обуви - Просмотр</h2>
        <?php elseif ($user_role == 'manager'): ?>
            <h2>Панель менеджера - Управление заказами</h2>
        <?php elseif ($user_role == 'admin'): ?>
            <h2>Панель администратора - Управление системой</h2>
        <?php endif; ?>
        
        <div class="nav-links">
            <!-- Отображение ФИО пользователя -->
            <?php if (!$is_guest): ?>
                <span style="margin-right: 15px; color: #2c3e50; font-weight: bold;">
                    👤 <?= htmlspecialchars($_SESSION['fio'] ?? 'Пользователь') ?>
                </span>
            <?php endif; ?>

            <?php if ($user_role == 'manager'): ?>
                <a href="orders.php" class="btn btn-primary">📋 Заказы</a>
            <?php elseif ($user_role == 'admin'): ?>
                <a href="admin_products.php" class="btn btn-primary">🛍️ Товары</a>
                <a href="admin_orders.php" class="btn btn-primary">📦 Заказы</a>
                <a href="admin_users.php" class="btn btn-primary">👥 Пользователи</a>
            <?php endif; ?>
            
            <a href="products.php?guest=true" class="btn btn-secondary">👤 Гость</a>
            
            <?php if (!$is_guest): ?>
                <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">🔑 Войти</a>
            <?php endif; ?>
        </div>
        <div style="clear: both;"></div>
    </div>

    <?php if ($user_role == 'manager' || $user_role == 'admin'): ?>
    <div class="filters-panel">
        <h3>🔍 Поиск и фильтрация</h3>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Поиск по названию, описанию, категории..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control" style="flex: 1;">
            
            <select name="sort" class="form-control" style="width: 220px;">
                <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>По названию (А-Я)</option>
                <option value="name_desc" <?= ($_GET['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>По названию (Я-А)</option>
                <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>По цене (возр.)</option>
                <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>По цене (убыв.)</option>
                <option value="discount_desc" <?= ($_GET['sort'] ?? '') == 'discount_desc' ? 'selected' : '' ?>>По скидке (убыв.)</option>
                <option value="quantity_asc" <?= ($_GET['sort'] ?? '') == 'quantity_asc' ? 'selected' : '' ?>>По наличию (мало→много)</option>
            </select>
            
            <button type="submit" class="btn btn-primary">🔍 Найти</button>
            <a href="products.php" class="btn btn-secondary">🔄 Сбросить</a>
        </form>
    </div>
    <?php endif; ?>

    <div style="padding: 20px;">
        <h3 style="margin-bottom: 20px; color: #2c3e50;">
            📦 Список товаров на складе 
            <span style="font-size: 14px; color: #7f8c8d;">(всего: <?= count($products) ?>)</span>
        </h3>

        <?php if (empty($products)): ?>
            <div class="alert alert-error" style="text-align: center;">
                <h4>Товары не найдены</h4>
                <p>Попробуйте изменить параметры поиска</p>
                <a href="products.php" class="btn btn-primary">Показать все товары</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Фото</th>
                            <th>Наименование</th>
                            <th>Категория</th>
                            <th>Описание</th>
                            <th>Производитель</th>
                            <th>Поставщик</th>
                            <th>Цена</th>
                            <th>Ед. изм.</th>
                            <th>Количество</th>
                            <th>Скидка</th>
                            <th>Ответственный</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <?php
                        $discounted_price = $product['price'];
                        if (isset($product['discount']) && $product['discount'] > 0) {
                            $discounted_price = $product['price'] * (1 - $product['discount'] / 100);
                        }
                        
                        $row_class = '';
                        if ($product['quantity'] == 0) {
                            $row_class = 'out-of-stock';
                        } elseif (isset($product['discount']) && $product['discount'] > 15) {
                            $row_class = 'discount-high';
                        }
                        ?>
                        <tr class="<?= $row_class ?>">
                            <!-- Фото товара -->
                            <td style="text-align: center;">
                                <?php
                                $image_path = $product['image_path'];
                                $full_image_path = __DIR__ . '/' . $image_path;
                                ?>
                                
                                <?php if (!empty($image_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($image_path, '/'))): ?>
                                    <img src="<?= $image_path ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         class="product-image-small">
                                <?php else: ?>
                                    <div class="no-image">
                                        Нет фото
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Наименование -->
                            <td style="font-weight: bold;"><?= htmlspecialchars($product['name']) ?></td>
                            
                            <!-- Категория -->
                            <td><?= htmlspecialchars($product['category'] ?? 'Не указана') ?></td>
                            
                            <!-- Описание -->
                            <td style="max-width: 200px;"><?= htmlspecialchars($product['description']) ?></td>
                            
                            <!-- Производитель -->
                            <td><?= htmlspecialchars($product['manufacturer'] ?? 'Не указан') ?></td>
                            
                            <!-- Поставщик -->
                            <td><?= htmlspecialchars($product['supplier'] ?? 'Не указан') ?></td>
                            
                            <!-- Цена -->
                            <td style="text-align: right;">
                                <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                                    <div>
                                        <span class="original-price">
                                            <?= number_format($product['price'], 0, '', ' ') ?> ₽
                                        </span>
                                        <br>
                                        <span class="final-price">
                                            <?= number_format($discounted_price, 0, '', ' ') ?> ₽
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="final-price">
                                        <?= number_format($product['price'], 0, '', ' ') ?> ₽
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Единица измерения -->
                            <td style="text-align: center;"><?= htmlspecialchars($product['unit'] ?? 'шт.') ?></td>
                            
                            <!-- Количество -->
                            <td style="text-align: center; font-weight: bold; color: <?= $product['quantity'] > 0 ? '#27ae60' : '#e74c3c' ?>;">
                                <?= $product['quantity'] ?>
                            </td>
                            
                            <!-- Скидка -->
                            <td style="text-align: center;">
                                <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                                    <span style="color: #e74c3c; font-weight: bold;">
                                        <?= $product['discount'] ?>%
                                    </span>
                                <?php else: ?>
                                    <span style="color: #7f8c8d;">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Ответственный -->
                            <td><?= htmlspecialchars($product['fio'] ?? 'Не указан') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Легенда -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00FA9A;">
                <h4 style="margin-bottom: 10px; color: #2c3e50;">📋 Легенда:</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background-color: #2E8B57; border-radius: 3px;"></div>
                        <span>Скидка >15%</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background-color: #e0f7ff; border-radius: 3px;"></div>
                        <span>Нет в наличии</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #e74c3c; font-weight: bold;">Красный текст</span>
                        <span>Цена со скидкой</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-20 p-20" style="background-color: #7FFF00; margin-top: 40px;">
        <p style="margin: 0; color: #2c3e50;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0; color: #2c3e50; font-size: 14px;">ИС управления магазином обуви</p>
    </div>
</body>
</html>