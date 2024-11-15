<?php

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use \PhpOffice\PhpWord;
use \PhpOffice\PhpWord\Style;
use \PhpOffice\PhpWord\SimpleType;
use \PhpOffice\PhpWord\Shared\Converter;
use \PhpOffice\PhpWord\IOFactory;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> toArray();

// работа с phpWord
PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);
$section = $phpWord->addSection(array(
    'orientation' => 'landscape',
    'marginLeft'   => 600,
    'marginRight'  => 600,
    'marginTop'    => 600,
    'marginBottom' => 600,
));

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

$section->addText($rsVote["UF_VOTING_TOPIC"], ['size' => 16, 'bold'=>true], ['spaceBefore' => 10]);

$styleTable = array('borderSize' => 6, 'borderColor' => '000000', 'cellMarginTop' => 150, 'cellMarginRight' => 100, 'cellMarginBottom' => 30, 'cellMarginLeft' => 100);
$cellRowContinue = array('vMerge' => 'continue');
$phpWord->addTableStyle('Colspan Rowspan', $styleTable);
$table = $section->addTable('Colspan Rowspan');

// получение значения поля у записи highloadblock
$resHLAnswersVote = $HLAnswersVote ::getList([
    "select" => ["*"],
    "order"  => ["ID" => "DESC"],
    "filter" => ["ID" => $rsVote["UF_VOTING_ANSWERS"]],
]);

while ($rsAnswersVot = $resHLAnswersVote -> Fetch()) {

    $table->addRow();
    $table->addCell(2000, ['bgColor' => '2F4F4F'])->addText("вопрос", ['color' => 'FFFFFF'], ['align' => 'center']);
    $table->addCell(15000, ['bgColor' => '2F4F4F'])->addText($rsAnswersVot["UF_QUESTIONS"], ['color' => 'FFFFFF'], ['align' => 'left']);

    $userList = [];
    foreach ($rsAnswersVot["UF_ANSWER_GIVEN"] as $key => $value) {
        foreach (explode(", ", $value) as $keyUser => $valueUser) {
            if ($valueUser != ""){
                $rsUser = CUser::GetByID($valueUser);
                $arUser = $rsUser->Fetch();
                $userList[$key][] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"];
            }
        }
    }

    $countAnswer = [];
    foreach ($rsAnswersVot["UF_COUNT_VOTES"] as $key => $value) {
        $countAnswer[] = $value;
    }

    $allUsers = "";
    foreach ($rsAnswersVot["UF_ANSWERS"] as $key => $value) {
        $table->addRow();
        $table->addCell(2000, ['bgColor' => 'D3D3D3'])->addText("ответ", null, ['align' => 'center']);
        $table->addCell(15000, ['bgColor' => 'D3D3D3'])->addText($value, null, ['align' => 'left']);

        foreach ($userList[$key] as $keyUser => $valueUser) {
            $allUsers .= $valueUser . ", ";
        }
        $table->addRow();
        $table->addCell(2000, ['bgColor' => 'FFFFFF'])->addText("список ответивших", null, ['align' => 'center']);
        $table->addCell(15000, ['bgColor' => 'FFFFFF'])->addText($allUsers, null, ['align' => 'left']);
        $allUsers = "";

        $table->addRow();
        $table->addCell(2000, [])->addText("кол-во ответов", null, ['align' => 'center']);
        $table->addCell(15000, [])->addText($countAnswer[$key], null, ['align' => 'left']);
    }
}



// получение значения поля у записи highloadblock
$resHLQuestionsOwnVote = $HLQuestionsOwnVote ::getList([
    "select" => ["*"],
    "order"  => ["ID" => "DESC"],
    "filter" => ["ID" => $rsVote["UF_VOTING_QUESTIONS_OWN"]],
]);

while ($rsQuestionsOwnVote = $resHLQuestionsOwnVote -> Fetch()) {
    if (!empty($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"])){

        $table->addRow();
        $table->addCell(2000, ['bgColor' => '2F4F4F'])->addText("вопрос", ['color' => 'FFFFFF'], ['align' => 'center']);
        $table->addCell(15000, ['bgColor' => '2F4F4F'])->addText($rsQuestionsOwnVote["UF_QUESTIONS_OWN"], ['color' => 'FFFFFF'], ['align' => 'left']);

        $userList = [];
        foreach ($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"] as $key => $value) {
            $rsUser = CUser::GetByID($value);
            $arUser = $rsUser->Fetch();
            $userList[] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"];
        }

        foreach ($rsQuestionsOwnVote["UF_ANSWERS_OWN"] as $key => $value) {
            $table->addRow();
            $table->addCell(2000, ['bgColor' => 'D3D3D3'])->addText("ответ", null, ['align' => 'center']);
            $table->addCell(15000, ['bgColor' => 'D3D3D3'])->addText($value, null, ['align' => 'left']);

            $table->addRow();
            $table->addCell(2000, ['bgColor' => 'FFFFFF'])->addText("ответивший", null, ['align' => 'center']);
            $table->addCell(15000, ['bgColor' => 'FFFFFF'])->addText($userList[$key], null, ['align' => 'left']);
        }
    }
}

$section->addText("дата завершения опроса: " . date("d.m.Y", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"])), ['size' => 12, 'bold'=>true], ['align' => 'right']);

// сохранение файла
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="Результат опроса.docx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$writer = \PhpOffice\PhpWord\IOFactory ::createWriter($phpWord, 'Word2007');
$writer -> save('php://output');
exit;


