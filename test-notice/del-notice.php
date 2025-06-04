<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION;
use Bitrix\Main\Application;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["elemHL"])){

    //удаляем запись в HL-блоке
    $testNoticeHL = getHLBTClass(13);
    $result = $testNoticeHL ::Delete($post["elemHL"]);

    echo json_encode(array(
        'status' => 'success',
        'text' => 'Уведомление удалено',
    ));

} else {
    echo json_encode(array(
        'status' => 'error',
        'error_text' => 'Ошибка, обновите страницу',
    ));
}




