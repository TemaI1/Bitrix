<?php

// Отключаем сбор статистики для ускорения AJAX-запросов
define('STOP_STATISTICS', true);
define('NO_AGENT_STATISTIC', 'Y');
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require_once __DIR__ . "/../includes/functions.php";

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\Date;

Loader ::includeModule('highloadblock');

header('Content-Type: application/json; charset=utf-8');

try {
    $request = Application ::getInstance() -> getContext() -> getRequest();
    $recordId = (int)$request -> getPost('record_id');

    if (!$recordId) {
        throw new \Exception('Не указан ID записи');
    }

    $hlBlockMain = getHlBlockByTableName("b_hlbd_nonconformity_analysis");
    if (!$hlBlockMain) {
        throw new \Exception('Highload-блок не найден');
    }

    $entityDataClass = HighloadBlockTable ::compileEntity($hlBlockMain) -> getDataClass();

    // Проверяем, что запись существует
    $existingRecord = $entityDataClass ::getList([
        'select' => ['ID'],
        'filter' => ['ID' => $recordId],
    ]) -> fetch();

    if (!$existingRecord) {
        throw new \Exception('Запись с ID ' . $recordId . ' не найдена');
    }

    $res = $entityDataClass ::delete($recordId);
    if (!$res -> isSuccess()) {
        throw new \Exception('Ошибка удаления: ' . implode('; ', $res -> getErrorMessages()));
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Запись успешно удалена',
        'id'      => $recordId,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e -> getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
