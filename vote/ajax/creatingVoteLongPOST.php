<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> toArray();

$file = "longPOST.txt";

//создаем и добавляем POST в файл
if (!file_exists($file)) {
    $fp = fopen($file, "w"); // ("r" - считывать "w" - создавать "a" - добавлять к тексту)
    fwrite($fp, $post["elem"]);
    fclose($fp);
} else {
    $fp = fopen($file, "a"); // ("r" - считывать "w" - создавать "a" - добавлять к тексту)
    fwrite($fp, $post["elem"]);
    fclose($fp);
}

//проверяем содержит ли файл EXIT и создаем опрос подобно creatingVote.php
$fileContent = file_get_contents($file);
if (strpos($fileContent, "EXIT") !== false) {
    //удаляем EXIT из строки
    $fileContent = substr($fileContent,0,-4);
    //удаляем файл
    unlink($file);

    //получаем новый список POST
    parse_str($fileContent, $output);
    unset($post);
    $post = $output;

    //дублируем работу creatingVote.php
    $checkEmpty = 1;
    //проверка на отсутствие вопросов
    foreach ($post as $key => $value) {
        if ((strpos($key, 'questionName') !== false) || (strpos($key, 'quOwnName') !== false)) {
            $checkEmpty = 1;
            break;
        } else {
            $checkEmpty = 0;
        }
    }
    //проверка на пустые inputs
    foreach ($post as $key => $value) {
        if (empty($value)) {
            $checkEmpty = 0;
        }
    }

    if ($checkEmpty) {
        //формируем список вопросов и ответов
        $question = [];
        $questionKey = [];
        $questionOwn = [];
        $answers = [];
        $countAnswer = 0;
        foreach ($post as $keyQuestion => $valueQuestion) {
            if (strpos($keyQuestion, 'questionName') !== false) {
                $question[] = $valueQuestion;
                $questionKey[] = $keyQuestion;

                foreach ($post as $keyAnswer => $valueAnswer) {
                    if (strpos($keyAnswer, 'qu' . preg_replace("/[^,.0-9]/", '', $keyQuestion) . 'answerName') !== false) {
                        $answers[$countAnswer][] = $valueAnswer;
                    }
                }
                $countAnswer++;
            }

            if (strpos($keyQuestion, 'quOwnName') !== false) {
                $questionOwn[] = $valueQuestion;
            }
        }

        //определение множественного выбора
        $checkbox = [];
        foreach ($post as $keyCheckbox => $valueCheckbox) {
            if (strpos($keyCheckbox, 'checkboxName') !== false) {
                $checkbox[] = array_search(
                    preg_replace("/[^,.0-9]/", '', $keyCheckbox),
                    preg_replace("/[^,.0-9]/", '', $questionKey)
                );
            }
        }

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

        //создание вопросов (своих)
        foreach ($questionOwn as $key => $value) {
            $dataAdd = [
                "UF_QUESTIONS_OWN" => $value,
            ];
            $result = $HLQuestionsOwnVote ::add($dataAdd);
        }

        //создание ответов
        foreach ($answers as $key => $value) {
            if (in_array($key, $checkbox)) {
                $dataAdd = [
                    "UF_QUESTIONS"       => $question[$key],
                    "UF_ANSWERS"         => $value,
                    "UF_MULTIPLE_CHOICE" => 0,
                    "UF_COUNT_VOTES"     => array_fill(0, count($value), 0),
                    "UF_ANSWER_GIVEN"    => array_fill(0, count($value), ", "),
                ];
                $result = $HLAnswersVote ::add($dataAdd);
            } else {
                $dataAdd = [
                    "UF_QUESTIONS"       => $question[$key],
                    "UF_ANSWERS"         => $value,
                    "UF_MULTIPLE_CHOICE" => 1,
                    "UF_COUNT_VOTES"     => array_fill(0, count($value), 0),
                    "UF_ANSWER_GIVEN"    => array_fill(0, count($value), ", "),
                ];
                $result = $HLAnswersVote ::add($dataAdd);
            }
        }

        if (count($question) !== 0) {
            //формирование ID hl-блоков вопросов для добавления в опрос
            $resHLAnswersVote = $HLAnswersVote ::getList([
                "select" => ["*"],
                "order"  => ["ID" => "DESC"],
                "filter" => [],
                "limit"  => count($question),
            ]);
            $listQuestion = [];
            while ($rsVote = $resHLAnswersVote -> Fetch()) {
                $listQuestion[] = $rsVote["ID"];
            }
        } else {
            $listQuestion[] = [];
        }

        if (count($questionOwn) !== 0) {
            //формирование ID hl-блоков вопросов для добавления в опрос
            $resHLQuestionsOwnVote = $HLQuestionsOwnVote ::getList([
                "select" => ["*"],
                "order"  => ["ID" => "DESC"],
                "filter" => [],
                "limit"  => count($questionOwn),
            ]);
            $listQuestionOwn = [];
            while ($rsVote = $resHLQuestionsOwnVote -> Fetch()) {
                $listQuestionOwn[] = $rsVote["ID"];
            }
        } else {
            $listQuestionOwn[] = [];
        }

        if (count($question) !== 0) {
            //формирование ID hl-блоков ответ для добавления в опрос
            $resHLAnswersVote = $HLAnswersVote ::getList([
                "select" => ["*"],
                "order"  => ["ID" => "DESC"],
                "filter" => [],
                "limit"  => count($question),
            ]);
            $listAnswers = [];
            while ($rsVote = $resHLAnswersVote -> Fetch()) {
                $listAnswers[] = $rsVote["ID"];
            }
        } else {
            $listAnswers[] = [];
        }

        //создание опроса
        $dataAdd = [
            "UF_VOTING_TOPIC"           => $post["voteName"],
            "UF_VOTING_QUESTIONS"       => $listQuestion,
            "UF_VOTING_ANSWERS"         => $listAnswers,
            "UF_VOTING_ANSWERS_GIVEN"   => $listAnswers,
            "UF_VOTING_QUESTIONS_OWN"   => $listQuestionOwn,
            "UF_VOTING_COMPLETION_DATE" => date("d.m.Y", strtotime($post["completionDate"])),
            "UF_VIEWED_USER" => $post["VIEWED_USER"],
        ];
        $result = $HLVote ::add($dataAdd);
    }
}





