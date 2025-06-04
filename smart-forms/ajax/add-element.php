<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule ::IncludeModule('highloadblock');
global $APPLICATION;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

// получение объекта сущности highloadblock
function getHBbyName($code)
{
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable ::getList([
        'filter' => ['=TABLE_NAME' => $code],
    ]) -> fetch();
    $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable ::compileEntity($hlblock)) -> getDataClass();
    return $entity_data_class;
}

// добавление смарт формы в hl
$smartFormHL = getHBbyName("b_hlbd_smartform");
$dataAddSmartForm = [
    "UF_PROBLEM_DATE" => new DateTime(),
];
$resultSmartForm = $smartFormHL ::add($dataAddSmartForm);

// получение последнего элемента смарт формы
$rsSmartForm = $smartFormHL ::getList(
    [
        "select" => ["ID"],
        "order"  => ["ID" => "DESC"],
        "limit"  => 1,
    ]
);

if ($arSmartForm = $rsSmartForm -> Fetch()) {
    $lastIDSmartForm = $arSmartForm["ID"];
} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка добавления',
    ]);
    die();
}

// добавление решения и этапа в hl
if (!empty($lastIDSmartForm)) {
    // добавление решения в hl
    $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");
    $dataAddSolution = [
        "UF_ID_PROBLEM" => $lastIDSmartForm,
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
            'text'   => 'Проблема добавлена',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'error_text' => 'Ошибка добавления',
        ]);
        die();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка добавления',
    ]);
    die();
}





