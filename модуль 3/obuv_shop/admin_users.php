<?php
include 'config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Обработка добавления пользователя
if ($_POST && isset($_POST['add_user'])) {
    $login = $_POST['login'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $fio = $_POST['fio'];
    
    $stmt = $pdo->prepare("INSERT INTO users (login, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$login, $password, $role]);
    header('Location: admin_users.php');
    exit;
}

// Обработка удаления пользователя
if (isset($_GET['delete'])) {
    // Нельзя удалить самого себя
    if ($_GET['delete'] != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    }
    header('Location: admin_users.php');
    exit;
}

// Обработка редактирования пользователя
if ($_POST && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $login = $_POST['login'];
    $role = $_POST['role'];
    $fio = $_POST['fio'];
    
    // Если указан новый пароль - обновляем его
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET login = ?, password = ?, role = ? WHERE id = ?");
        $stmt->execute([$login, $password, $role, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET login = ?, role = ? WHERE id = ?");
        $stmt->execute([$login, $role, $user_id]);
    }
    header('Location: admin_users.php');
    exit;
}

// Получение списка пользователей
$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();

$page_title = "Управление пользователями - ООО 'Обувь'";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
        <h1 id="logo-text" style="display: none; color: white; margin: 0;">ООО "Обувь"</h1>
    </div>

    <div class="nav">
        <h2>👥 Управление пользователями</h2>
        <div class="nav-links">
            <span style="margin-right: 15px; color: #2c3e50; font-weight: bold;">
                👤 <?= htmlspecialchars($_SESSION['fio'] ?? 'Пользователь') ?>
            </span>
            <a href="products.php" class="btn btn-primary">🛍️ Товары</a>
            <a href="admin_products.php" class="btn btn-primary">📦 Заказы</a>
            <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div style="padding: 20px;">
        <!-- Форма добавления пользователя -->
        <div class="filters-panel">
            <h3>➕ Добавить нового пользователя</h3>
            <form method="POST">
                <input type="hidden" name="add_user" value="1">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                    <div>
                        <label class="form-label">Логин:</label>
                        <input type="text" name="login" class="form-control" required placeholder="Введите логин">
                    </div>
                    <div>
                        <label class="form-label">Пароль:</label>
                        <input type="password" name="password" class="form-control" required placeholder="Введите пароль">
                    </div>
                    <div>
                        <label class="form-label">ФИО:</label>
                        <input type="text" name="fio" class="form-control" required placeholder="Введите ФИО">
                    </div>
                    <div>
                        <label class="form-label">Роль:</label>
                        <select name="role" class="form-control" required>
                            <option value="client">Клиент</option>
                            <option value="manager">Менеджер</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">➕ Добавить</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Список пользователей -->
        <h3 style="margin-top: 30px;">📋 Список пользователей системы</h3>
        
        <?php if (empty($users)): ?>
            <div class="alert alert-error">Пользователи не найдены</div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>ФИО</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="edit_user" value="1">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                
                                <td><?= $user['id'] ?></td>
                                
                                <td>
                                    <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" 
                                           class="form-control" style="width: 100%;" required>
                                </td>
                                
                                <td>
                                    <input type="text" name="fio" value="<?= htmlspecialchars($user['fio'] ?? '') ?>" 
                                           class="form-control" style="width: 100%;" required>
                                </td>
                                
                                <td>
                                    <select name="role" class="form-control" style="width: 100%;" required>
                                        <option value="client" <?= $user['role'] == 'client' ? 'selected' : '' ?>>Клиент</option>
                                        <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                                    </select>
                                </td>
                                
                                <td style="white-space: nowrap;">
                                    <div style="display: flex; gap: 5px; flex-direction: column;">
                                        <input type="password" name="password" placeholder="Новый пароль" 
                                               class="form-control" style="width: 100%;">
                                        <div style="display: flex; gap: 5px;">
                                            <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">
                                                💾 Сохранить
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin_users.php?delete=<?= $user['id'] ?>" 
                                                   class="btn btn-danger" 
                                                   style="padding: 5px 10px; font-size: 12px;"
                                                   onclick="return confirm('Удалить пользователя <?= htmlspecialchars($user['login']) ?>?')">
                                                    🗑️ Удалить
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #7f8c8d; font-size: 12px;">Текущий</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Легенда ролей -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00FA9A;">
                <h4 style="margin-bottom: 10px; color: #2c3e50;">👥 Описание ролей:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong>👑 Администратор</strong>
                        <ul style="margin: 5px 0 0 15px; font-size: 13px; color: #666;">
                            <li>Полный доступ ко всем функциям</li>
                            <li>Управление пользователями</li>
                            <li>Управление товарами и заказами</li>
                        </ul>
                    </div>
                    <div>
                        <strong>📊 Менеджер</strong>
                        <ul style="margin: 5px 0 0 15px; font-size: 13px; color: #666;">
                            <li>Просмотр товаров с фильтрами</li>
                            <li>Управление заказами</li>
                            <li>Отчетность</li>
                        </ul>
                    </div>
                    <div>
                        <strong>👤 Клиент</strong>
                        <ul style="margin: 5px 0 0 15px; font-size: 13px; color: #666;">
                            <li>Просмотр каталога товаров</li>
                            <li>Личный кабинет</li>
                            <li>История заказов</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-20 p-20" style="background-color: #7FFF00; margin-top: 40px;">
        <p style="margin: 0; color: #2c3e50;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0; color: #2c3e50; font-size: 14px;">Панель управления пользователями</p>
    </div>
</body>
</html>