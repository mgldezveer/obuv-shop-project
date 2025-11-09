<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - ООО Обувь</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 100px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; margin: 5px; }
        .guest-btn { background: #28a745; color: white; border: none; }
        .login-btn { background: #007bff; color: white; border: none; }
    </style>
</head>
<body>
    <h2>Вход в систему</h2>
    
    <?php
    if ($_POST) {
        if (isset($_POST['login']) && isset($_POST['password'])) {
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
                echo '<p style="color: red;">Неверный логин или пароль</p>';
            }
        }
    }
    ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Логин:</label>
            <input type="text" name="login" required>
        </div>
        <div class="form-group">
            <label>Пароль:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="login-btn">Войти</button>
        <a href="products.php?guest=true"><button type="button" class="guest-btn">Войти как гость</button></a>
    </form>
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="margin: 0; color: #7f8c8d;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
    </div>
</body>
</html>