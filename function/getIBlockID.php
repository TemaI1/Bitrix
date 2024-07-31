//Возвращает ID инфоблока по его коду
function GetIBlockID($code)
{
    \Bitrix\Main\Loader::includeModule("iblock");

    $iBlock = \Bitrix\Iblock\IblockTable::getList([
        'filter' => [
            'CODE' => $code,
        ],
        'select' => [
            'ID',
            'CODE',
        ]
    ]) -> fetch();

    return $iBlock["ID"];
}
