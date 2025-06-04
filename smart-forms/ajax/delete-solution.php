<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule ::IncludeModule('highloadblock');
global $APPLICATION;
use Bitrix\Main\Application;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["elemProblem"]) && !empty($post["elemSolution"])){

    // получение объекта сущности highloadblock
    function getHBbyName($code){
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $code]
        ])->fetch();
        $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock))->getDataClass();
        return $entity_data_class;
    }

    $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");

    // получение кол-ва решений по одной проблеме
    $countSolution = $smartFormSolutionHL ::getCount(['UF_ID_PROBLEM' => $post["elemProblem"]]);

    if ($countSolution > 1){
        // удаляем запись решения в hl
        $resultSolution = $smartFormSolutionHL ::Delete($post["elemSolution"]);

        // получение этапов
        $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");
        $rsFormStages = $smartFormStagesHL ::getList(
            [
                "select" => ["*"],
                "order"  => ["ID" => "ASC"],
                "filter" => ['UF_ID_SOLUTION' => $post["elemSolution"]],
            ]
        );
        while ($arFormStages = $rsFormStages -> Fetch()){
            // удаляем запись этапов в hl
            $resultStages = $smartFormStagesHL ::Delete($arFormStages["ID"]);
        }

        echo json_encode([
            'status' => 'success',
            'text' => 'Решение удалено',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'error_text' => 'Невозможно удалить единственное решение',
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
