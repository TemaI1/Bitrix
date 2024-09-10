<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

//получаем список почт пользователей
$by="id"; //поле для сортировки (сортировка по id)
$order="asc"; //порядок сортировки (сортировка asc - по возрастанию)
$filter = ["ID" => "5700 | 3200"]; //необязательный массив для фильтрации (ID пользователя)
$rsUsers = CUser::GetList($by, $order, $filter); //возвращает список пользователей в виде объекта
$listMail = ""; //группа для отправки уведомлений
while ($arRes = $rsUsers->Fetch()){
    $listMail .= $arRes["EMAIL"] . ", ";
}

// получение объекта сущности highloadblock
$hlBlockID = 1;
$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlBlockID)->fetch();
$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$HLQuestion = $entity->getDataClass();

//создание поля highloadblock, отправка уведомления (создании)
if (!empty($post["questionText"]) && !empty($post["questionName"]) && !empty($post["questionFioName"]) && !empty($post["questionFioId"])) {
    // выборка из highloadblock
    $dataAdd = [
        "UF_QUESTION"          => $post["questionText"],
        "UF_QUESTION_TOPICS"   => $post["questionName"],
        "UF_QUESTION_STATUSES" => 1, // ID значения списка (создан)
        "UF_QUESTION_CREATOR"  => $post["questionFioId"],
        "UF_ANSWER"            => "Ожидает ответа",
        "UF_DATE_ANSWER"       => $post["questionDate"],
        "UF_HISTORY_QUESTION"  => $post["questionFioName"] . ", вопрос создан " . $post["questionDate"] . ": " . $post["questionText"],
    ];
    $result = $HLQuestion ::add($dataAdd);

    // массив полей типа почтового события
    $arrEventField = [
        "QUESTION"         => $post["questionText"],
        "QUESTION_CREATOR" => getUserFullName($post["questionFioId"]),
        "LIST_EMAIL"       => $listMail,
    ];

    // создаем почтовое событие
    CEvent ::Send("CREATE_QUESTION", "s1", $arrEventField, "N", 171);
}

//добавление ответа и ответившего в поле highloadblock, отправка уведомления (ответ)
if (!empty($post["idAnswer"]) && !empty($post["questionGiveFioId"]) && !empty($post["questionGiveFioName"]) && !empty($post["questionGiveText"])) {
    // получение значения поля у записи highloadblock
    $resHLQuestion = $HLQuestion ::getList([
        "select" => [
            "ID",
            "UF_QUESTION",
            "UF_QUESTION_CREATOR",
            "UF_HISTORY_QUESTION",
            "UF_QUESTION_CREATOR",
            "UF_DATE_ANSWER",
        ],
        "order"  => ["ID" => "DESC"],
        "filter" => ["ID" => $post["idAnswer"]],
    ]);
    $resultHistoryQuestion = "";
    $arr = $resHLQuestion -> Fetch();
    $resultHistoryQuestion = $arr["UF_HISTORY_QUESTION"];

    // выборка из highloadblock
    $dataAdd = [
        "UF_QUESTION_STATUSES" => 2, // ID значения списка (получен ответ)
        "UF_ANSWER"            => $post["questionGiveText"],
        "UF_ANSWER_CREATOR"    => $post["questionGiveFioId"],
        "UF_DATE_ANSWER"       => "",
        "UF_HISTORY_QUESTION"  => $resultHistoryQuestion . "\n \n" . $post["questionGiveFioName"] . ", ответ получен " . $post["questionGiveDate"] . ": " . $post["questionGiveText"],
    ];
    $result = $HLQuestion ::update($post["idAnswer"], $dataAdd);

    // создатель вопроса
    $userCreator = CUser ::GetByID($arr["UF_QUESTION_CREATOR"]) -> Fetch();

    // массив полей типа почтового события
    $arrEventField = [
        "ID_QUESTION"            => $arr["ID"],
        "QUESTION"               => $arr["UF_QUESTION"],
        "QUESTION_CREATOR"       => getUserFullName($arr["UF_QUESTION_CREATOR"]),
        "QUESTION_CREATOR_EMAIL" => $userCreator["EMAIL"],
        "ANSWER_CREATOR"         => getUserFullName($post["questionGiveFioId"]),
        "ANSWER"                 => $post["questionGiveText"],
        "HISTORY_QUESTION"       => $resultHistoryQuestion . "\n \n" . $post["questionGiveFioName"] . ", ответ получен " . $post["questionGiveDate"] . ": " . $post["questionGiveText"],
    ];

    // создаем почтовое событие
    CEvent ::Send("ANSWER_TO_QUESTION", "s1", $arrEventField, "N", 170);
}

