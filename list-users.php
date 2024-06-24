//Получить список пользователей
$by="id"; //поле для сортировки (сортировка по id)
$order="asc"; //порядок сортировки (сортировка asc - по возрастанию)
$filter = ["ID" => "3500 | 6700"]; //необязательный массив для фильтрации (ID пользователя)
$rsUsers = CUser::GetList($by, $order, $filter); //возвращает список пользователей в виде объекта
while ($arRes = $rsUsers->Fetch()){
	?><pre><? print_r($arRes) ?></pre><? //выводим результат на страницу
}



//Получить пользователя по ID
$rsUser = CUser::GetByID(6700); //возвращает пользователя по его коду id в виде объекта
$arUser = $rsUser->Fetch(); //делает выборку значений полей в массив
?><pre><? print_r($arUser) ?></pre><? //выводим результат на страницу



//Получить ID текущего авторизованного пользователя
$userId = $USER->GetID();
?><pre><? print_r($userId) ?></pre><? //выводим результат на страницу