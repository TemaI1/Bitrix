<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION;
global $USER;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

if (!empty($post["department"]) && !empty($post["USERS"]) && !empty($post["category"]) && !empty($post["date"]) && !empty($post["description"])){

    //создаем запись в HL-блоке
    $warnDangerHL = getHLBTClass(15);
    $dataAdd = [
        "UF_DEPARTMEN"  => $post["department"],
        "UF_ADDRES" => $post["addres"],
        "UF_USERS" => str_replace("U", "", $post['USERS']),
        "UF_CATEGORY" => $post["category"],
        "UF_DATETIME" => date("d.m.Y H:i:s", strtotime($post["date"])),
        "UF_DESCRIPTION" => $post["description"],
        "UF_MEASURES" => $post["measures"],
    ];
    $result = $warnDangerHL ::add($dataAdd);


    // получение пользователя
    $resUsers = \Bitrix\Main\UserTable ::getList([
        'filter' => [
            'ID' => str_replace("U", "", $post['USERS']),
        ],
        'select' => [
            'ID',
            'LAST_NAME',
            'NAME',
            'SECOND_NAME',
        ],
        'order' => ['id' => 'ASC'],
    ]);
    $users = "";
    while ($arUser = $resUsers -> fetch()) {
        $arUser['FULL_NAME'] = \Tools\Helper ::getUserFullName(
            $arUser['LAST_NAME'],
            $arUser['NAME'],
            $arUser['SECOND_NAME']
        );
        $users .=  $arUser["FULL_NAME"] . ", ";
    }

    //отправить уведомление
    $arrEventField = [
        "FOR_WHOM" => "test@mail.ru",
        "TOPIC"    => "Уведомление по созданному обращению",
        "TEXT"     => "Cтруктурное подразделение: " . $post["department"] .
            "<br>Адрес: " . $post["addres"] .
            "<br>ФИО: " . rtrim($users, ", ") .
            "<br>Категория: " . $post["category"] .
            "<br>Дата и время: " . date("d.m.Y H:i:s", strtotime($post["date"])) .
            "<br>Описание: " . $post["description"] .
            "<br>Меры: " . $post["measures"]
        ,
    ];
    CEvent ::Send("REGULAR_LETTER", "s1", $arrEventField, "N", 270);

    echo json_encode(array(
        'status' => 'success',
        'text' => 'Спасибо за обращение',
    ));

} else {
    echo json_encode(array(
        'status' => 'error',
        'error_text' => 'Ошибка, проверьте входные данные',
    ));
}
