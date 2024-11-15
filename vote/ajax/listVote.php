<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> toArray();

// получение объекта сущности highloadblock
CModule ::IncludeModule('highloadblock');
function getHBbyName($code)
{
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable ::getList([
        'filter' => ['=TABLE_NAME' => $code],
    ]) -> fetch();
    $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable ::compileEntity($hlblock)) -> getDataClass();
    return $entity_data_class;
}

$HLVote = getHBbyName("b_hlbd_vote_main");
$HLAnswersVote = getHBbyName("b_hlbd_vote_answers");
$HLQuestionsOwnVote = getHBbyName("b_hlbd_vote_questions_own");

// получение значения поля у записи highloadblock
$resHLVote = $HLVote ::getList([
    "select" => ["*"],
    "order"  => ["ID" => "DESC"],
    "filter" => ["ID" => $post["voteId"]],
]);
$rsVote = $resHLVote -> Fetch();

// получение значения поля у записи highloadblock
$resHLAnswersVote = $HLAnswersVote ::getList([
    "select" => ["*"],
    "order"  => ["ID" => "DESC"],
    "filter" => ["ID" => $rsVote["UF_VOTING_ANSWERS"]],
]);

//проверка на пустые inputs
$checkEmpty = 1;
foreach ($post as $key => $value) {
    if (empty($value)) {
        $checkEmpty = 0;
    }
}

// формируем массив для проверки пустых ответов
$arrAnswerVote = [];
while ($rsAnswersVote = $resHLAnswersVote -> Fetch()) {
    $arrAnswerVote[] = $rsAnswersVote["ID"];
}
// формируем массив для проверки пустых ответов
$arrUserVote = [];
foreach ($post as $key => $value) {
    if (strpos($key, 'question') !== false) {
        $question = explode("-", preg_replace("/[^-0-9]/", '', $key));
        $arrUserVote[] = $question[0];
    }
}

if ($checkEmpty) {
    // выполнение кода, если ответ дан на все вопросы (кроме собственных)
    if (empty(array_diff($arrAnswerVote, $arrUserVote))) {
        //формируем список голосов
        $voteCount = [];
        foreach ($post as $key => $value) {
            if (strpos($key, 'question') !== false) {
                if (strpos($key, 'radioquestion') !== false) {
                    // получение значения поля у записи highloadblock
                    $resHLAnswersVote = $HLAnswersVote ::getList([
                        "select" => ["UF_ANSWERS"],
                        "order"  => ["ID" => "DESC"],
                        "filter" => ["ID" => preg_replace("/[^-0-9]/", '', $key)],
                    ]);
                    $rsAnswersVote = $resHLAnswersVote -> Fetch();

                    // [ответ ID] - варианты ответов
                    $questionAnswer = explode(
                        "-",
                        preg_replace("/[^-0-9]/", '', $key) . "-" . array_search($value, $rsAnswersVote["UF_ANSWERS"])
                    );
                    $voteCount[$questionAnswer[0]][] = $questionAnswer[1];
                } else {
                    // [ответ ID] - варианты ответов
                    $questionAnswer = explode("-", preg_replace("/[^-0-9]/", '', $key));
                    $voteCount[$questionAnswer[0]][] = $questionAnswer[1];
                }
            }
        }

        //записываем полученные голоса в highloadblock(UF_COUNT_VOTES)
        foreach ($voteCount as $key => $value) {
            // получение значения поля у записи highloadblock
            $resHLAnswersVote = $HLAnswersVote ::getList([
                "select" => ["UF_COUNT_VOTES", "UF_ANSWER_GIVEN"],
                "order"  => ["ID" => "DESC"],
                "filter" => ["ID" => $key],
            ]);
            $rsAnswersVote = $resHLAnswersVote -> Fetch();


            //добавляем полученные голоса в highloadblock(UF_COUNT_VOTES)
            $listRespondents = $rsAnswersVote["UF_ANSWER_GIVEN"];
            foreach ($listRespondents as $listKey => $listValue) {
                if (in_array($listKey, $value)) {
                    $listRespondents[$listKey] .= $post["userId"] . ", ";
                }
            }

            //добавляем полученные голоса в highloadblock(UF_COUNT_VOTES)
            $currentCount = $rsAnswersVote["UF_COUNT_VOTES"];
            foreach ($currentCount as $currentKey => $currentValue) {
                if (in_array($currentKey, $value)) {
                    $currentCount[$currentKey] += 1;
                }
            }
            $dataAdd = [
                "UF_COUNT_VOTES"  => $currentCount,
                "UF_ANSWER_GIVEN" => $listRespondents,
            ];
            $result = $HLAnswersVote ::update($key, $dataAdd);
        }

        //формируем список ответ
        $voteAnswer = [];
        //формируем список вопросов
        $voteQuestion = [];

        //выполнение кода, если ответ дан на вопросы (собственные)
        foreach ($post as $key => $value) {
            if (strpos($key, 'quOwnName') !== false) {
                $questionOwn = preg_replace("/[^-0-9]/", '', $key);
                $voteAnswer[$questionOwn] = $value;
            }
        }

        //перебираем список ответ
        foreach ($voteAnswer as $key => $value) {
            $answersOwn = [];
            $answersOwnUser = [];

            // получение значения поля у записи highloadblock
            $resHLQuestionsOwnVote = $HLQuestionsOwnVote ::getList([
                "select" => ["UF_ANSWERS_OWN", "UF_ANSWER_OWN_GIVEN"],
                "order"  => ["ID" => "DESC"],
                "filter" => ["ID" => $key],
            ]);
            $rsQuestionsOwnVote = $resHLQuestionsOwnVote -> Fetch();

            $answersOwn = array_merge($rsQuestionsOwnVote["UF_ANSWERS_OWN"], $answersOwn);
            $answersOwn[] = $value;

            $answersOwnUser = array_merge($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"], $answersOwnUser);
            $answersOwnUser[] = $post["userId"];

            //добавляем полученные ответы в highloadblock(UF_ANSWERS_OWN)
            $dataAdd = [
                "UF_ANSWERS_OWN"      => $answersOwn,
                "UF_ANSWER_OWN_GIVEN" => $answersOwnUser,
            ];
            $result = $HLQuestionsOwnVote ::update($key, $dataAdd);

            $voteQuestion[] = $key;
        }

        //добавляем полученные ответы в highloadblock(UF_ANSWERS_OWN)
        if (!empty($voteQuestion)) {
            $dataAdd = [
                "UF_VOTING_ANSWERS_OWN_GIVEN" => $voteQuestion,
                "UF_VOTING_ANSWERS_OWN"       => $voteQuestion,
            ];
            $result = $HLVote ::update($rsVote["ID"], $dataAdd);
        }

        echo json_encode("response received");
    }
}




