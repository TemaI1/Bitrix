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
    $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");
    $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");

    // собираем проблемы
    $dataProblemAdd = [];
    if (!empty($post["problemDate"])){
        $dataProblemAdd["UF_PROBLEM_DATE"] = date("d.m.Y", strtotime($post["problemDate"]));
    }
    if (!empty($post["initiator"])){
        $dataProblemAdd["UF_INITIATOR"] = $post["initiator"];
    }
    if (!empty($post["descriptionProblem"])){
        $dataProblemAdd["UF_DESCRIPTION_PROBLEM"] = $post["descriptionProblem"];
    }

    // обновление проблемы
    $resultProblem = $smartFormHL ::update($post["elemHL"], $dataProblemAdd);

    // собираем решения и этапы
    $dataSolutionAdd = [];
    $dataStagesAdd = [];
    foreach ($post as $key => $el){
        if (!empty($el)){

            switch ($key){
                case strpos($key, "descriptionSolution") !== false:
                    $dataSolutionAdd[str_replace("descriptionSolution", "", $key)]["descriptionSolution"] = $el;
                    break;
                case strpos($key, "descriptionStages") !== false:
                    $dataStagesAdd[str_replace("descriptionStages", "", $key)]["descriptionStages"] = $el;
                    break;
                case strpos($key, "executorStages") !== false:
                    $dataStagesAdd[str_replace("executorStages", "", $key)]["executorStages"] = $el;
                    break;
                case strpos($key, "decisionDateStages") !== false:
                    $dataStagesAdd[str_replace("decisionDateStages", "", $key)]["decisionDateStages"] = $el;
                    break;
                case strpos($key, "explanationStages") !== false:
                    $dataStagesAdd[str_replace("explanationStages", "", $key)]["explanationStages"] = $el;
                    break;
                case strpos($key, "changeDateStages") !== false:
                    $dataStagesAdd[str_replace("changeDateStages", "", $key)]["changeDateStages"] = $el;
                    break;
                case strpos($key, "closingDateStages") !== false:
                    $dataStagesAdd[str_replace("closingDateStages", "", $key)]["closingDateStages"] = $el;
                    break;
                case strpos($key, "statusStages") !== false:
                    $dataStagesAdd[str_replace("statusStages", "", $key)]["statusStages"] = $el;
                    break;
            }

        }
    }

    // обновление решений
    foreach ($dataSolutionAdd as $key => $solution){
        if (!empty($solution["descriptionSolution"])){
            $dataSolutionAdd["UF_SOLUTION"] = $solution["descriptionSolution"];
        }
        $resultSolution = $smartFormSolutionHL ::update($key, $dataSolutionAdd);
        unset($dataSolutionAdd);
    }

    // обновление этапов
    foreach ($dataStagesAdd as $key => $stages){

        if (!empty($stages["descriptionStages"])){
            $dataStagesAdd["UF_STAGES"] = $stages["descriptionStages"];
        }
        if (!empty($stages["executorStages"])){
            $dataStagesAdd["UF_EXECUTOR"] = $stages["executorStages"];
        }
        if (!empty($stages["decisionDateStages"])){
            $dataStagesAdd["UF_DECISION_DATE"] = date("d.m.Y", strtotime($stages["decisionDateStages"]));
        }
        if (!empty($stages["explanationStages"])){
            $dataStagesAdd["UF_EXPLANATION"] = $stages["explanationStages"];
        }
        if (!empty($stages["changeDateStages"])){
            $dataStagesAdd["UF_CHANGE_DATE"] = date("d.m.Y", strtotime($stages["changeDateStages"]));
        }
        if (!empty($stages["closingDateStages"])){
            $dataStagesAdd["UF_CLOSING_DATE"] = date("d.m.Y", strtotime($stages["closingDateStages"]));
        }
        if (!empty($stages["statusStages"])){
            $dataStagesAdd["UF_STATUS"] = $stages["statusStages"];
        }

        $resultStages = $smartFormStagesHL ::update($key, $dataStagesAdd);
        unset($dataStagesAdd);
    }

    echo json_encode([
        'status' => 'success',
        'text' => 'Проблема обновлена',
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'error_text' => 'Ошибка, обновите страницу',
    ]);
}
