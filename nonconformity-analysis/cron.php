<?php

@set_time_limit(0);
if (!defined('NOT_CHECK_PERMISSIONS')) {
    define('NOT_CHECK_PERMISSIONS', true);
}
if (!defined('NO_AGENT_CHECK')) {
    define('NO_AGENT_CHECK', true);
}
if (!defined('BX_CRONTAB')) {
    define("BX_CRONTAB", true);
}
if (!defined('ADMIN_SECTION')) {
    define("ADMIN_SECTION", true);
}
if (!ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
    @date_default_timezone_set("Europe/Moscow");
}
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../../..');
if (!array_key_exists('REQUEST_URI', $_SERVER)) {
    $_SERVER["REQUEST_URI"] = substr(__FILE__, strlen($_SERVER["DOCUMENT_ROOT"]));
}
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\DateTime;

if (!Loader ::includeModule('highloadblock')) {
    die('Модуль Highload-блоков не установлен');
}


// Получает массив данных HL-блока по имени его таблицы в БД
function getHlBlockByTableName(string $tableName)
{
    return HighloadBlockTable ::getList([
        'filter' => ['=TABLE_NAME' => $tableName],
    ]) -> fetch();
}

// Получает ID значений списочного свойства по его XML_ID
function getStatusIdsByXmlIds(int $hlBlockId, array $xmlIds): array
{
    $statusList = [];
    $statusField = \CUserTypeEntity ::GetList(
        [],
        ['ENTITY_ID' => 'HLBLOCK_' . $hlBlockId, 'FIELD_NAME' => 'UF_STATUS']
    ) -> Fetch();

    if ($statusField) {
        $enumIterator = \CUserFieldEnum ::GetList([], ['USER_FIELD_ID' => $statusField['ID']]);
        while ($enumItem = $enumIterator -> Fetch()) {
            $key = !empty($enumItem['XML_ID']) ? $enumItem['XML_ID'] : $enumItem['VALUE'];
            if (in_array($key, $xmlIds)) {
                $statusList[] = $enumItem['ID'];
            }
        }
    }
    return $statusList;
}

// Получает почту пользователя
function getUserEmail($userId): string
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return '';
    }

    $user = \CUser ::GetList(
        'ID',
        'ASC',
        ['ID' => $userId],
        ['FIELDS' => ['EMAIL']]
    ) -> Fetch();

    return $user['EMAIL'] ?? '';
}

// Получаем данные HL-блоков
$hlBlockMain = getHlBlockByTableName("b_hlbd_nonconformity_analysis");
$hlBlockApproval = getHlBlockByTableName("b_hlbd_approval_nonconformity_analysis");

$currentDate = new DateTime();
$output = [
    'main'     => [],
    'approval' => [],
];

// Обрабатываем hl main
if ($hlBlockMain) {
    $hlBlockId = (int)$hlBlockMain['ID'];
    $mainStatuses = getStatusIdsByXmlIds($hlBlockId, ["WAITING_WORKER", "REVISION"]);

    if (!empty($mainStatuses)) {
        $entityDataClass = HighloadBlockTable ::compileEntity($hlBlockMain) -> getDataClass();
        $mainResult = $entityDataClass ::getList([
            'select' => ['ID', 'UF_WORKER_ID', 'UF_SUPERVISOR_WORKER_ID', 'UF_CONTROLS'],
            'filter' => [
                '=UF_STATUS'   => $mainStatuses,
                '<UF_DEADLINE' => $currentDate,
            ],
        ]);

        while ($row = $mainResult -> fetch()) {
            $workerEmail = getUserEmail($row['UF_WORKER_ID']);

            // Если у сотрудника есть почта
            if (!empty($workerEmail)) {
                $output['main'][$row['ID']] = [
                    'WORKER_ID_EMAIL'            => $workerEmail,
                    'SUPERVISOR_WORKER_ID_EMAIL' => getUserEmail($row['UF_SUPERVISOR_WORKER_ID']),
                    'UF_CONTROLS'                => $row['UF_CONTROLS'],
                ];
            }
        }
    }
}

// Обрабатываем hl approval
if ($hlBlockApproval) {
    $hlBlockApprovalId = (int)$hlBlockApproval['ID'];
    $approvalStatuses = getStatusIdsByXmlIds($hlBlockApprovalId, ["WAITING"]);

    if (!empty($approvalStatuses)) {
        $approvalDataClass = HighloadBlockTable ::compileEntity($hlBlockApproval) -> getDataClass();
        $approvalResult = $approvalDataClass ::getList([
            'select' => ['ID', 'UF_APPROVER', 'UF_NONCONFORMITY_ID', 'UF_CONTROLS'],
            'filter' => [
                '=UF_STATUS'            => $approvalStatuses,
                '<UF_DEADLINE_APPROVER' => $currentDate,
            ],
        ]);

        while ($row = $approvalResult -> fetch()) {
            $approverEmail = getUserEmail($row['UF_APPROVER']);

            // Если у согласующего есть почта
            if (!empty($approverEmail)) {
                $output['approval'][$row['ID']] = [
                    'APPROVER_EMAIL'      => $approverEmail,
                    'UF_NONCONFORMITY_ID' => $row['UF_NONCONFORMITY_ID'],
                    'UF_CONTROLS'         => $row['UF_CONTROLS'],
                ];
            }
        }
    }
}

// Проверяем, зарегистрирован ли тип события в админке
$eventTypeExists = (bool)\Bitrix\Main\Mail\Internal\EventTypeTable ::getRow([
    'filter' => ['=EVENT_NAME' => 'LETTER_LAN'],
    'select' => ['EVENT_NAME'],
]);

// Результирующий массив для уведомлений
if ($eventTypeExists && (!empty($output['main']) || !empty($output['approval']))) {
    // Уведомления по main (сотрудник, руководитель сотрудника)
    if (!empty($output['main'])) {
        foreach ($output['main'] as $elementId => $emails) {
            \Bitrix\Main\Mail\Event ::send([
                "EVENT_NAME" => "LETTER_LAN",
                "LID"        => "s1",
                "MESSAGE_ID" => 273, // ID шаблона
                "C_FIELDS"   => [
                    "FOR_WHOM" => $emails['WORKER_ID_EMAIL'],
                    "FOR_COPY" => $emails['SUPERVISOR_WORKER_ID_EMAIL'] . ", " . $emails['UF_CONTROLS'],
                    "TOPIC"    => "Мониторинг выполнения анализа несоответствий № " . $elementId,
                    "TEXT"     => "Информируем Вас о том, что в срок не выполнен анализ несоответствий, который входит в зону вашей ответственности, по несоответствию № " . $elementId,
                ],
            ]);
        }
    }

    // Уведомления по approval (согласующие)
    if (!empty($output['approval'])) {
        foreach ($output['approval'] as $elementId => $emails) {
            \Bitrix\Main\Mail\Event ::send([
                "EVENT_NAME" => "LETTER_LAN",
                "LID"        => "s1",
                "MESSAGE_ID" => 273, // ID шаблона
                "C_FIELDS"   => [
                    "FOR_WHOM" => $emails['APPROVER_EMAIL'],
                    "FOR_COPY" => $emails['UF_CONTROLS'],
                    "TOPIC"    => "Мониторинг выполнения анализа несоответствий № " . $emails['UF_NONCONFORMITY_ID'],
                    "TEXT"     => "Информируем Вас о том, что в срок не принято решение по согласованию листа анализа несоответствий, по несоответствию № " . $emails['UF_NONCONFORMITY_ID'],
                ],
            ]);
        }
    }
}
