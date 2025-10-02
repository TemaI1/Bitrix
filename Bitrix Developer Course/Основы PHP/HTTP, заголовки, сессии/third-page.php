<?php
session_start();

// Инициализация переменной
if (!isset($_SESSION['third-page-count'])) {
    $_SESSION['third-page-count'] = 0;
}

// Увеличиваем счетчик при каждом открытии
$_SESSION['third-page-count']++;

// Проверяем кратность счетчика 3
if ($_SESSION['third-page-count'] % 3 == 0) {
    // Перенаправляем на четвертую страницу
    header('Location: fourth-page.php');
    exit;
}
?>
<p>Вы открыли эту страницу <?php echo $_SESSION['third-page-count']; ?> раз(а).</p>