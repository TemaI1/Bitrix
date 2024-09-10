<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Application;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\UI\PageNavigation;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;
global $USER;
$APPLICATION -> SetTitle("Ответы на вопросы");
?>

<?
//получение данных
$uID = $USER -> GetID();
$fio = getUserFullName($uID);
$date = new DateTime ();
$date = $date -> format('d.m.Y');
$user = CUser ::GetByID($uID) -> Fetch();
$company = $user['WORK_COMPANY'];

//группа для создания ответа
$arrExperts = [5700, 3200];

//добавление колонок грида
$arResult['GRID']["COLUMNS"] = [
    ['id' => 'ID', 'name' => '№', 'sort' => 'ID', 'default' => true],
    ['id' => 'UF_QUESTION_CREATOR', 'name' => 'Создатель вопроса', 'default' => true],
    ['id' => 'UF_QUESTION', 'name' => 'Вопрос', 'default' => true],
    ['id' => 'UF_ANSWER_CREATOR', 'name' => 'Ответивший', 'default' => true],
    ['id' => 'UF_ANSWER', 'name' => 'Ответ', 'default' => true],
    ['id' => 'UF_QUESTION_TOPICS', 'name' => 'Раздел', 'sort' => 'UF_QUESTION_TOPICS', 'default' => true],
    ['id' => 'UF_QUESTION_STATUSES', 'name' => 'Статус', 'sort' => 'UF_QUESTION_STATUSES', 'default' => true],
    ['id' => 'UF_DATE_ANSWER', 'name' => 'Дата вопроса', 'default' => false],
];

//список значений поля(10) highload-блока
$rsEnumTopics = CUserFieldEnum ::GetList([], ["USER_FIELD_ID" => 10]);
$enumTopics = [];
while ($arEnum = $rsEnumTopics -> Fetch()) {
    $enumTopics[$arEnum["ID"]] = $arEnum["VALUE"];
}

//список значений поля(11) highload-блока
$rsEnumStatus = CUserFieldEnum ::GetList([], ["USER_FIELD_ID" => 11]);
$enumStatus = [];
while ($arEnum = $rsEnumStatus -> Fetch()) {
    $enumStatus[$arEnum["ID"]] = $arEnum["VALUE"];
}

//навигация для грида
$grid_options = new Bitrix\Main\Grid\Options("question_answer"); // получаем опции грида
$nav_params = $grid_options -> GetNavParams(); // получаем параметры навигации
$nav = new \Bitrix\Main\UI\PageNavigation("question_answer"); // класс навигации

$nav -> allowAllRecords(true)
    -> setPageSize($nav_params['nPageSize']) // количество элементов на странице
    -> initFromUri();

if ($nav -> allRecordsShown()) {
    $nav_params = false;
} else {
    $nav_params['iNumPage'] = $nav -> getCurrentPage(); // номер страницы
}

// сортировка
$sortArray = $grid_options -> getSorting();
foreach ($sortArray['sort'] as $key => $value) {
    $order = [$key => $value];
}
if (empty($order)) {
    $order = ['ID' => 'desc'];
}

// получение объекта сущности highloadblock
$hlBlockID = 1;
$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlBlockID)->fetch();
$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$HLQuestion = $entity->getDataClass();

$nav -> setRecordCount($HLQuestion ::getCount()); // устанавливает количество записей для навигации

//фильтр для грида
$arrFilt = ['PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND']; // массив ненужных данных массива фильтра
$filterOption = new Bitrix\Main\UI\Filter\Options('question_answer'); // получаем опции фильтра
$filterData = $filterOption -> getFilter([]); // текущие значения фильтра
$filterHLB = [];
foreach ($filterData as $k => $v) {
    if (!in_array($k, $arrFilt)) {
        $filterHLB[] = [$k => $v]; // собираем фильтр
    }
}

$resHLQuestion = $HLQuestion ::getList(
    [
        "select"      => [
            "ID",
            "UF_QUESTION",
            "UF_QUESTION_TOPICS",
            "UF_QUESTION_STATUSES",
            "UF_ANSWER",
            "UF_QUESTION_CREATOR",
            "UF_ANSWER_CREATOR",
            "UF_DATE_ANSWER",
        ],
        "order"       => $order,
        "filter"      => $filterHLB,
        "count_total" => true, // заставляет ORM выполнить отдельный запрос COUNT
        "offset"      => $nav -> getOffset(), // возвращает позицию первой записи
        "limit"       => $nav -> getLimit(), // возвращает количество записей на странице
    ]
);

