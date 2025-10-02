<?php
if (isset($_GET['text'])) {
    $text = $_GET['text'];
    $filename = "file.txt";

    // Устанавливаем заголовки
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($text));

    echo $text;
    exit;
} else {
    echo "Параметр 'text' не передан.";
}
?>