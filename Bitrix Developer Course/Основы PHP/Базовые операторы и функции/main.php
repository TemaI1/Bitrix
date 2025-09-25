<?php

echo "Введите ваше имя: ";
$firstName = trim(fgets(STDIN));

echo "Введите вашу фамилию: ";
$lastName = trim(fgets(STDIN));

echo "Введите ваше отчество: ";
$middleName = trim(fgets(STDIN));

// Функция для форматирования имени с первой заглавной буквой и остальными строчными
function formatName($name) {
    $name = mb_strtolower($name);
    return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
}

// Форматируем каждое слово
$lastNameFormatted = formatName($lastName);
$firstNameFormatted = formatName($firstName);
$middleNameFormatted = formatName($middleName);

// Полное имя
$fullname = $lastNameFormatted . ' ' . $firstNameFormatted . ' ' . $middleNameFormatted;

// Инициалы с заглавной буквы
$fio = mb_strtoupper(mb_substr($lastName, 0, 1)) .
       mb_strtoupper(mb_substr($firstName, 0, 1)) .
       mb_strtoupper(mb_substr($middleName, 0, 1));

// Фамилия и инициалы
$surnameAndInitials = $lastNameFormatted . " " . 
                      mb_strtoupper(mb_substr($firstName, 0, 1)) . "." . 
                      mb_strtoupper(mb_substr($middleName, 0, 1)) . ".";

echo "Полное имя: " . $fullname . "\n";

echo "Аббревиатура: " . $fio . "\n";

echo "Фамилия и инициалы: " . $surnameAndInitials . "\n";