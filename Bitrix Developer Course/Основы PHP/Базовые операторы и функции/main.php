<?php

echo "Введите ваше имя: ";
$firstName = trim(fgets(STDIN));

echo "Введите вашу фамилию: ";
$lastName = trim(fgets(STDIN));

echo "Введите ваше отчество: ";
$middleName = trim(fgets(STDIN));

// Полное имя
$fullname = ucwords(mb_strtolower($lastName . ' ' . $firstName . ' ' . $middleName));

$fullnameWords = explode(' ', $fullname);

// Аббревиатура
$fio = $fullnameWords[0][0] . $fullnameWords[1][0] . $fullnameWords[2][0];

// Фамилия и инициалы
$surnameAndInitials = $fullnameWords[0] . " " . $fullnameWords[1][0] . "." . $fullnameWords[2][0] . ". ";

echo "Полное имя: " . $fullname . "\n";

echo "Аббревиатура: " . $fio . "\n";

echo "Фамилия и инициалы: " . $surnameAndInitials . "\n";