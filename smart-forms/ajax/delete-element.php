<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule ::IncludeModule('highloadblock');
global $APPLICATION;
use Bitrix\Main\Application;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["elemHL"])){

    // получение объекта сущности highloadblock
    function getHBbyName($code){
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $code]
        ])->fetch();
        $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock))->getDataClass();
        return $entity_data_class;
    }

    $smartFormHL = getHBbyName("b_hlbd_smartform");
    // удаляем запись смарт формы в hl
    $resultSmartForm = $smartFormHL ::Delete($post["elemHL"]);

    // получение решений
    $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");
    $rsSmartFormSolution = $smartFormSolutionHL ::getList(
        [
            "select" => ["*"],
            "order"  => ["ID" => "ASC"],
            "filter" => ['UF_ID_PROBLEM' => $post["elemHL"]],
        ]
    );
    $solutions = [];
    while ($arSmartFormSolution = $rsSmartFormSolution -> Fetch()){
        $solutions[] = $arSmartFormSolution["ID"];
        // удаляем запись решений в hl
        $resultSolution = $smartFormSolutionHL ::Delete($arSmartFormSolution["ID"]);
    }

    if (!empty($solutions)){
        // получение этапов
        $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");
        $rsFormStages = $smartFormStagesHL ::getList(
            [
                "select" => ["*"],
                "order"  => ["ID" => "ASC"],
                "filter" => ['UF_ID_SOLUTION' => $solutions],
            ]
        );
        while ($arFormStages = $rsFormStages -> Fetch()){
            // удаляем запись этапов в hl
            $resultStages = $smartFormStagesHL ::Delete($arFormStages["ID"]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'error_text' => 'Ошибка удаления',
        ]);
        die();
    }

    echo json_encode([
        'status' => 'success',
        'text' => 'Проблема удалена',
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка удаления',
    ]);
    die();
}
