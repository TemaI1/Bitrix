<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><? $APPLICATION -> ShowTitle() ?></title>

		<?
		$APPLICATION -> ShowHead();
		\Bitrix\Main\Page\Asset ::getInstance() -> addCss(SITE_TEMPLATE_PATH . '/assets/css/style.css');
		\Bitrix\Main\Page\Asset ::getInstance() -> addJs(SITE_TEMPLATE_PATH . "/assets/js/script.js");
		?>
</head>
<body>

<?

//$APPLICATION -> ShowPanel();

// получение объекта сущности highloadblock
CModule ::IncludeModule('highloadblock');
function getHBbyName($code){
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList([
        'filter' => ['=TABLE_NAME' => $code]
    ])->fetch();
    $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock))->getDataClass();
    return $entity_data_class;
}
$HLVote = getHBbyName("b_hlbd_vote_main");
$HLAnswersVote = getHBbyName("b_hlbd_vote_answers");
$HLQuestionsOwnVote = getHBbyName("b_hlbd_vote_questions_own");

?>

<div class="header center">

	<div class="header-logo">
		<a href="<?= SITE_DIR ?>"><img class="header-logo-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/logo-20.svg" alt="logo"></a>
		<h1 class="header-logo-title">Опрос сотрудников организации</h1>
	</div>

	<hr>

	<div class="header-menu">
		<?$APPLICATION->IncludeComponent("bitrix:menu","top_menu",Array(
				"ROOT_MENU_TYPE" => "top",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "top",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "Y",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => ""
			)
		);?>
	</div>

	<hr>

</div>
