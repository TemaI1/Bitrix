//Cписок значений поля(10) highload-блока
$rsEnumStatus = CUserFieldEnum ::GetList([], ["USER_FIELD_ID" => 10]);
$enumStatus = [];
while ($arEnum = $rsEnumStatus -> Fetch()) {
    $enumStatus[$arEnum["ID"]] = $arEnum["VALUE"];
}