//добавление дополнительного вопроса в поле highloadblock, отправка уведомления (уточнение)
if (!empty($post["idQuestion"]) && !empty($post["clarifyQuestionText"]) && !empty($post["clarifyQuestionFioId"]) && !empty($post["clarifyQuestionFioName"])) {
    // получение значения поля у записи highloadblock
    $resHLQuestion = $HLQuestion ::getList([
        "select" => ["ID", "UF_HISTORY_QUESTION",],
        "order"  => ["ID" => "DESC"],
        "filter" => ["ID" => $post["idQuestion"]],
    ]);
    $resultHistoryQuestion = "";
    $arr = $resHLQuestion -> Fetch();
    $resultHistoryQuestion = $arr["UF_HISTORY_QUESTION"];

    // выборка из highloadblock
    $dataAdd = [
        "UF_QUESTION"          => $post["clarifyQuestionText"],
        "UF_QUESTION_STATUSES" => 3, // ID значения списка (требуется разъяснение)
        "UF_QUESTION_CREATOR"  => $post["clarifyQuestionFioId"],
        "UF_ANSWER"            => "Ожидает ответа",
        "UF_DATE_ANSWER"       => $post["clarifyQuestionDate"],
        "UF_HISTORY_QUESTION"  => $resultHistoryQuestion . "\n \n" . $post["clarifyQuestionFioName"] . ", уточняющий вопрос " . $post["clarifyQuestionDate"] . ": " . $post["clarifyQuestionText"],
    ];
    $result = $HLQuestion ::update($post["idQuestion"], $dataAdd);

    // массив полей типа почтового события
    $arrEventField = [
        "QUESTION"         => $post["clarifyQuestionText"],
        "QUESTION_CREATOR" => getUserFullName($post["clarifyQuestionFioId"]),
        "LIST_EMAIL"       => $listMail,
        "HISTORY_QUESTION" => $resultHistoryQuestion . "\n \n" . $post["clarifyQuestionFioName"] . ", уточняющий вопрос " . $post["clarifyQuestionDate"] . ": " . $post["clarifyQuestionText"],
    ];

    // создаем почтовое событие
    CEvent ::Send("CREATE_QUESTION", "s1", $arrEventField, "N", 172);
}

//завершение вопроса в поле highloadblock
if (!empty($post["idCloseQuestion"])) {
    // выборка из highloadblock
    $dataAdd = [
        "UF_QUESTION_STATUSES" => 4, // ID значения списка (закрыт)
    ];
    $result = $HLQuestion ::update($post["idCloseQuestion"], $dataAdd);
    echo json_encode($post["idCloseQuestion"]);
}

//вывод истории вопроса
if (!empty($post["idHistoryQuestion"])) {
    // получение значения поля у записи highloadblock
    $resHLQuestion = $HLQuestion ::getList([
        "select" => ["ID", "UF_HISTORY_QUESTION",],
        "order"  => ["ID" => "DESC"],
        "filter" => ["ID" => $post["idHistoryQuestion"]],
    ]);
    $resultHistoryQuestion = "";
    $arr = $resHLQuestion -> Fetch();
    $resultHistoryQuestion = $arr["UF_HISTORY_QUESTION"];

    if (!empty($resultHistoryQuestion)) {
        echo json_encode($resultHistoryQuestion);
    } else {
        echo json_encode("История вопроса отсуствует");
    }
}
