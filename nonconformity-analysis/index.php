<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require_once __DIR__ . "/includes/functions.php";

use Bitrix\Main\Grid\Options;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\Date;

$APPLICATION -> SetTitle("Мониторинг проведения анализа несоответствий");
$APPLICATION -> SetAdditionalCSS('/nonconformity-analysis/assets/style.css');

// Получаем ID текущего авторизованного пользователя
global $USER;
$currentUserId = (int)$USER -> GetID();

// ============================================================================
// КОНСТАНТЫ И НАСТРОЙКИ
// ============================================================================
$hlBlockMain = getHlBlockByTableName("b_hlbd_nonconformity_analysis");
$hlBlockApproval = getHlBlockByTableName("b_hlbd_approval_nonconformity_analysis");
$GRID_ID = 'my_grid';
$FILTER_ID = 'my_grid';

if (!$hlBlockMain) {
    ShowError("Highload-блок не найден");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

$hlBlockId = (int)$hlBlockMain['ID'];
$entityDataClass = HighloadBlockTable ::compileEntity($hlBlockMain) -> getDataClass();

// Получаем список значений поля (UF_STATUS) для отображения в гриде [ID => VALUE]
$statusField = \CUserTypeEntity ::GetList(
    [],
    ['ENTITY_ID' => 'HLBLOCK_' . $hlBlockId, 'FIELD_NAME' => 'UF_STATUS']
) -> Fetch();

$statusList = [];
$statusXmlList = [];
if ($statusField) {
    $enumIterator = \CUserFieldEnum ::GetList([], ['USER_FIELD_ID' => $statusField['ID']]);
    while ($enumItem = $enumIterator -> Fetch()) {
        $statusList[$enumItem['ID']] = $enumItem['VALUE'];
        $statusXmlList[$enumItem['ID']] = $enumItem['XML_ID'];
    }
}

// ============================================================================
// КОНФИГУРАЦИЯ КОЛОНОК ГРИДА
// ============================================================================
$gridColumns = [
    ['id' => 'ID', 'name' => '№', 'sort' => 'ID', 'default' => true],
    ['id' => 'UF_DOC_NAME', 'name' => 'Наименование документа н/с', 'default' => true],
    ['id' => 'UF_DATE_REG', 'name' => 'Дата регистрации', 'default' => true],
    ['id' => 'UF_NAME_NONCONFORMITY', 'name' => 'Несоответствие', 'default' => true],
    ['id' => 'UF_WORKER', 'name' => 'Работник допустивший н/с', 'default' => true],
    ['id' => 'UF_DEPARTMENT_WORKER', 'name' => 'Подразделение работника', 'default' => true],
    ['id' => 'UF_SUPERVISOR_WORKER', 'name' => 'Руководитель работника', 'default' => true],
    ['id' => 'UF_SUPERVISOR_DEPARTMENT_WORKER', 'name' => 'Подразделение руководителя', 'default' => true],
    ['id' => 'UF_DEADLINE', 'name' => 'Срок устранения', 'default' => true],
    ['id' => 'UF_APPROVERS', 'name' => 'Согласующие', 'default' => true],
    ['id' => 'UF_DATE_ACCEPTED', 'name' => 'Дата решения работником', 'default' => true],
    ['id' => 'UF_WORKER_COMMENT', 'name' => 'Комментарий работника', 'default' => true],
    ['id' => 'UF_FILES', 'name' => 'Приложенные документы', 'default' => true],
    ['id' => 'UF_ROUNDS_DATES', 'name' => 'Завершение кругов согласования', 'default' => true],
    ['id' => 'UF_APPROVER_COMMENTS', 'name' => 'Комментарии согласующих', 'default' => true],
    ['id' => 'UF_FINAL_APPROVAL_DATE', 'name' => 'Итоговая дата согласования', 'default' => true],
    ['id' => 'UF_STATUS', 'name' => 'Статус', 'default' => true],
];

// ============================================================================
// НАВИГАЦИЯ И СОРТИРОВКА
// ============================================================================
$gridOptions = new Options($GRID_ID);
$navParams = $gridOptions -> GetNavParams();

$nav = new PageNavigation($GRID_ID);
$nav -> allowAllRecords(true)
    -> setPageSize($navParams['nPageSize'])
    -> initFromUri();

$sortArray = $gridOptions -> getSorting();
$order = $sortArray['sort'] ? : ['ID' => 'desc'];

// ============================================================================
// ОБРАБОТКА ФИЛЬТРА
// ============================================================================
$filterOption = new FilterOptions($FILTER_ID);
$rawFilter = $filterOption -> getFilter([]);
$ormFilter = [];

$find = trim($rawFilter['FIND'] ?? '');
if ($find !== '') {
    $searchGroup = [];
    $searchFields = [
        'UF_DOC_NAME',
        'UF_NAME_NONCONFORMITY',
        'UF_WORKER',
        'UF_DEPARTMENT_WORKER',
        'UF_SUPERVISOR_WORKER',
        'UF_SUPERVISOR_DEPARTMENT_WORKER',
    ];

    foreach ($searchFields as $field) {
        $searchGroup[] = ['%' . $field => $find];
    }

    if (!empty($searchGroup)) {
        $ormFilter[] = array_merge(['LOGIC' => 'OR'], $searchGroup);
    }
}

$skipKeys = ['PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND', 'LOGIC'];
$allowedFields = [
    'ID',
    'UF_DOC_NAME',
    'UF_NAME_NONCONFORMITY',
    'UF_WORKER',
    'UF_DEPARTMENT_WORKER',
    'UF_DATE_REG',
    'UF_SUPERVISOR_WORKER',
    'UF_SUPERVISOR_DEPARTMENT_WORKER',
    'UF_STATUS',
];

foreach ($rawFilter as $key => $value) {
    if (in_array($key, $skipKeys, true) || $value === '' || $value === null || $value === []) {
        continue;
    }

    $operator = '=';
    $baseField = null;

    if (preg_match('/^(.+?)_(from|to|datesel|month|quarter|year|preset|ops|numsel)$/', $key, $m)) {
        $baseField = $m[1];
        $suffix = $m[2];
        if ($suffix === 'from') {
            $operator = '>=';
        } elseif ($suffix === 'to') {
            $operator = '<=';
        } else {
            continue;
        }
    } elseif (preg_match('/^(>=|<=|>|<|=|%|!%)?([a-zA-Z0-9_]+)$/', $key, $m)) {
        $operator = $m[1] ? : '=';
        $baseField = $m[2];
    } else {
        continue;
    }

    if (!in_array($baseField, $allowedFields, true)) {
        continue;
    }

    $ormFilter["{$operator}{$baseField}"] = ($baseField === 'ID') ? (int)$value : trim($value);
}

// ============================================================================
// ЗАПРОС ДАННЫХ ИЗ HIGHLOAD-БЛОКА
// ============================================================================
$res = $entityDataClass ::getList([
    'select'      => ['*'],
    'order'       => $order,
    'filter'      => $ormFilter,
    'count_total' => true,
    'offset'      => $nav -> getOffset(),
    'limit'       => $nav -> allRecordsShown() ? null : $nav -> getLimit(),
]);

// ============================================================================
// ФОРМИРОВАНИЕ СТРОК ГРИДА
// ============================================================================
$gridRows = [];

while ($row = $res -> fetch()) {
    // Преобразуем массив ФИО в строку
    $approversString = is_array($row['UF_APPROVERS']) ? implode(
        ', ',
        $row['UF_APPROVERS']
    ) : (string)$row['UF_APPROVERS'];

    $fileLinks = '';
    if (!empty($row['UF_FILES'])) {
        // Приводим к массиву, если вернул одиночное значение или строку
        $fileIds = is_array($row['UF_FILES']) ? $row['UF_FILES'] : [$row['UF_FILES']];

        $linksArray = [];
        foreach ($fileIds as $fileId) {
            if ((int)$fileId > 0) {
                // Получаем данные о файле
                $fileData = CFile ::GetFileArray($fileId);
                if ($fileData) {
                    // Экранируем имя для безопасности
                    $fileName = htmlspecialcharsbx($fileData['ORIGINAL_NAME']);
                    // Формируем HTML-ссылку на скачивание
                    $linksArray[] = '<a href="' . $fileData['SRC'] . '" target="_blank" download>' . $fileName . '</a>';
                }
            }
        }
        // Объединяем ссылки через перенос строки или запятую
        $fileLinks = implode('<br>', $linksArray);
    }

    // Формируем действия
    $rowActions = [];

    // Приводим множественное поле согласующих к чистому массиву целых чисел
    $approverIds = [];
    if (!empty($row['UF_APPROVERS_ID'])) {
        $approverIds = is_array($row['UF_APPROVERS_ID'])
            ? array_map('intval', $row['UF_APPROVERS_ID'])
            : [(int)$row['UF_APPROVERS_ID']];
    }

    // Добавляем действия динамически в зависимости от XML_ID статуса
    $currentXmlId = $statusXmlList[$row['UF_STATUS']] ?? '';
    switch ($currentXmlId) {
        case 'WAITING_WORKER':
        case 'REVISION':
            if ($currentUserId === (int)$row['UF_WORKER_ID']) {
                $rowActions[] = [
                    'text'    => 'Выполнить анализ несоответствия',
                    'onclick' => 'addSolution(' . (int)$row['ID'] . ', ' . (int)$row['UF_CURRENT_ROUND'] . ');',
                ];
            }
            break;

        case 'APPROVAL':
            if (in_array($currentUserId, $approverIds, true)) {
                $rowActions[] = [
                    'text'    => 'Принять решение о согласовании',
                    'onclick' => 'decisionApproval(' . (int)$row['ID'] . ');',
                ];
            }
            break;
    }

    // Добавляем действия
    if ($currentXmlId === 'WAITING_WORKER' && $currentUserId === (int)$row['UF_INITIATOR']) {
        $rowActions[] = [
            'text'    => 'Изменить',
            'onclick' => 'updateElem(' . (int)$row['ID'] . ');',
        ];
    }

    $gridRows[] = [
        'data'    => [
            'ID'                              => $row['ID'],
            'UF_DOC_NAME'                     => $row['UF_DOC_NAME'],
            'UF_DATE_REG'                     => $row['UF_DATE_REG'],
            'UF_NAME_NONCONFORMITY'           => $row['UF_NAME_NONCONFORMITY'],
            'UF_WORKER'                       => $row['UF_WORKER'],
            'UF_WORKER_ID'                    => $row['UF_WORKER_ID'],
            'UF_DEPARTMENT_WORKER'            => $row['UF_DEPARTMENT_WORKER'],
            'UF_SUPERVISOR_WORKER'            => $row['UF_SUPERVISOR_WORKER'],
            'UF_SUPERVISOR_WORKER_ID'         => $row['UF_SUPERVISOR_WORKER_ID'],
            'UF_SUPERVISOR_DEPARTMENT_WORKER' => $row['UF_SUPERVISOR_DEPARTMENT_WORKER'],
            'UF_STATUS'                       => $statusList[$row['UF_STATUS']] ?? 'Неизвестно',
            'UF_DEADLINE'                     => $row['UF_DEADLINE'],
            'UF_APPROVERS'                    => $approversString,
            'UF_DATE_ACCEPTED'                => $row['UF_DATE_ACCEPTED'],
            'UF_WORKER_COMMENT'               => $row['UF_WORKER_COMMENT'],
            'UF_APPROVER_COMMENTS'            => is_array($row['UF_APPROVER_COMMENTS'])
                ? implode('<br>', $row['UF_APPROVER_COMMENTS'])
                : $row['UF_APPROVER_COMMENTS'],
            'UF_FILES'                        => $fileLinks,
            'UF_ROUNDS_DATES'                 => is_array($row['UF_ROUNDS_DATES'])
                ? implode('<br>', $row['UF_ROUNDS_DATES'])
                : $row['UF_ROUNDS_DATES'],
            'UF_FINAL_APPROVAL_DATE'          => $row['UF_FINAL_APPROVAL_DATE'],
        ],
        'actions' => $rowActions,
    ];
}

$nav -> setRecordCount($res -> getCount());

// ============================================================================
// КОНФИГУРАЦИЯ КОМПОНЕНТОВ
// ============================================================================
$gridFilter = [
    ['id' => 'ID', 'name' => '№', 'type' => 'number', 'default' => true],
    ['id' => 'UF_DOC_NAME', 'name' => 'Наименование документа н/с', 'type' => 'string', 'default' => true],
    ['id' => 'UF_DATE_REG', 'name' => 'Дата регистрации', 'type' => 'date', 'default' => true],
    ['id' => 'UF_NAME_NONCONFORMITY', 'name' => 'Несоответствие', 'type' => 'string', 'default' => true],
    ['id' => 'UF_WORKER', 'name' => 'Работник', 'type' => 'string', 'default' => true],
    ['id' => 'UF_DEPARTMENT_WORKER', 'name' => 'Подразделение работника', 'type' => 'string', 'default' => true],
    ['id' => 'UF_SUPERVISOR_WORKER', 'name' => 'Руководитель работника', 'type' => 'string', 'default' => true],
    [
        'id'      => 'UF_SUPERVISOR_DEPARTMENT_WORKER',
        'name'    => 'Подразделение руководителя',
        'type'    => 'string',
        'default' => true,
    ],
    ['id' => 'UF_STATUS', 'name' => 'Статус', 'type' => 'list', 'default' => true, 'items' => $statusList],
];
?>

<div class="content">
	<div class="content-top">
        <?php
        $APPLICATION -> IncludeComponent(
            'bitrix:main.ui.filter',
            '',
            [
                'FILTER_ID'          => $FILTER_ID,
                'GRID_ID'            => $GRID_ID,
                'FILTER'             => $gridFilter,
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL'       => true,
                'DISABLE_SEARCH'     => false,
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        ?>

		<?php
		if (in_array($currentUserId, [1348, 3873, 72, 206, 3955, 4094], true)): ?>
		<div class="content-top-btns">
			<button class="open-btn btn-creation" id="openModal">Завести несоответствие</button>
		</div>
		<?php
		endif; ?>
	</div>

    <?php
    $APPLICATION -> IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        [
            'GRID_ID'                   => $GRID_ID,
            'COLUMNS'                   => $gridColumns,
            'ROWS'                      => $gridRows,
            'NAV_OBJECT'                => $nav,
            'AJAX_MODE'                 => 'Y',
            'AJAX_OPTION_JUMP'          => 'N',
            'AJAX_OPTION_HISTORY'       => 'N',
            'AJAX_PATH'                 => $_SERVER['REQUEST_URI'],
            'SHOW_PAGESIZE'             => true,
            'PAGE_SIZES'                => [
                ['NAME' => '5', 'VALUE' => '5'],
                ['NAME' => '10', 'VALUE' => '10'],
                ['NAME' => '20', 'VALUE' => '20'],
                ['NAME' => '50', 'VALUE' => '50'],
            ],
            'SHOW_CHECK_ALL_CHECKBOXES' => false,
            'SHOW_ROW_CHECKBOXES'       => false,
            'SHOW_SELECTED_COUNTER'     => false,
            'SHOW_TOTAL_COUNTER'        => false,
        ],
        false,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
</div>

<!-- ======================================================================== -->
<!-- МОДАЛЬНОЕ ОКНО СОЗДАНИЯ -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modalOverlay">
	<div class="modal-content">
		<span class="close-btn" id="closeModal">&times;</span>
		<h2>Новое несоответствие</h2>

		<form id="form-creation">
			<div class="form-group">
				<label for="doc-name">Наименование документа н/с:</label>
				<input type="text" id="doc-name" name="doc-name" required>
			</div>

			<div class="form-group">
				<label for="name-nonconformity">Несоответствие:</label>
				<textarea id="name-nonconformity" name="name-nonconformity" required></textarea>
			</div>

			<div class="form-group">
				<p>Работник допустивший н/с:</p>
                <?php
                $APPLICATION -> IncludeComponent(
                    "bitrix:main.user.selector",
                    "",
                    [
                        "ID"            => "worker_selector",
                        "INPUT_NAME"    => "WORKER_ID",
                        "MULTIPLE"      => "N",
                        "NAME"          => "Выбрать работника",
                        "USE_BUTTON"    => "Y",
                        "SHOW_USERS"    => "Y",
                        "SHOW_EXTERNAL" => "Y",
                    ]
                );
                ?>
			</div>

			<div class="form-group">
				<p>Руководитель работника:</p>
                <?php
                $APPLICATION -> IncludeComponent(
                    "bitrix:main.user.selector",
                    "",
                    [
                        "ID"            => "supervisor_selector",
                        "INPUT_NAME"    => "SUPERVISOR_ID",
                        "MULTIPLE"      => "N",
                        "NAME"          => "Выбрать руководителя",
                        "USE_BUTTON"    => "Y",
                        "SHOW_USERS"    => "Y",
                        "SHOW_EXTERNAL" => "Y",
                    ]
                );
                ?>
			</div>

			<div class="form-group">
				<p>Группа контроля:</p>
                <?php
                $APPLICATION -> IncludeComponent(
                    "bitrix:main.user.selector",
                    "",
                    [
                        "ID"            => "control_selector",
                        "INPUT_NAME"    => "CONTROL_ID[]",
                        "MULTIPLE"      => "Y",
                        "NAME"          => "Выбрать сотрудников",
                        "USE_BUTTON"    => "Y",
                        "SHOW_USERS"    => "Y",
                        "SHOW_EXTERNAL" => "Y",
                        "SHOW_DEL"      => "Y",
                        "SHOW_SEARCH"   => "Y",
                    ]
                );
                ?>
			</div>

			<button type="submit" class="submit-btn">Создать</button>
		</form>
	</div>
</div>

<!-- ======================================================================== -->
<!-- МОДАЛЬНОЕ ОКНО РЕДАКТИРОВАНИЯ -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modalOverlayEdit">
	<div class="modal-content">
		<span class="close-btn" id="closeModalEdit">&times;</span>
		<h2 id="editModalTitle">Изменить несоответствие</h2>

		<form id="form-edit">
			<p style="color: #a73232; font-size: 14px; background-color: #f5eaea; padding: 20px; border-radius: 10px;">
				Поля, которые не нужно менять, оставьте пустыми
			</p>
			<input type="hidden" id="edit-record-id" name="record_id" value="">

			<div class="form-group">
				<label for="edit-doc-name">Наименование документа н/с:</label>
				<input type="text" id="edit-doc-name" name="doc-name">
			</div>

			<div class="form-group">
				<label for="edit-name-nonconformity">Несоответствие:</label>
				<textarea id="edit-name-nonconformity" name="name-nonconformity"></textarea>
			</div>

			<div class="btns-update">
				<button type="button" class="delete-btn" id="deleteRecordBtn">Удалить несоответствие</button>
				<button type="submit" class="submit-btn">Сохранить изменения</button>
			</div>
		</form>
	</div>
</div>

<!-- ======================================================================== -->
<!-- МОДАЛЬНОЕ ОКНО РЕШЕНИЯ СОТРУДНИКА -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modalOverlaySolution">
	<div class="modal-content">
		<span class="close-btn" id="closeModalSolution">&times;</span>
		<h2 id="solutionModalTitle">Выполнить анализ несоответствия</h2>

		<form id="form-solution" enctype="multipart/form-data">
			<input type="hidden" id="solution-record-id" name="record_id" value="">

			<div class="form-group">
				<label for="solution-comment">Комментарий:</label>
				<textarea id="solution-comment" name="comment" required></textarea>
			</div>

			<div class="form-group">
				<label for="solution-files">Прикрепить файлы:</label>

				<!-- Визуальный контейнер для Drag-and-Drop -->
				<div id="drop-zone" class="file-drop-zone">
					<span class="drop-zone-text">Переместите файлы в эту область или <mark>выберите на диске</mark></span>
					<!-- Скрытый оригинальный инпут -->
					<input type="file" id="solution-files" name="WORKER_FILES[]" multiple style="display: none;">
				</div>

				<!-- динамически выводиться выбранные файлы -->
				<div id="file-list" class="selected-files-list"></div>
			</div>

			<div class="form-group" id="approvers-form-group">
				<p>Соглаующие:</p>
                <?php
                $APPLICATION -> IncludeComponent(
                    "bitrix:main.user.selector",
                    "",
                    [
                        "ID"            => "worker_selector_solution",
                        "INPUT_NAME"    => "WORKER_ID_SOLUTION[]",
                        "MULTIPLE"      => "Y",
                        "NAME"          => "Выбрать сотрудников",
                        "USE_BUTTON"    => "Y",
                        "SHOW_USERS"    => "Y",
                        "SHOW_EXTERNAL" => "Y",
                        "SHOW_DEL"      => "Y",
                        "SHOW_SEARCH"   => "Y",
                    ]
                );
                ?>
			</div>

			<button type="submit" class="submit-btn">Отправить</button>
		</form>
	</div>
</div>

<!-- ======================================================================== -->
<!-- МОДАЛЬНОЕ ОКНО РЕШЕНИЯ СОГЛАСУЮЩЕГО -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modalOverlayDecision">
	<div class="modal-content">
		<span class="close-btn" id="closeModalDecision">&times;</span>
		<h2 id="decisionModalTitle">Принять решение о согласовании несоответствия</h2>

		<form id="form-decision">
			<input type="hidden" id="decision-record-id" name="record_id" value="">

			<div class="form-group">
				<label for="decision-comment">Комментарий:</label>
				<textarea id="decision-comment" name="decision-comment"></textarea>
			</div>

			<div class="btns-decision" style="display: flex; gap: 10px; margin-top: 15px;">
				<button type="submit" class="reject-btn" data-action="reject">Отклонить</button>
				<button type="submit" class="submit-btn" data-action="approve">Согласовать</button>
			</div>
		</form>
	</div>
</div>

<!-- ======================================================================== -->
<!-- КОНТЕЙНЕР ДЛЯ ВСПЛЫВАЮЩИХ УВЕДОМЛЕНИЙ -->
<!-- ======================================================================== -->
<div class="toast-container" id="toastContainer"></div>

<!-- ======================================================================== -->
<!-- ОКНО ПОДТВЕРЖДЕНИЯ (УДАЛЕНИЕ ЗАПИСИ) -->
<!-- ======================================================================== -->
<div class="confirm-overlay" id="confirmOverlay">
	<div class="confirm-box">
		<h3 class="confirm-title" id="confirmTitle">Подтверждение</h3>
		<div class="confirm-message" id="confirmMessage"></div>
		<div class="confirm-buttons">
			<button class="confirm-btn confirm-btn-cancel" id="confirmCancel">Отмена</button>
			<button class="confirm-btn confirm-btn-ok" id="confirmOk">Да</button>
		</div>
	</div>
</div>

<script src="/nonconformity-analysis/assets/script.js"></script>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
