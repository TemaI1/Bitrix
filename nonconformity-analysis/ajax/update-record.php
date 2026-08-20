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

Loader ::includeModule('iblock');
Loader ::includeModule('intranet');
Loader ::includeModule('highloadblock');

header('Content-Type: application/json; charset=utf-8');

try {
    $request = Application ::getInstance() -> getContext() -> getRequest();

    // Извлекаем данные из POST-запроса
    $recordId = (int)$request -> getPost('record_id');
    $docName = trim($request -> getPost('doc-name'));
    $nonconformity = trim($request -> getPost('name-nonconformity'));
    $workerId = (int)$request -> getPost('WORKER_ID_EDIT');
    $supervisorId = (int)$request -> getPost('SUPERVISOR_ID_EDIT');
    $dateReg = trim($request -> getPost('date-reg'));

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

    // Формируем массив полей для обновления (только те, что пришли и не пустые)
    $fields = [];

    if ($docName !== '') {
        $fields['UF_DOC_NAME'] = $docName;
    }

    if ($dateReg !== '') {
        $dateObj = \DateTime ::createFromFormat('Y-m-d', $dateReg);
        if ($dateObj) {
            $fields['UF_DATE_REG'] = $dateObj -> format('d.m.Y');
        } else {
            throw new \Exception('Неверный формат даты: ' . $dateReg);
        }
    }

    if ($nonconformity !== '') {
        $fields['UF_NAME_NONCONFORMITY'] = $nonconformity;
    }

    // Работник
    if ($workerId > 0) {
        $workerData = getUserData($workerId);
        if (!$workerData) {
            throw new \Exception('Работник с ID ' . $workerId . ' не найден');
        }
        $fields['UF_WORKER'] = $workerData['fio'];
        $fields['UF_WORKER_ID'] = $workerData['id'];
        $fields['UF_DEPARTMENT_WORKER'] = $workerData['dept']['name'] ?? '';
    }

    // Руководитель
    if ($supervisorId > 0) {
        $supervisorData = getUserData($supervisorId);
        if (!$supervisorData) {
            throw new \Exception('Руководитель с ID ' . $supervisorId . ' не найден');
        }
        $fields['UF_SUPERVISOR_WORKER'] = $supervisorData['fio'];
        $fields['UF_SUPERVISOR_WORKER_ID'] = $supervisorData['id'];
        $fields['UF_SUPERVISOR_DEPARTMENT_WORKER'] = $supervisorData['dept']['name'] ?? '';
    }

    // Если ничего не изменилось — прерываем выполнение без запроса к БД
    if (empty($fields)) {
        echo json_encode([
            'status'  => 'success',
            'message' => 'Нет изменений для сохранения',
            'id'      => $recordId,
        ], JSON_UNESCAPED_UNICODE);
        require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
        exit;
    }

    // Выполняем обновление записи
    $res = $entityDataClass ::update($recordId, $fields);
    if (!$res -> isSuccess()) {
        throw new \Exception('Ошибка обновления: ' . implode('; ', $res -> getErrorMessages()));
    }

    echo json_encode([
        'status'         => 'success',
        'message'        => 'Запись успешно обновлена',
        'id'             => $recordId,
        'updated_fields' => array_keys($fields),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e -> getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
