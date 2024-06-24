use Bitrix\Main\Loader; //загрузка необходимых файлов, классов и модулей
Loader::includeModule("highloadblock"); //подключение модуля
use Bitrix\Main\Entity; //пространство имён для работы с сущностями

//Получение объекта сущности хайлоадблока
$hlbl = 10; //ID highloadblock
$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$hlData = $entity->getDataClass();

//Добавление записи в highloadblock
$dataAdd = [
    "UF_QUESTION"          => "data QUESTION",
    "UF_QUESTION_CREATOR"  => "data CREATOR",
];
$result = $hlData ::add($dataAdd);

//Обновление записи в highloadblock
$dataUpdate = [
    "UF_QUESTION"          => "new data QUESTION",
    "UF_QUESTION_CREATOR"  => "new data CREATOR",
];
$result = $hlData ::update("3", $dataUpdate); //3-id обновляемой записи

//Удаление записи по ID из highloadblock
$result = $hlData ::delete("3");  //3-id удаляемой записи 