while ($arr = $resHLQuestion -> Fetch()) {
    // получаем данные создателя вопроса
    $rsQuestionUser = CUser ::GetByID($arr["UF_QUESTION_CREATOR"]);
    $arReceive = $rsQuestionUser -> Fetch(); // получаем данные сотрудника
    $questionCreatorName = $arReceive['LAST_NAME'] . " " . $arReceive['NAME'] . " " . $arReceive['SECOND_NAME'];

    // получаем данные ответившего
    $rsAnswerUser = CUser ::GetByID($arr["UF_ANSWER_CREATOR"]);
    $arReceive = $rsAnswerUser -> Fetch(); // получаем данные сотрудника
    $answerCreatorName = $arReceive['LAST_NAME'] . " " . $arReceive['NAME'] . " " . $arReceive['SECOND_NAME'];

    // определяем список действий для грида
    // 1 - ID значения списка (создан)
    if ($arr["UF_QUESTION_STATUSES"] == 1) {
		if ($USER -> IsAdmin() || in_array($uID, $arrExperts)){
            $actions = [
                [
                    'text'    => "Ответить",
                    'onclick' => 'giveAnswer(' . $arr["ID"] . ');',
                ],
            ];
		}
    }
    // 2 - ID значения списка (получен ответ)
    if ($arr["UF_QUESTION_STATUSES"] == 2) {
        if ($USER -> GetID() == $arr["UF_QUESTION_CREATOR"]) {
            $actions = [
                [
                    'text'    => "Уточнить",
                    'onclick' => 'clarifyQuestion(' . $arr["ID"] . ');',
                ],
                [
                    'text'    => "Завершить",
                    'onclick' => 'closeQuestion(' . $arr["ID"] . ');',
                ],
                [
                    'text'    => "История вопрос/ответ",
                    'onclick' => 'historyQuestion(' . $arr["ID"] . ');',
                ],
            ];
        } else {
            $actions = [
                [
                    'text'    => "История вопрос/ответ",
                    'onclick' => 'historyQuestion(' . $arr["ID"] . ');',
                ],
            ];
        }
    }
    // 3 - ID значения списка (требуется разъяснение)
    if ($arr["UF_QUESTION_STATUSES"] == 3) {
        if ($USER -> IsAdmin() || in_array($uID, $arrExperts)){
            $actions = [
                [
                    'text'    => "Ответить",
                    'onclick' => 'giveAnswer(' . $arr["ID"] . ');',
                ],
                [
                    'text'    => "История вопрос/ответ",
                    'onclick' => 'historyQuestion(' . $arr["ID"] . ');',
                ],
            ];
		} else {
            $actions = [
                [
                    'text'    => "История вопрос/ответ",
                    'onclick' => 'historyQuestion(' . $arr["ID"] . ');',
                ],
            ];
		}
    }
    // 4 - ID значения списка (закрыт)
    if ($arr["UF_QUESTION_STATUSES"] == 4) {
        $actions = [
            [
                'text'    => "История вопрос/ответ",
                'onclick' => 'historyQuestion(' . $arr["ID"] . ');',
            ],
        ];
    }
    // 6 - ID значения списка (неактивен)
    if ($arr["UF_QUESTION_STATUSES"] == 6) {
		continue; //убираем вопрос из грида
    }

    //добавление строк грида
    $elementRow = [
        'data'    => [
            "ID"                   => $arr["ID"],
            "UF_QUESTION_CREATOR"  => $questionCreatorName,
            "UF_QUESTION"          => $arr["UF_QUESTION"],
            "UF_ANSWER_CREATOR"    => $answerCreatorName,
            "UF_ANSWER"            => $arr["UF_ANSWER"],
            "UF_QUESTION_TOPICS"   => $enumTopics[$arr["UF_QUESTION_TOPICS"]],
            "UF_QUESTION_STATUSES" => $enumStatus[$arr["UF_QUESTION_STATUSES"]],
            "UF_DATE_ANSWER"       => $arr["UF_DATE_ANSWER"],
        ],
        'actions' => $actions,
    ];
    $arResult['GRID']["ROWS"][] = $elementRow;
}
?>

<a href="#" class="btn btn-secondary procument_btn mb-2" data-toggle="modal" data-target="#question-answer">Задать вопрос</a>

