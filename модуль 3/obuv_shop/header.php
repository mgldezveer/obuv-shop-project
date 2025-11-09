<?php
// header.php - общий заголовок для всех страниц
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?? 'ООО "Обувь"' ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="ООО Обувь" class="logo" onerror="this.innerHTML='<h1>ООО Обувь</h1>'; this.style.color='white';">
    </div>