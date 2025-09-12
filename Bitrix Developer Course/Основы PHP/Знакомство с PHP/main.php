<?php

// Выведите на экран при помощи echo() название текущего файла и номер текущей строки
echo "Текущий файл: " . basename(__FILE__, '.php') . "\n" . "Текущая строка: " . __LINE__;

// Создайте многострочную переменную при помощи heredoc синтаксиса
$text = <<<TEXT
Это пример многострочной переменной.
Она может содержать несколько строк,
включая любые символы и переносы строк.
TEXT;

echo "\n" . $text;

// Задайте две простые строковые переменные и используйте их для построения конечной фразы
$a='Рыба';
$b='человек';

echo "\n" . "$a рыбою сыта, а $b человеком";

// Определение типа переменной
$variable = null;

if (is_bool($variable)) {
    $type = 'bool';
} else if(is_string($variable)) {
    $type = 'string';
} else if(is_float($variable)) {
    $type = 'float';
} else if(is_int($variable)) {
    $type = 'integer';
} else if(is_null($variable)) {
    $type = 'null';
} else if(is_array($variable) || is_object($variable) || is_resource($variable)) {
    $type = 'other';
} else {
    echo ('error');
}

echo "\n" . "type is $type";


// Определение типа переменной (switch)
$variable = 3.14;

switch (true) {
    case is_bool($variable):
        $type = 'bool';
        break;
    case is_string($variable):
        $type = 'string';
        break;
    case is_float($variable):
        $type = 'float';
        break;
    case is_int($variable):
        $type = 'integer';
        break;
    case is_null($variable):
        $type = 'null';
        break;
    case is_array($variable) || is_object($variable) || is_resource($variable):
        $type = 'other';
        break;
    default:
        echo 'error';
}

echo "\n" . "type is $type";