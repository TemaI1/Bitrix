<?php
$uploadDir = __DIR__ . '/upload';

// Создаем папку, если ее нет
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Проверка наличия поля 'file_name' и его заполненности
if (empty(trim($_POST['file_name']))) {
    header('Location: index.html');
    exit;
}

// Проверка, был ли передан файл
if (!isset($_FILES['content']) || $_FILES['content']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.html');
    exit;
}

// Получаем значение поля 'file_name' и файл
$fileName = trim($_POST['file_name']);
$file = $_FILES['content'];

// Получаем расширение файла
$originalName = basename($file['name']);
$extension = pathinfo($originalName, PATHINFO_EXTENSION);

// Формируем имя файла
$fullFileName = $fileName . '.' . $extension;
$destinationPath = $uploadDir . '/' . $fullFileName;

// Перемещаем файл
move_uploaded_file($file['tmp_name'], $destinationPath);

// Получаем размер файла
$fileSize = filesize($destinationPath);

// Выводим полный путь и размер файла
echo "Файл успешно сохранен: " . htmlspecialchars($destinationPath) . "<br>";
echo "Размер файла: " . $fileSize . " байт";
?>