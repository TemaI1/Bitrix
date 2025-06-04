<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule ::IncludeModule('highloadblock');
global $APPLICATION;
use Bitrix\Main\Application;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["elemHL"])){

    // получение объекта сущности highloadblock
    function getHBbyName($code)
    {
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable ::getList([
            'filter' => ['=TABLE_NAME' => $code],
        ]) -> fetch();
        $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable ::compileEntity($hlblock)) -> getDataClass();
        return $entity_data_class;
    }

    // добавление решения в hl
    $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");
    $dataAddSolution = [
        "UF_ID_PROBLEM" => $post["elemHL"],
    ];
    $resultSolution = $smartFormSolutionHL ::add($dataAddSolution);

    // получение последнего элемента решения
    $rsSmartFormSolution = $smartFormSolutionHL ::getList(
        [
            "select" => ["ID"],
            "order"  => ["ID" => "DESC"],
            "limit"  => 1,
        ]
    );

    if ($arSmartFormSolution = $rsSmartFormSolution -> Fetch()) {
        $lastIDSolution = $arSmartFormSolution["ID"];
    }

    if (!empty($lastIDSolution)) {
        // добавление этапа в hl
        $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");
        $dataAddStages = [
            "UF_ID_SOLUTION" => $lastIDSolution,
        ];
        $resultStages = $smartFormStagesHL ::add($dataAddStages);

        echo json_encode([
            'status' => 'success',
            'text'   => 'Решение добавлено',
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка добавления',
    ]);
    die();
}





