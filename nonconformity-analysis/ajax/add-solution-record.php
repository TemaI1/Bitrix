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
Loader ::includeModule('iblock');
Loader ::includeModule('intranet');

header('Content-Type: application/json; charset=utf-8');

try {
    $request = Application ::getInstance() -> getContext() -> getRequest();

    // Извлекаем данные из POST-запроса
    $recordId = (int)$request -> getPost('record_id');
    $comment = trim($request -> getPost('comment'));
    $solutionWorkerIds = $request -> getPost('WORKER_ID_SOLUTION');

    // Формируем массив полей для обновления hl main
    $fields = [];

    // Валидация ID записи
    if (!$recordId) {
        throw new \Exception('Не указан ID записи');
    }

    // Записываем в массив fields комментарий
    if (!$comment !== '') {
        $fields['UF_WORKER_COMMENT'] = $comment;
    } else {
        throw new \Exception('Не указан комментарий');
    }

    // Получаем массив данных HL-блока
    $hlBlockMain = getHlBlockByTableName("b_hlbd_nonconformity_analysis");
    $hlBlockApproval = getHlBlockByTableName("b_hlbd_approval_nonconformity_analysis");
    if (!$hlBlockMain || !$hlBlockApproval) {
        throw new \Exception('Один из Highload-блоков не найден в системе');
    }

    $hlBlockId = (int)$hlBlockMain['ID'];
    $hlBlockApprovalId = (int)$hlBlockApproval['ID'];

    $entityDataClass = HighloadBlockTable ::compileEntity($hlBlockMain) -> getDataClass();
    $approvalDataClass = HighloadBlockTable ::compileEntity($hlBlockApproval) -> getDataClass();

    // Проверяем, что запись существует
    $existingRecord = $entityDataClass ::getList([
        'select' => ['ID', 'UF_DOC_NAME', 'UF_CURRENT_ROUND', 'UF_APPROVERS_ID', 'UF_FILES', 'UF_CONTROLS'],
        'filter' => ['ID' => $recordId],
    ]) -> fetch();

    if (!$existingRecord) {
        throw new \Exception('Запись с ID ' . $recordId . ' не найдена');
    }

    // Получаем список значений поля (UF_STATUS) hl main
    $statusField = \CUserTypeEntity ::GetList(
        [],
        ['ENTITY_ID' => 'HLBLOCK_' . $hlBlockId, 'FIELD_NAME' => 'UF_STATUS']
    ) -> Fetch();

    $statusList = [];
    if ($statusField) {
        $enumIterator = \CUserFieldEnum ::GetList([], ['USER_FIELD_ID' => $statusField['ID']]);
        while ($enumItem = $enumIterator -> Fetch()) {
            $statusList[$enumItem['XML_ID'] ? : $enumItem['VALUE']] = $enumItem['ID'];
        }
    }

    // Получаем список значений поля (UF_STATUS) hl approval
    $statusFieldApproval = \CUserTypeEntity ::GetList(
        [],
        ['ENTITY_ID' => 'HLBLOCK_' . $hlBlockApprovalId, 'FIELD_NAME' => 'UF_STATUS']
    ) -> Fetch();

    $statusListApproval = [];
    if ($statusFieldApproval) {
        $enumIteratorApproval = \CUserFieldEnum ::GetList([], ['USER_FIELD_ID' => $statusFieldApproval['ID']]);
        while ($enumItemApproval = $enumIteratorApproval -> Fetch()) {
            $statusListApproval[$enumItemApproval['XML_ID'] ? : $enumItemApproval['VALUE']] = $enumItemApproval['ID'];
        }
    }

    // Загрузка файлов
    $uploadedFileIds = [];
    if (!empty($_FILES['WORKER_FILES']) && is_array($_FILES['WORKER_FILES']['name'])) {
        foreach ($_FILES['WORKER_FILES']['name'] as $key => $name) {
            if ($_FILES['WORKER_FILES']['error'][$key] === UPLOAD_ERR_OK) {
                // Формируем стандартный массив файла для Битрикса напрямую из $_FILES
                $arFile = [
                    'name'      => $_FILES['WORKER_FILES']['name'][$key],
                    'type'      => $_FILES['WORKER_FILES']['type'][$key],
                    'tmp_name'  => $_FILES['WORKER_FILES']['tmp_name'][$key],
                    'error'     => $_FILES['WORKER_FILES']['error'][$key],
                    'size'      => $_FILES['WORKER_FILES']['size'][$key],
                    'MODULE_ID' => 'highloadblock',
                ];

                // Сохраняем файл стандартными средствами Битрикса
                $fileId = \CFile ::SaveFile($arFile, "uf");
                if ($fileId > 0) {
                    $uploadedFileIds[] = (int)$fileId;
                }
            }
        }
    }

    // Формирование поля UF_FILES для update в hl main
    if (!empty($uploadedFileIds)) {
        $fields['UF_FILES'] = [];
        $index = 0;

        // Передаем новые файлы с индексами n0, n1, n2..., перезапишет поле UF_FILES в hl main
        foreach ($uploadedFileIds as $newFileId) {
            $fields['UF_FILES']['n' . $index] = \CFile ::MakeFileArray($newFileId);
            $index++;
        }
    }

    // Подготавливаем структуру файлов (путь к файлу) для add в hl approval
    $approvalFiles = false;
    if (!empty($uploadedFileIds)) {
        $approvalFiles = [];
        foreach ($uploadedFileIds as $fileId) {
            // Получаем путь к файлу
            $filePath = \CFile ::GetPath($fileId);
            if ($filePath) {
                $approvalFiles[] = $filePath;
            }
        }
    }

    // Определяем состав согласующих (с учетом круга)
    $workerIds = [];
    $isFirstRound = ((int)$existingRecord['UF_CURRENT_ROUND'] === 0);

    if ($isFirstRound) {
        // На самом первом старте (круг 0) берем из POST-запроса формы
        if (!empty($solutionWorkerIds)) {
            $workerIds = is_array($solutionWorkerIds) ? $solutionWorkerIds : [$solutionWorkerIds];
        } else {
            throw new \Exception('Не выбран согласующий');
        }
    } else {
        // Если круг 1, 2, 3 и т.д. — берем согласующих, сохраненных в основном HL-блоке
        if (!empty($existingRecord['UF_APPROVERS_ID'])) {
            $workerIds = is_array($existingRecord['UF_APPROVERS_ID'])
                ? $existingRecord['UF_APPROVERS_ID']
                : [$existingRecord['UF_APPROVERS_ID']];
        } else {
            throw new \Exception('Системная ошибка: отсутствует список согласующих для повторного круга');
        }
    }

    // Записываем в массив fields выбранных согласующих и их ФИО в hl main
    $fields['UF_APPROVERS_ID'] = $workerIds;

    // Собираем ФИО и почту для каждого сотрудника
    $approversNames = [];
    $approversEmails = [];
    foreach ($workerIds as $workerId) {
        $userData = getUserData((int)$workerId);
        if ($userData && !empty($userData['fio'])) {
            $approversNames[] = $userData['fio'];

            // Собираем уникальные и непустые email
            if (!empty($userData['email'])) {
                $approversEmails[] = trim($userData['email']);
            }
        }
    }

    // Записываем в массив fields ФИО в множественное строковое поле
    $fields['UF_APPROVERS'] = $approversNames;

    // Оставляем только уникальные адреса
    $approversEmails = array_unique($approversEmails);

    // Расчет нового этапа согласования
    $nextRound = $existingRecord['UF_CURRENT_ROUND'] + 1;

    // Рассчитываем дедлайн один раз для всех последующих блоков
    $deadlineDate = addWorkingDays(new Date(), 2);

    // Записываем в массив fields дату решения, статус, этап, комментарий (очищаем)
    $fields['UF_DATE_ACCEPTED'] = new Date();
    $fields['UF_STATUS'] = $statusList["APPROVAL"];
    $fields['UF_CURRENT_ROUND'] = $nextRound;
    $fields['UF_APPROVER_COMMENTS'] = [];

    // Включаем транзакцию, чтобы данные не рассинхронизировались
    $connection = \Bitrix\Main\Application ::getConnection();
    $connection -> startTransaction();

    // Обновляем главную запись hl main
    $res = $entityDataClass ::update($recordId, $fields);
    if (!$res -> isSuccess()) {
        throw new \Exception('Ошибка сохранения: ' . implode('; ', $res -> getErrorMessages()));
    }

    // Создание записей в hl approval
    foreach ($workerIds as $workerId) {
        $resApproval = $approvalDataClass ::add([
            'UF_NONCONFORMITY_ID'  => $recordId,
            'UF_ROUND'             => $nextRound,
            'UF_APPROVER'          => (int)$workerId,
            'UF_FILES_STRING'      => $approvalFiles,
            'UF_STATUS'            => $statusListApproval["WAITING"],
            'UF_WORKER_COMMENT'    => $comment,
            'UF_DEADLINE_APPROVER' => $deadlineDate,
            'UF_CONTROLS'          => $existingRecord['UF_CONTROLS'],
        ]);

        if (!$resApproval -> isSuccess()) {
            throw new \Exception(
                'Ошибка создания листа согласования: ' . implode('; ', $resApproval -> getErrorMessages())
            );
        }
    }

    // Если всё прошло успешно, фиксируем изменения в БД
    $connection -> commitTransaction();

    // Проверяем, зарегистрирован ли тип события в админке
    $eventTypeExists = (bool)\Bitrix\Main\Mail\Internal\EventTypeTable ::getRow([
        'filter' => ['=EVENT_NAME' => 'LETTER_LAN'],
        'select' => ['EVENT_NAME'],
    ]);

    // Отправка почтового события
    if ($eventTypeExists && !empty($approversEmails)) {
        // Объединяем все почтовые адреса согласующих через запятую
        $allApproversEmails = implode(', ', $approversEmails);

        // Переводим дату в строку
        $mailDeadline = is_object($deadlineDate) ? $deadlineDate -> toString() : (string)$deadlineDate;

        \Bitrix\Main\Mail\Event ::send([
            "EVENT_NAME" => "LETTER_LAN",
            "LID"        => "s1",
            "MESSAGE_ID" => 273, // ID шаблона
            "C_FIELDS"   => [
                "FOR_WHOM" => $allApproversEmails,
                "FOR_COPY" => "",
                "TOPIC"    => "Мониторинг выполнения анализа несоответствий № " . $recordId,
                "TEXT"     => "Информируем Вас о необходимости в срок до " . $mailDeadline . " принять решение по согласованию листа анализа несоответствий, по несоответствию «" . $existingRecord['UF_DOC_NAME'] . "»",
            ],
        ]);
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Успешно отправлено на ' . $nextRound . ' круг согласования',
        'id'      => $recordId,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e -> getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
