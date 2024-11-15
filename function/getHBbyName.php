//Получение объекта сущности highloadblock по его названию таблицы в БД (b_hlbd)
use Bitrix\Main\Loader; 
Loader::includeModule("highloadblock"); //подключение модуля
function getHBbyName($code){
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList([
        'filter' => ['=TABLE_NAME' => $code]
    ])->fetch();
    $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock))->getDataClass();
    return $entity_data_class;
}
