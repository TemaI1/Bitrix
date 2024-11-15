<?php

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Bitrix\Main\Entity;

\Bitrix\Main\Loader ::IncludeModule("highloadblock"); // подключение модуля
global $APPLICATION;

// получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> toArray();


$xls = new PHPExcel();

// Установка сводки документа:
$xls->getProperties()->setTitle("Результат опроса");
$xls->getProperties()->setSubject("Опрос");

// Создаем новый лист
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle('Результат опроса');

// Установить стили шрифта для всего документа
$sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
$sheet->getDefaultStyle()->getFont()->setSize(12);

// Поля
$sheet->getPageMargins()->setTop(1);
$sheet->getPageMargins()->setRight(0.75);
$sheet->getPageMargins()->setLeft(0.75);
$sheet->getPageMargins()->setBottom(1);

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

$bg = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '2F4F4F')
    )
);

$bg2 = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'D3D3D3')
    )
);

$border = array(
    'borders'=>array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => 'a1a1a1')
        )
    )
);

$countCell = 1;
$sheet->setCellValue("A" . $countCell, $rsVote["UF_VOTING_TOPIC"]);
$sheet->mergeCells("A1:B1");
$sheet->getStyle('A1')->getFont()->setSize(14);
$sheet->getRowDimension("1")->setRowHeight(40);
$countCell++;

// получение значения поля у записи highloadblock
$resHLAnswersVote = $HLAnswersVote ::getList([
    "select" => ["*"],
    "order"  => ["ID" => "DESC"],
    "filter" => ["ID" => $rsVote["UF_VOTING_ANSWERS"]],
]);

while ($rsAnswersVot = $resHLAnswersVote -> Fetch()) {

    $sheet->setCellValue("A" . $countCell, "вопрос");
    $sheet->setCellValue("B" . $countCell, $rsAnswersVot["UF_QUESTIONS"]);
    $sheet->getStyle("A" . $countCell . ":B" . $countCell)->getFont()->getColor()->setRGB('ffffff');
    $sheet->getStyle("A" . $countCell . ":B" . $countCell)->applyFromArray($bg);
    $countCell++;

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
        $sheet->setCellValue("A"  . $countCell, "ответ");
        $sheet->setCellValue("B"  . $countCell, $value);
        $sheet->getStyle("A" . $countCell . ":B" . $countCell)->applyFromArray($bg2);
        $countCell++;

        foreach ($userList[$key] as $keyUser => $valueUser) {
            $allUsers .= $valueUser . ", ";
        }

        $sheet->setCellValue("A"  . $countCell, "список ответивших");
        $sheet->setCellValue("B"  . $countCell, $allUsers);
        $allUsers = "";
        $countCell++;

        $sheet->setCellValue("A"  . $countCell, "кол-во ответов");
        $sheet->setCellValue("B"  . $countCell, $countAnswer[$key]);
        $sheet->getStyle("B"  . $countCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $countCell++;
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

        $sheet->setCellValue("A"  . $countCell, "вопрос");
        $sheet->setCellValue("B"  . $countCell, $rsQuestionsOwnVote["UF_QUESTIONS_OWN"]);
        $sheet->getStyle("A" . $countCell . ":B" . $countCell)->getFont()->getColor()->setRGB('ffffff');
        $sheet->getStyle("A" . $countCell . ":B" . $countCell)->applyFromArray($bg);
        $countCell++;

        $userList = [];
        foreach ($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"] as $key => $value) {
            $rsUser = CUser::GetByID($value);
            $arUser = $rsUser->Fetch();
            $userList[] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"];
        }

        foreach ($rsQuestionsOwnVote["UF_ANSWERS_OWN"] as $key => $value) {

            $sheet->setCellValue("A"  . $countCell, "ответ");
            $sheet->setCellValue("B"  . $countCell, $value);
            $sheet->getStyle("A" . $countCell . ":B" . $countCell)->applyFromArray($bg2);
            $countCell++;

            $sheet->setCellValue("A"  . $countCell, "ответивший");
            $sheet->setCellValue("B"  . $countCell, $userList[$key]);
            $countCell++;
        }
    }
}

$sheet->setCellValue("A"  . $countCell, "дата завершения опроса: " . date("d.m.Y", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"])));
$sheet->mergeCells("A". $countCell . ":B" . $countCell);

$sheet->getColumnDimension("A")->setWidth(20);
$sheet->getColumnDimension("B")->setWidth(200);
$sheet->getStyle("A1" . ":B" . $countCell)->applyFromArray($border);
$sheet->getStyle("A1" . ":B" . $countCell)->getAlignment()->setWrapText(true);
$sheet->getStyle("A1" . ":A" . $countCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1" . ":A" . $countCell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getStyle("A"  . $countCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

// сохранение файла
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=Результат опроса.xlsx");
$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save('php://output');
exit();
