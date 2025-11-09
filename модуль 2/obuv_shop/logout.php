<?php
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выход из системы - ООО "Обувь"</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f8f9fa; }
        .container { max-width: 500px; margin: 0 auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0.1); }
        .message { color: #28a745; font-size: 18px; margin-bottom: 20px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">Вы успешно вышли из системы</div>
        <p>Спасибо, что пользовались нашей системой!</p>
        <a href="login.php" style="color: #007bff; text-decoration: none;">Вернуться на страницу входа</a>
        
        <div class="footer">
            <p style="margin: 0;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
            <p style="margin: 5px 0 0; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выход из системы - ООО "Обувь"</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f8f9fa; }
        .container { max-width: 500px; margin: 0 auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0.1); }
        .message { color: #28a745; font-size: 18px; margin-bottom: 20px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">Вы успешно вышли из системы</div>
        <p>Спасибо, что пользовались нашей системой!</p>
        <a href="login.php" style="color: #007bff; text-decoration: none;">Вернуться на страницу входа</a>
        
        <div class="footer">
            <p style="margin: 0;">&copy; 2025 ООО "Обувь". Все права защищены.</p>
            <p style="margin: 5px 0 0 0; font-size: 12px;">Версия системы: 1.0 | ИС управления магазином обуви</p>
        </div>
    </div>
</body>
</html>