<div class="procurement">
    <?
    //вызов компонента (фильтр)
    $APPLICATION -> IncludeComponent(
        'bitrix:main.ui.filter',
        '',
        [
            'FILTER_ID'          => 'question_answer',
            'GRID_ID'            => 'question_answer',
            'FILTER'             => [
                ['id' => 'ID', 'name' => 'Номер вопроса', 'type' => 'string', 'default' => true],
                ['id' => 'UF_QUESTION', 'name' => 'Вопрос', 'type' => 'string', 'default' => true],
            ],
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL'       => true,
            'DISABLE_SEARCH'     => true,
        ]
    );

    //вызов компонента (грид)
    $APPLICATION -> IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        [
            'GRID_ID'                   => 'question_answer',
            'COLUMNS'                   => $arResult['GRID']["COLUMNS"],
            'ROWS'                      => $arResult['GRID']["ROWS"],
            'NAV_OBJECT'                => $nav,
            'AJAX_MODE'                 => 'Y',
            'AJAX_OPTION_JUMP'          => 'N',
            'AJAX_OPTION_HISTORY'       => 'N',
            'AJAX_ID'                   => \CAjax ::getComponentID('bitrix:main.ui.grid', '.default', ''),
            'SHOW_PAGESIZE'             => true,
            'PAGE_SIZES'                => [
                ['NAME' => "5", 'VALUE' => '5'],
                ['NAME' => '10', 'VALUE' => '10'],
                ['NAME' => '20', 'VALUE' => '20'],
                ['NAME' => '50', 'VALUE' => '50'],
            ],
            'SHOW_CHECK_ALL_CHECKBOXES' => false,
            'SHOW_ROW_CHECKBOXES'       => false,
            'SHOW_ROW_ACTIONS_MENU'     => true,
            'SHOW_GRID_SETTINGS_MENU'   => true,
            'SHOW_NAVIGATION_PANEL'     => true,
            'SHOW_PAGINATION'           => true,
            'SHOW_SELECTED_COUNTER'     => false,
            'SHOW_TOTAL_COUNTER'        => false,
            'SHOW_ACTION_PANEL'         => true,
            'ALLOW_COLUMNS_SORT'        => true,
            'ALLOW_COLUMNS_RESIZE'      => true,
            'ALLOW_HORIZONTAL_SCROLL'   => true,
            'ALLOW_SORT'                => true,
            'ALLOW_PIN_HEADER'          => true,
        ]
    );
    ?>
</div>

<div class="modal fade" id="give-answer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	 aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content ">
			<div class="modal-header">
				<h5 class="text-center">Дать ответ</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="" method="post" id="give-answer-form" class="give-answer-form">
				<div class="modal-body">
					<input type="text" value="<?= $uID ?>" class="form-control" name="questionGiveFioId"
						   style="display: none">
					<input type="text" value="<?= $fio ?>" class="form-control" name="questionGiveFioName"
						   style="display: none">
					<input type="text" value="<?= $date ?>" class="form-control" name="questionGiveDate"
						   style="display: none">
					<div class='mt-3'>Ваш ответ</div>
					<textarea rows="8" name="questionGiveText" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary">Отправить</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть окно</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="clarify-question" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	 aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content ">
			<div class="modal-header">
				<h5 class="text-center">Уточнить вопрос</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="" method="post" id="clarify-question-form" class="clarify-question-form">
				<div class="modal-body">
					<input type="text" value="<?= $uID ?>" class="form-control" name="clarifyQuestionFioId"
						   style="display: none">
					<input type="text" value="<?= $fio ?>" class="form-control" name="clarifyQuestionFioName"
						   style="display: none">
					<input type="text" value="<?= $date ?>" class="form-control" name="clarifyQuestionDate"
						   style="display: none">
					<div class='mt-3'>Ваш вопрос</div>
					<textarea rows="8" name="clarifyQuestionText" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary">Отправить</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть окно</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="history-question" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	 aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content ">
			<div class="modal-header">
				<h5 id="history-question-title" class="text-center"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p id="history-question-text" class='mt-3' style="white-space: pre-wrap;"></p>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="question-answer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content ">
			<div class="modal-header">
				<h5 class="text-center" >Создать вопрос</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="" method="post" id="question-answer-form">
					<input type="text" value="<?=$uID?>" class="form-control" name="questionFioId" style="display: none">
					<input type="text" value="<?=$fio?>" class="form-control" name="questionFioName" style="display: none">
					<input type="text" value="<?=$date?>" class="form-control" name="questionDate" style="display: none">
					<div class='mt-3'>Выберите раздел вашего вопроса</div>
					<!--option value формируются из Highload-блок(ID значения списка)-->
					<select name="questionName" class="form-control" required>
						<option value="6">Разъяснения</option>
						<option value="7">Особые</option>
						<option value="8">Приложение</option>
						<option value="9" selected>Иное</option>
					</select>
					<div class='mt-3'>Ваш вопрос</div>
					<textarea rows="8" name="questionText" class="form-control" required></textarea>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary">Отправить</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть окно</button>
			</div>
			</form>
		</div>
	</div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
