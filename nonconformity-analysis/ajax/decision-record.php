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
    global $USER;
    $request = Application ::getInstance() -> getContext() -> getRequest();

    // Извлекаем данные из POST-запроса
    $recordId = (int)$request -> getPost('record_id');
    $decisionComment = trim($request -> getPost('decision-comment'));
    $action = trim($request -> getPost('action'));

    // Получаем данные согласующего
    $approverData = getUserData((int)$USER -> GetID());
    if (!$approverData) {
        throw new \Exception('Согласующий не определен');
    }

    // Валидация ID записи
    if (!$recordId) {
        throw new \Exception('Не указан ID записи');
    }

    // Валидация действия кнопки
    if (!in_array($action, ['approve', 'reject'])) {
        throw new \Exception('Не указано или передано неверное действие (согласовать/отклонить)');
    }

    // Проверка обязательного комментария при отклонении
    if ($action === 'reject' && $decisionComment === '') {
        throw new \Exception('При отклонении, комментарий обязателен для заполнения');
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
        'select' => ['ID', 'UF_CURRENT_ROUND', 'UF_ROUNDS_DATES', 'UF_APPROVER_COMMENTS', 'UF_WORKER_ID', 'UF_SUPERVISOR_WORKER_ID', 'UF_DOC_NAME', 'UF_CONTROLS'],
        'filter' => ['ID' => $recordId],
    ]) -> fetch();

    if (!$existingRecord) {
        throw new \Exception('Запись с ID ' . $recordId . ' не найдена');
    }

    // Сохраняем текущий раунд (приводим к int, если пусто — будет 1 круг)
    $currentRound = (int)$existingRecord['UF_CURRENT_ROUND'] ? : 1;

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

    // Получаем запись из hl approval, связанную с hl main по id и согласющему
    $approvalRecords = [];
    $approvalIterator = $approvalDataClass ::getList([
        'select' => ['ID', 'UF_STATUS', 'UF_NONCONFORMITY_ID', 'UF_APPROVER'],
        'filter' => [
            '=UF_NONCONFORMITY_ID' => $recordId,
            '=UF_APPROVER'         => (int)$USER -> GetID(),
            '=UF_ROUND'            => $currentRound,
        ],
        'order'  => ['ID' => 'ASC'],
    ]);
    while ($approvalRow = $approvalIterator -> fetch()) {
        $approvalRecords[] = $approvalRow;
    }
    // Если нашли больше одной записи
    if (count($approvalRecords) > 1) {
        throw new \Exception('Системная ошибка: найдено несколько записей согласования для одного пользователя');
    }
    // Берём единственную запись
    $currentApproval = $approvalRecords[0] ?? null;
    // Проверяем, нашлась ли запись, и записываем её ID
    if (!$currentApproval) {
        throw new \Exception('Запись согласования не найдена');
    }
    // Проверка на текущий статус записи
    if ((int)$currentApproval['UF_STATUS'] !== (int)$statusListApproval["WAITING"]) {
        throw new \Exception('Решение о согласовании было принято ранее, ожидание других согласующих');
    }
    $recordIdApproval = (int)$currentApproval['ID'];

    // Формируем массив полей для обновления hl блоков
    $fieldsMain = [];
    $fieldsApproval = [];

    // Записываем в массив $fieldsApproval и $fieldsMain комментарий (если он есть)
    if ($decisionComment !== '') {
        $fieldsApproval['UF_APPROVER_COMMENT'] = $decisionComment;

        $formattedComment = $approverData['fio'] . ": " . $decisionComment;
        $arDecisionComments = is_array($existingRecord['UF_APPROVER_COMMENTS']) ? $existingRecord['UF_APPROVER_COMMENTS'] : [];
        $arDecisionComments[] = $formattedComment;
        $fieldsMain['UF_APPROVER_COMMENTS'] = $arDecisionComments;
    }

    // Записываем в массив $fieldsApproval дату решения
    $fieldsApproval['UF_DECISION_DATE'] = new Date();

    // Динамически выставляем ID статуса из списка на основе нажатой кнопки
    if ($action === 'approve') {
        if (!isset($statusListApproval["APPROVED"])) {
            throw new \Exception('В системе не найден XML_ID статус APPROVED для блока согласований');
        }
        $fieldsApproval['UF_STATUS'] = $statusListApproval["APPROVED"];
    } elseif ($action === 'reject') {
        if (!isset($statusListApproval["REJECTED"])) {
            throw new \Exception('В системе не найден XML_ID статус REJECTED для блока согласований');
        }
        $fieldsApproval['UF_STATUS'] = $statusListApproval["REJECTED"];
    }

    // Выполняем обновление записи hl approval
    $res = $approvalDataClass ::update($recordIdApproval, $fieldsApproval);
    if (!$res -> isSuccess()) {
        throw new \Exception('Ошибка сохранения: ' . implode('; ', $res -> getErrorMessages()));
    }

    // Логика ожидания всех согласующих
    // Считаем, сколько осталось записей в статусе WAITING для этого $recordId
    $remainingWaitingCount = $approvalDataClass ::getCount([
        '=UF_NONCONFORMITY_ID' => $recordId,
        '=UF_STATUS'           => $statusListApproval["WAITING"],
        '=UF_ROUND'            => $currentRound,
    ]);

    // Документ финализируется когда счетчик оставшихся равен 0
    $isFinalDecision = ($remainingWaitingCount === 0);

    if ($isFinalDecision) {
        // Проверяем, есть ли среди записей документа REJECTED
        $hasAnyReject = $approvalDataClass ::getList([
            'select' => ['ID'],
            'filter' => [
                '=UF_NONCONFORMITY_ID' => $recordId,
                '=UF_STATUS'           => $statusListApproval["REJECTED"],
                '=UF_ROUND'            => $currentRound,
            ],
            'limit'  => 1,
        ]) -> fetch();

        // Итоговый статус для hl main
        if ($hasAnyReject) {
            if (!isset($statusList["REVISION"])) {
                throw new \Exception('В системе не найден XML_ID статус');
            }
            $fieldsMain['UF_STATUS'] = $statusList["REVISION"];
            $fieldsMain['UF_DEADLINE'] = addWorkingDays(new Date(), 2);
            $fieldsMain['UF_DATE_ACCEPTED'] = false;

            // Записываем даты завершения кругов
            $currentDates = is_array($existingRecord['UF_ROUNDS_DATES']) ? $existingRecord['UF_ROUNDS_DATES'] : [];
            $currentDates[] = (new Date()) -> toString();
            $fieldsMain['UF_ROUNDS_DATES'] = $currentDates;

            $mainMessage = 'Все сотрудники приняли решение, документ отправлен на доработку';

            // Проверяем, зарегистрирован ли тип события в админке
            $eventTypeExists = (bool)\Bitrix\Main\Mail\Internal\EventTypeTable::getRow([
                'filter' => ['=EVENT_NAME' => 'LETTER_LAN'],
                'select' => ['EVENT_NAME']
            ]);

            // Отправка почтового события
            if ($eventTypeExists) {

                // Получаем данные работника и руководителя
                $workerData = getUserData($existingRecord['UF_WORKER_ID']);
                if (!$workerData) {
                    throw new \Exception('Работник не найден');
                }
                $supervisorData = getUserData($existingRecord['UF_SUPERVISOR_WORKER_ID']);
                if (!$supervisorData) {
                    throw new \Exception('Руководитель не найден');
                }

                // Проверяем, что у сотрудника заполнен email
                $workerEmail = !empty($workerData['email']) ? $workerData['email'] : "";
                $supervisorEmail = !empty($supervisorData['email']) ? $supervisorData['email'] : "";

                // Переводим дату в строку
                $mailDeadline = is_object($fieldsMain['UF_DEADLINE']) ? $fieldsMain['UF_DEADLINE']->toString() : (string)$fieldsMain['UF_DEADLINE'];

                \Bitrix\Main\Mail\Event::send([
                    "EVENT_NAME" => "LETTER_LAN",
                    "LID"        => "s1",
                    "MESSAGE_ID" => 273, // ID шаблона
                    "C_FIELDS"   => [
                        "FOR_WHOM" => $workerEmail,
                        "FOR_COPY" => $supervisorEmail . ", " . $existingRecord['UF_CONTROLS'],
                        "TOPIC"    => "Мониторинг выполнения анализа несоответствий № " . $recordId,
                        "TEXT"     => "Информируем Вас о необходимости повторно оформить и разместить на согласование лист анализа несоответствий",
                    ]
                ]);
            }
        } else {
            if (!isset($statusList["APPROVED"])) {
                throw new \Exception('В системе не найден XML_ID статус');
            }
            $fieldsMain['UF_STATUS'] = $statusList["APPROVED"];
            $fieldsMain['UF_FINAL_APPROVAL_DATE'] = new Date();

            // Записываем даты завершения кругов
            $currentDates = is_array($existingRecord['UF_ROUNDS_DATES']) ? $existingRecord['UF_ROUNDS_DATES'] : [];
            $currentDates[] = (new Date()) -> toString();
            $fieldsMain['UF_ROUNDS_DATES'] = $currentDates;

            $mainMessage = 'Все сотрудники приняли решение, документ согласован';
        }

    } else {
        // Если кто-то еще не проголосовал
        $mainMessage = 'Ваше решение успешно сохранено, ожидается согласование остальных согласующих';
    }

    // Обновляем hl main
    if (!empty($fieldsMain)) {
        $resMain = $entityDataClass::update($recordId, $fieldsMain);
        if (!$resMain->isSuccess()) {
            throw new \Exception('Ошибка обновления записи main: ' . implode('; ', $resMain->getErrorMessages()));
        }
    }

    echo json_encode([
        'status'         => 'success',
        'message'        => $mainMessage,
        'id'             => $recordIdApproval,
        'updated_fields' => array_keys($fieldsApproval),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e -> getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
