<?php
declare(strict_types=1);

const OPERATION_EXIT = 0;
const OPERATION_ADD = 1;
const OPERATION_DELETE = 2;
const OPERATION_PRINT = 3;

$operations = [
    OPERATION_EXIT => OPERATION_EXIT . '. Завершить программу.',
    OPERATION_ADD => OPERATION_ADD . '. Добавить товар в список покупок.',
    OPERATION_DELETE => OPERATION_DELETE . '. Удалить товар из списка покупок.',
    OPERATION_PRINT => OPERATION_PRINT . '. Отобразить список покупок.',
];

$items = [];

function getOperationNumber(array &$items, array $operations): int
{
    do {
        echo 'Выберите операцию для выполнения: ' . PHP_EOL;

        // Проверить, есть ли товары в списке? Если нет, то не отображать пункт про удаление товаров
        $displayOperations = $operations;
        if (empty($items)){
            unset($displayOperations[OPERATION_DELETE]);
        }

        echo implode(PHP_EOL, $displayOperations) . PHP_EOL . '> ';
        $operationInput = trim(fgets(STDIN));
        $operationNumber = (int)$operationInput;

        if (!array_key_exists($operationNumber, $operations)) {
            system('cls');
            echo '!!! Неизвестный номер операции, повторите попытку.' . PHP_EOL;
        } else {
            return $operationNumber;
        }

    } while (true);
}

function operationActionAdd(array &$items): void
{
    echo "Введение название товара для добавления в список: \n> ";
    $itemName = trim(fgets(STDIN));
    $items[] = $itemName;
}

function operationActionDelete(array &$items): void
{
    echo 'Текущий список покупок:' . PHP_EOL;
    echo 'Список покупок: ' . PHP_EOL;
    echo implode("\n", $items) . "\n";
    echo 'Введение название товара для удаления из списка:' . PHP_EOL . '> ';
    $itemName = trim(fgets(STDIN));

    // Проверить, есть ли товары в списке? Если нет, то сказать об этом и попросить ввести другую операцию
    if (!in_array($itemName, $items, true)) {
        echo 'Такого товара в списке нет.' . PHP_EOL;
        echo 'Выберите операцию для выполнения: ' . PHP_EOL;
        return;
    }

    $key = array_search($itemName, $items, true);
    while ($key !== false) {
        unset($items[$key]);
        $key = array_search($itemName, $items, true);
    }
}

function operationActionPrint(array &$items): void
{
    echo 'Ваш список покупок: ' . PHP_EOL;
    echo implode(PHP_EOL, $items) . PHP_EOL;
    echo 'Всего ' . count($items) . ' позиций. ' . PHP_EOL;
    echo 'Нажмите enter для продолжения';
    fgets(STDIN);
}

do {
    $operationNumber = getOperationNumber($items, $operations);

    echo 'Выбрана операция: '  . $operations[$operationNumber] . PHP_EOL;

    switch ($operationNumber) {
        case OPERATION_ADD:
            operationActionAdd($items);
            break;

        case OPERATION_DELETE:
            operationActionDelete($items);
            break;

        case OPERATION_PRINT:
            operationActionPrint($items);
            break;
    }

    echo "\n ----- \n";
} while ($operationNumber > 0);

echo 'Программа завершена' . PHP_EOL;