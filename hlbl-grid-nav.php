//Получение объекта сущности хайлоадблока
$hlbl = 10; //ID highloadblock
$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$hlData = $entity->getDataClass();

//Добавление колонок грида
$arResult['GRID']["COLUMNS"] = [
    ['id' => 'ID', 'name' => '№', 'sort' => 'ID', 'default' => true],
    ['id' => 'UF_QUESTION_CREATOR', 'name' => 'Создатель вопроса', 'default' => true],
    ['id' => 'UF_QUESTION', 'name' => 'Вопрос', 'default' => true],
];

//Навигация для грида
$grid_options = new Bitrix\Main\Grid\Options("my_grid"); //получаем опции грида
$nav_params = $grid_options -> GetNavParams(); //получаем параметры навигации
$nav = new \Bitrix\Main\UI\PageNavigation("my_grid"); //класс навигации

$nav -> allowAllRecords(true)
    -> setPageSize($nav_params['nPageSize']) //количество элементов на странице
    -> initFromUri();

if ($nav -> allRecordsShown()) {
    $nav_params = false;
} else {
    $nav_params['iNumPage'] = $nav -> getCurrentPage(); //номер страницы
}

//Сортировка
$sortArray = $grid_options -> getSorting();
foreach ($sortArray['sort'] as $key => $value) {
    $order = [$key => $value];
}
if (empty($order)) {
    $order = ['ID' => 'desc'];
}

$nav -> setRecordCount($hlData ::getCount()); //устанавливает количество записей для навигации

//Фильтр для грида
$arrFilt = ['PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND']; //массив ненужных данных фильтра
$filterOption = new Bitrix\Main\UI\Filter\Options('my_grid'); //получаем опции фильтра
$filterData = $filterOption -> getFilter([]); //текущие значения фильтра
$filterHLB = [];
foreach ($filterData as $k => $v) {
    if (!in_array($k, $arrFilt)) {
        $filterHLB[] = [$k => $v]; // собираем фильтр
    }
}

$reshlData = $hlData ::getList(
    [
        "select"      => [
            "ID",
            "UF_QUESTION",
            "UF_QUESTION_CREATOR",
        ],
        "order"       => $order,
        "filter"       => $filterHLB,
        "count_total" => true, //заставляет ORM выполнить отдельный запрос COUNT
        "offset"      => $nav -> getOffset(), //возвращает позицию первой записи
        "limit"       => $nav -> getLimit(), //возвращает количество записей на странице
    ]
);

while ($arr = $reshlData -> Fetch()) {
    $actions = [
        [
            'text'    => "Ответить",
            'onclick' => 'giveAnswer("data");',
        ],
    ];

    //добавление строк грида
    $elementRow = [
        'data'    => [
            "ID"                  => "data ID",
            "UF_QUESTION_CREATOR" => "data CREATOR",
            "UF_QUESTION"         => "data QUESTION",
        ],
        'actions' => $actions,
    ];
    $arResult['GRID']["ROWS"][] = $elementRow;
}

//Для вывода фильтра на страницу, подключаем компонент main.ui.filter
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID' => 'my_grid',
        'GRID_ID' => 'my_grid',
        'FILTER' => [
            ['id' => 'ID', 'name' => 'Номер вопроса', 'type' => 'string', 'default' => true],
        ],
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true,
        'DISABLE_SEARCH' => true,
    ]
);

//Для вывода грида на страницу, подключаем компонент main.ui.grid
$APPLICATION -> IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID'             => 'my_grid', //идентификатор компонента
        'COLUMNS'             => $arResult['GRID']["COLUMNS"], //массив колонок
        'ROWS'                => $arResult['GRID']["ROWS"], //массив строк
        'NAV_OBJECT'          => $nav, //постраничная навигация
        'AJAX_MODE'           => 'Y', //AJAX_* параметры
        'AJAX_OPTION_JUMP'    => 'N', //AJAX_* параметры
        'AJAX_OPTION_HISTORY' => 'N', //AJAX_* параметры
        'AJAX_ID'                   => \CAjax ::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'SHOW_PAGESIZE'             => true, //отображение выпадающего списка с выбором размера страницы
        'PAGE_SIZES'                => [
            ['NAME' => "5", 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
        ], //выпадающий список выбора размера страницы
        'SHOW_CHECK_ALL_CHECKBOXES' => false, //отображение чекбоксов "Выбрать все"
        'SHOW_ROW_CHECKBOXES'       => false, //отображение чекбоксов для строк
        'SHOW_SELECTED_COUNTER'     => false, //отображение счетчика выделенных строк
        'SHOW_TOTAL_COUNTER'        => false, //отображение счетчика общего количества строк
    ]
);
