<?php

$input1 = trim(fgets(STDIN));
$input2 = trim(fgets(STDIN));

if (!is_numeric($input1) || !is_numeric($input2)) {
    fwrite(STDERR, "Введите, пожалуйста, число\n");
} else if($input1 == 0 || $input2 == 0) {
    fwrite(STDERR, "Делить на 0 нельзя\n");
}else {
    fwrite(STDOUT, "Результат деления: " . ($input1 / $input2));
}