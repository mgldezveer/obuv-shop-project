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
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.style.display='none'">
        <h1>ООО "Обувь" - Вход в систему</h1>
    </div>

    <div class="form-container">
        <h2 class="text-center">Авторизация</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Логин:</label>
                <input type="text" name="login" class="form-control" required 
                       placeholder="Введите ваш логин">
            </div>
            
            <div class="form-group">
                <label class="form-label">Пароль:</label>
                <input type="password" name="password" class="form-control" required 
                       placeholder="Введите ваш пароль">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Войти в систему
            </button>
        </form>
        
        <div class="text-center mt-20">
            <p>Или войдите как гость</p>
            <a href="products.php?guest=true" class="btn btn-secondary">
                Продолжить как гость
            </a>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="margin: 0; color: #7f8c8d;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
    </div>
</body>
</html>