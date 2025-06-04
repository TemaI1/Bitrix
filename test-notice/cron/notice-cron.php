<?php
@set_time_limit(0);
if(!defined('NOT_CHECK_PERMISSIONS')) define('NOT_CHECK_PERMISSIONS', true);
if(!defined('NO_AGENT_CHECK')) define('NO_AGENT_CHECK', true);
if(!defined('BX_CRONTAB')) define("BX_CRONTAB", true);
if(!defined('ADMIN_SECTION')) define("ADMIN_SECTION", true);
if(!ini_get('date.timezone') && function_exists('date_default_timezone_set')){@date_default_timezone_set("Europe/Moscow");}
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
if(!array_key_exists('REQUEST_URI', $_SERVER)) $_SERVER["REQUEST_URI"] = substr(__FILE__, strlen($_SERVER["DOCUMENT_ROOT"]));
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);

use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Mail\Event;

$today = new DateTime();

$testNoticeHL = getHLBTClass(13);
$rsTestNotice = $testNoticeHL ::getList(
    [
        "select" => ["*"],
        "order"  => ["ID" => "ASC"],
        "filter" => ["*"],
    ]
);

$testNoticeList = [];
while ($arTestNotice = $rsTestNotice -> Fetch()){
    $testNoticeList[$arTestNotice["ID"]]["ID"] = $arTestNotice["ID"];
    $testNoticeList[$arTestNotice["ID"]]["UF_USERS_EMAIL"] = $arTestNotice["UF_USERS_EMAIL"];
    $testNoticeList[$arTestNotice["ID"]]["UF_DATE"] = $arTestNotice["UF_DATE"];
}

foreach ($testNoticeList as $elemHL){
	$countdown = (strtotime($elemHL["UF_DATE"]) - strtotime($today)) / (60*60*24);
	if ($countdown > 0 && $countdown < 5){
		//отправить уведомление
        $arrEventField = [
            "FOR_WHOM" => $elemHL["UF_USERS_EMAIL"],
            "TOPIC"    => "Уведомление по тестированию",
            "TEXT"     => "Необходимо пройти тестирование до: " . $elemHL["UF_DATE"],
        ];
        CEvent ::Send("REGULAR_LETTER", "s1", $arrEventField, "N", 270);
    }
	if (strtotime($elemHL["UF_DATE"]) < strtotime($today)){
        //удалить запись
        $result = $testNoticeHL ::Delete($elemHL['ID']);
    }
}





