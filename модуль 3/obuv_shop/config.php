<?php
// Настройки подключения к базе данных
$host = 'localhost';
$dbname = 'obuv_shop';
$username = 'root';
$password = 'root';

// Параметры для отладки (можно закомментировать в продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Создаем подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Устанавливаем режим ошибок - исключения
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Устанавливаем режим выборки по умолчанию - ассоциативный массив
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Отключаем эмуляцию подготовленных запросов для безопасности
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // В случае ошибки подключения выводим сообщение
    die("❌ Ошибка подключения к базе данных: " . $e->getMessage());
}

// Стартуем сессию для хранения данных пользователя
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Функция для проверки авторизации
function checkAuth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    if ($required_role && $_SESSION['role'] != $required_role) {
        header('Location: products.php');
        exit;
    }
    
    return true;
}

// Функция для получения информации о текущем пользователе
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'login' => $_SESSION['login'] ?? null,
        'role' => $_SESSION['role'] ?? 'guest',
        'fio' => $_SESSION['fio'] ?? 'Гость'
    ];
}

// Функция для защиты от XSS атак
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Функция для редиректа с сообщением
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Функция для отображения flash сообщений
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        $class = $type == 'error' ? 'alert-error' : 'alert-success';
        
        echo "<div class='alert $class'>$message</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Автоматическая установка временной зоны
date_default_timezone_set('Europe/Moscow');

// Настройки для безопасности
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

?>