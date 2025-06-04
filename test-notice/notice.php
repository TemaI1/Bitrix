<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION;
global $USER;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

//$today = new DateTime();
//$yesterday = date("d.m.Y", strtotime("-1 days", time()));
//$tomorrow = date("d.m.Y", strtotime("+1 Weekday", time()));

if (!empty($post["USERS"]) && !empty($post["noticeDate"])){

    $usersId = [];
    $usersId = str_replace("U", "", $post['USERS']);

	//фильтруем полученных пользователей
    $rs = \Bitrix\Main\UserTable ::getList([
        'filter' => [
            '=ID' => $usersId,
        ],
        'select' => [
            'ID',
            'EMAIL',
            'LAST_NAME',
            'NAME',
            'SECOND_NAME',
        ],
    ]);

	//создаем список почт пользователей из формы и текущего
    $usersEmail = $USER->GetEmail();
    $usersName = "";
    while ($arUser = $rs -> fetch()) {
        $usersEmail .= ", " . $arUser["EMAIL"];
        $usersName .= $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"] . ", ";
    }

    //создаем запись в HL-блоке
    $testNoticeHL = getHLBTClass(13);
    $dataAdd = [
        "UF_USERS_EMAIL"   => $usersEmail,
        "UF_USERS_NAME"   => substr($usersName, 0, -2),
        "UF_DATE"   => date("d.m.Y", strtotime($post["noticeDate"])),
    ];
    $result = $testNoticeHL ::add($dataAdd);

    echo json_encode(array(
        'status' => 'success',
        'text' => 'Уведомление успешно создано',
    ));

} else {
    echo json_encode(array(
        'status' => 'error',
        'error_text' => 'Ошибка, проверьте входные данные',
    ));
}




