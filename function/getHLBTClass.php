//Получение объекта сущности highloadblock по ID
function getHLBTClass($HlBlockId){
    CModule ::IncludeModule('highloadblock');
    if (empty($HlBlockId) || $HlBlockId < 1) {
        return false;
    }
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable ::getById($HlBlockId) -> fetch();
    $entity = Bitrix\Highloadblock\HighloadBlockTable ::compileEntity($hlblock);
    $entity_data_class = $entity -> getDataClass();
    return $entity_data_class;
}
