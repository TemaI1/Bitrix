<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule ::IncludeModule('highloadblock');
global $APPLICATION;
use Bitrix\Main\Application;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["elemSolution"]) && !empty($post["elemStage"])){

    // получение объекта сущности highloadblock
    function getHBbyName($code){
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $code]
        ])->fetch();
        $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock))->getDataClass();
        return $entity_data_class;
    }

    $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");

    // получение кол-ва этапов по одному решению
    $countStage = $smartFormStagesHL ::getCount(['UF_ID_SOLUTION' => $post["elemSolution"]]);

    if ($countStage > 1){
        // удаляем запись этапа в hl
        $resultStages = $smartFormStagesHL ::Delete($post["elemStage"]);

        echo json_encode([
            'status' => 'success',
            'text' => 'Этап удален',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'error_text' => 'Невозможно удалить единственный этап',
        ]);
        die();
    }

} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка удаления',
    ]);
    die();
}
