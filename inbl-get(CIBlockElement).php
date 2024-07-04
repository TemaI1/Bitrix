//Получить ID инфоблока по ID его элемента
$blockId = CIBlockElement ::GetIBlockByID(37405);
?><pre><? print_r($blockId) ?></pre><?

//Получить поля элемента инфоблока
$res = CIBlockElement ::GetByID(37405);
while ($ob = $res -> GetNext()) {
    ?><pre><? print_r($ob) ?></pre><?
}

//Получить поля элемента инфоблока
$res = CIBlockElement ::GetList(
    ["ID"=>"ASC"], //сортировка
    ["IBLOCK_ID" => 35, "ID" => 37405], //фильтруемое поле
    false, //группировка элементов
    false, //навигация
    ["ID", "NAME", "PROPERTY_COMMENT"] //возвращаемые поля элемента
);
while ($ob = $res -> GetNextElement()) {
    $arFields = $ob->GetFields(); //содержит основные поля элемента инфоблока
    ?><pre><? print_r($arFields) ?></pre><?
}

//Получить свойства элемента инфоблока
$res = CIBlockElement ::GetList(
    ["ID"=>"ASC"], //сортировка
    ["IBLOCK_ID" => 35, "ID" => 37405], //фильтруемое поле
    false, // группировка элементов
    false, //навигация
    ["*"] //возвращаемые поля элемента
);
while ($ob = $res -> GetNextElement()) {
    $arProps = $ob->GetProperties(); //содержит свойства элемента инфоблока
    ?><pre><? print_r($arProps) ?></pre><?
}

//Получить значение свойств элемента
$res = CIBlockElement ::GetProperty(
    35, //ID инфоблока
    37405, //ID элемента
    [], //сортировка
    ["ID" => 327] //фильтруемое поле
);
while ($ob = $res -> Fetch()) {
    ?><pre><? print_r($ob) ?></pre><?
}

//Получить значения свойств для элементов одного информационного блока
$res = CIBlockElement ::GetPropertyValues(
    35, //ID инфоблока
    [], //фильтр
    true, //расширенное число полей
    ["ID" => "*"] //фильтр возвращаемых свойств
);
while ($ob = $res -> Fetch()) {
    ?><pre><? print_r($ob) ?></pre><?
}
