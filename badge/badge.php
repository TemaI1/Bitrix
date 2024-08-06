<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('STOP_STATISTICS', true);
define('NO_AGENT_STATISTIC', 'Y');
define('BX_SECURITY_SHOW_MESSAGE', true);

global $APPLICATION;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use \PhpOffice\PhpWord;
use \PhpOffice\PhpWord\Style;
use \PhpOffice\PhpWord\SimpleType;
use \PhpOffice\PhpWord\Shared\Converter;
use \PhpOffice\PhpWord\IOFactory;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

$id = [];
$id = str_replace("U", "", $post['USERS']);

//создание документа, шрифта и размера
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord -> setDefaultFontName('Franklin Gothic Demi Cond');
$phpWord -> setDefaultFontSize(2);

//стиль для документа
$sectionStylePhoto = [
    'breakType' => 'continuous',
    'colsNum'   => 2,
    'colsSpace' => 300,
    'marginBottom' => 1000,
    'marginTop' => 500,
    'marginLeft' => 0,
    'marginRight' => 100,
];
$sectionStyleText = [
    'breakType' => 'nextColumn',
    'colsNum'   => 2,
    'colsSpace' => 300,
    'marginBottom' => 1000,
    'marginTop' => 500,
    'marginLeft' => 0,
    'marginRight' => 100,
];

//создание линейного элемента
$lineStyle = ['weight' => 1, 'width' => 20000, 'height' => 0];

//фильтруем полученного пользователя
$rs = \Bitrix\Main\UserTable ::getList([
    'filter' => [
        '=ID' => $id,
    ],
    'select' => [
        'ID',
        'PERSONAL_PHOTO',
        'WORK_DEPARTMENT',
        'LAST_NAME',
        'NAME',
        'SECOND_NAME',
        'WORK_POSITION',
        'WORK_PHONE',
    ],
]);

//создаем массив полученных пользователей
$allUser = [];
while ($arUser = $rs -> fetch()) {
    $allUser[] = $arUser;
}

//перебираем массив пользователей
foreach ($allUser as $userInfo) {
    //создание секций
    $sectionPhoto = $phpWord -> addSection($sectionStylePhoto);
    $sectionText = $phpWord -> addSection($sectionStyleText);

    //путь к фото
    $photoPath = CFile ::GetPath($userInfo['PERSONAL_PHOTO']);

    //создание пустой таблицы для отступов
    $table = $sectionText -> addTable();
    $table -> addRow(500, []);
    $cell = $table -> addCell(100, []);

    //заполнение документа
    $sectionPhoto -> addImage(
        $_SERVER['DOCUMENT_ROOT'] . '/images/logo.png',
        ['width' => 160, 'height' => 45, 'align' => 'right']
    );
    if(!empty($photoPath)){
        $sectionPhoto -> addImage(
            $_SERVER['DOCUMENT_ROOT'] . $photoPath,
            ['width' => 160, 'height' => 170, 'align' => 'right']
        );
    } else {
        $table2 = $sectionPhoto -> addTable();
        $table2 -> addRow(3600, []);
        $cell2 = $table2 -> addCell(100, []);
    }
    $sectionText -> addText(
        htmlspecialchars($userInfo['WORK_DEPARTMENT']),
        ['size' => 16, 'bold' => true, 'allCaps' => true],
        []
    );
    $sectionText -> addTextBreak();
    $sectionText -> addText(
        htmlspecialchars($userInfo['LAST_NAME']),
        ['size' => 26, 'bold' => true, 'allCaps' => true],
        []
    );
    $sectionText -> addText(
        htmlspecialchars($userInfo['NAME'] . " " . $userInfo['SECOND_NAME']),
        ['size' => 26, 'bold' => true],
        []
    );
    $sectionText -> addText(
        htmlspecialchars($userInfo['WORK_POSITION']),
        ['size' => 18, 'bold' => true],
        []
    );
    $sectionText -> addTextBreak();
    $sectionText -> addText(
        htmlspecialchars("Тел: " . $userInfo['WORK_PHONE']),
        ['size' => 16, 'bold' => true],
        []
    );
    $sectionText -> addTextBreak();
    $sectionPhoto -> addTextBreak();
    $sectionPhoto -> addLine($lineStyle);
}

//отправка документа
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="Карточка сотрудника.docx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$writer = \PhpOffice\PhpWord\IOFactory ::createWriter($phpWord, 'Word2007');
$writer -> save('php://output');
exit;
