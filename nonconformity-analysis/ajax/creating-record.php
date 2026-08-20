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

// Подключаем необходимые модули Bitrix
Loader ::includeModule('iblock');
Loader ::includeModule('intranet');
Loader ::includeModule('highloadblock');

header('Content-Type: application/json; charset=utf-8');

try {
    global $USER;
    $request = Application ::getInstance() -> getContext() -> getRequest();

    // Извлекаем данные из POST-запроса
    $docName = trim($request -> getPost('doc-name'));
    $nonconformity = trim($request -> getPost('name-nonconformity'));
    $workerId = (int)$request -> getPost('WORKER_ID');
    $supervisorId = (int)$request -> getPost('SUPERVISOR_ID');
    $controlIds = $request -> getPost('CONTROL_ID');

    // Валидация входных данных
    if (!$docName) {
        throw new \Exception('Не указано наименование документа');
    }
    if (!$nonconformity) {
        throw new \Exception('Не указано несоответствие');
    }
    if (!$workerId) {
        throw new \Exception('Не выбран работник');
    }
    if (!$supervisorId) {
        throw new \Exception('Не выбран руководитель');
    }

    // Получаем данные работника и руководителя
    $workerData = getUserData($workerId);
    if (!$workerData) {
        throw new \Exception('Работник не найден');
    }
    $supervisorData = getUserData($supervisorId);
    if (!$supervisorData) {
        throw new \Exception('Руководитель не найден');
    }

    // Определяем группу контроля и приводим к массиву
    $workerControlIds = [];
    if (!empty($controlIds)) {
        $workerControlIds = is_array($controlIds) ? $controlIds : [$controlIds];
    }
    // Собираем почту каждого сотрудника группы контроля
    $controlsEmails = [];
    foreach ($workerControlIds as $id) {
        // Используем быструю функцию getUserEmail
        $email = getUserEmail($id);
        if (!empty($email)) {
            $controlsEmails[] = $email;
        }
    }
    // Превращаем массив почт в строку через запятую для отправки
    $controlsEmailsString = implode(', ', $controlsEmails);

    // Получаем массив данных HL-блока
    $hlBlockMain = getHlBlockByTableName("b_hlbd_nonconformity_analysis");

    if (!$hlBlockMain) {
        throw new \Exception('Highload-блок не найден');
    }

    $hlBlockId = (int)$hlBlockMain['ID'];

    // Компилируем сущность для работы с ORM
    $entityDataClass = HighloadBlockTable ::compileEntity($hlBlockMain) -> getDataClass();

    // Получаем список значений поля (UF_STATUS)
    $statusField = \CUserTypeEntity ::GetList(
        [],
        ['ENTITY_ID' => 'HLBLOCK_' . $hlBlockId, 'FIELD_NAME' => 'UF_STATUS']
    ) -> Fetch();

    $statusList = [];
    if ($statusField) {
        $enumIterator = \CUserFieldEnum ::GetList([], ['USER_FIELD_ID' => $statusField['ID']]);
        while ($enumItem = $enumIterator -> Fetch()) {
            $key = $enumItem['XML_ID'] ? : $enumItem['VALUE'];
            $statusList[$key] = $enumItem['ID'];
        }
    }

    // Формируем массив полей для записи
    $fields = [
        'UF_DOC_NAME'                     => $docName,
        'UF_NAME_NONCONFORMITY'           => $nonconformity,
        'UF_WORKER'                       => $workerData['fio'],
        'UF_WORKER_ID'                    => $workerData['id'],
        'UF_DATE_REG'                     => new Date(),
        'UF_DEPARTMENT_WORKER'            => $workerData['dept']['name'] ?? '',
        'UF_SUPERVISOR_WORKER'            => $supervisorData['fio'],
        'UF_SUPERVISOR_WORKER_ID'         => $supervisorData['id'],
        'UF_SUPERVISOR_DEPARTMENT_WORKER' => $supervisorData['dept']['name'] ?? '',
        'UF_DEADLINE'                     => addWorkingDays(new Date(), 4),
        'UF_STATUS'                       => $statusList["WAITING_WORKER"],
        'UF_CURRENT_ROUND'                => 0,
        'UF_INITIATOR'                    => (int)$USER -> GetID(),
        'UF_CONTROLS'                     => $controlsEmailsString,
    ];

    // Создаём запись в HL-блоке
    $res = $entityDataClass ::add($fields);
    if (!$res -> isSuccess()) {
        throw new \Exception('Ошибка сохранения: ' . implode('; ', $res -> getErrorMessages()));
    }

    // Получаем ID запись в HL-блоке
    $newRecordId = (int)$res -> getId();

    // Проверяем, зарегистрирован ли тип события в админке
    $eventTypeExists = (bool)\Bitrix\Main\Mail\Internal\EventTypeTable ::getRow([
        'filter' => ['=EVENT_NAME' => 'LETTER_LAN'],
        'select' => ['EVENT_NAME'],
    ]);

    // Отправка почтового события
    if ($eventTypeExists) {
        // Проверяем, что у сотрудника заполнен email
        $workerEmail = !empty($workerData['email']) ? $workerData['email'] : "";
        $supervisorEmail = !empty($supervisorData['email']) ? $supervisorData['email'] : "";

        // Переводим дату в строку
        $mailDeadline = is_object($fields['UF_DEADLINE']) ? $fields['UF_DEADLINE'] -> toString(
        ) : (string)$fields['UF_DEADLINE'];

        \Bitrix\Main\Mail\Event ::send([
            "EVENT_NAME" => "LETTER_LAN",
            "LID"        => "s1",
            "MESSAGE_ID" => 273, // ID шаблона
            "C_FIELDS"   => [
                "FOR_WHOM" => $workerEmail,
                "FOR_COPY" => $supervisorEmail,
                "TOPIC"    => "Мониторинг выполнения анализа несоответствий № " . $newRecordId,
                "TEXT"     => "Информируем Вас о необходимости в срок до " . $mailDeadline . " оформить и разместить на согласование лист анализа несоответствий, по несоответствию «" . $docName . "»",
            ],
        ]);
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Запись успешно создана',
        'id'      => $res -> getId(),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()], JSON_UNESCAPED_UNICODE);
}

// Подключаем эпилог Bitrix (добавлена точка с запятой в конце)
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
