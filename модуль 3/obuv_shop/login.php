<?php
include 'config.php';

$error = '';
if ($_POST && isset($_POST['login']) && isset($_POST['password'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fio'] = $user['fio'] ?? 'Пользователь';
        header('Location: products.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему - ООО "Обувь"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #7FFF00 0%, #00FA9A 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        .login-container {
            max-width: 450px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-top: 5px solid #00FA9A;
            flex: 1;
        }
        .company-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-info h1 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .guest-access {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }
        .test-accounts {
            background: #f8fff0;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #2E8B57;
        }
        .test-accounts h4 {
            color: #2E8B57;
            margin-bottom: 10px;
        }
        .header {
            background-color: #7FFF00;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            max-width: 200px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .footer {
            background-color: #7FFF00;
            padding: 20px;
            text-align: center;
            margin-top: auto;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #00FA9A;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 250, 154, 0.1);
        }
        .input-icon {
            position: absolute;
            left: 12px;
            top: 38px;
            color: #7f8c8d;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .btn-primary {
            background-color: #00FA9A;
            color: #2c3e50;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #00E58B;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-secondary {
            background-color: #7FFF00;
            color: #2c3e50;
        }
        .btn-secondary:hover {
            background-color: #72E600;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .text-center {
            text-align: center;
        }
        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Шапка с логотипом -->
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
        <h1 id="logo-text" style="display: none; color: white; margin: 0;">ООО "Обувь"</h1>
    </div>

    <!-- Основной контент -->
    <div class="login-container">
        <div class="company-info">
            <h1>ООО "Обувь"</h1>
            <p style="color: #7f8c8d; font-size: 16px; line-height: 1.5;">
                Система управления магазином обуви<br>
                <span style="font-size: 14px;">Учет товаров, заказов и клиентов</span>
            </p>
        </div>

        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50; border-bottom: 2px solid #00FA9A; padding-bottom: 10px;">
            🔐 Авторизация в системе
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">👤 Логин пользователя:</label>
                <div class="input-icon">👤</div>
                <input type="text" name="login" class="form-control" required 
                       placeholder="Введите ваш логин" 
                       value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">🔒 Пароль:</label>
                <div class="input-icon">🔒</div>
                <input type="password" name="password" class="form-control" required 
                       placeholder="Введите ваш пароль">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-top: 10px;">
                🔐 Войти в систему
            </button>
        </form>
        
        <div class="guest-access">
            <p style="color: #7f8c8d; margin-bottom: 15px; font-size: 14px;">
                Хотите просто посмотреть каталог товаров?
            </p>
            <a href="products.php?guest=true" class="btn btn-secondary" style="width: 100%; padding: 12px; font-size: 15px;">
                👁️ Просмотр товаров как гость
            </a>
        </div>

        <!-- Тестовые доступы -->
        <div class="test-accounts">
            <h4>🧪 Тестовые доступы для демонстрации:</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                <div style="text-align: left;">
                    <strong>Роль:</strong> Администратор<br>
                    <strong>Логин:</strong> admin<br>
                    <strong>Пароль:</strong> password<br>
                    <span style="color: #2E8B57;">Полный доступ ко всем функциям</span>
                </div>
                <div style="text-align: left;">
                    <strong>Роль:</strong> Менеджер<br>
                    <strong>Логин:</strong> manager<br>
                    <strong>Пароль:</strong> password<br>
                    <span style="color: #2E8B57;">Товары + заказы + фильтры</span>
                </div>
                <div style="text-align: left; grid-column: 1 / -1; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                    <strong>Роль:</strong> Клиент<br>
                    <strong>Логин:</strong> client<br>
                    <strong>Пароль:</strong> password<br>
                    <span style="color: #2E8B57;">Просмотр товаров</span>
                </div>
            </div>
        </div>

        <!-- Информация о системе -->
        <div style="text-align: center; margin-top: 25px; font-size: 12px; color: #95a5a6; line-height: 1.4;">
            <p>
                <strong>Система обеспечивает:</strong><br>
                • Учет товаров на складе 📦<br>
                • Управление заказами 📋<br>
                • Ролевой доступ пользователей 👥<br>
                • Автоматический подсчет скидок 🏷️
            </p>
        </div>
    </div>

    <!-- Подвал -->
    <div class="footer">
        <p style="margin: 0; color: #2c3e50; font-weight: bold;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0; color: #2c3e50; font-size: 12px;">
            Версия системы: 1.0 | ИС управления магазином обуви
        </p>
    </div>
</body>
</html>