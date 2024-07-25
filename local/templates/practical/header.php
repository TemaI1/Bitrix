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
$APPLICATION -> ShowPanel(); ?>
<div class="top center">

	<div class="top-header">
		<div class="top-header-left">
			<img class="top-header-left-logo" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/logo.svg" alt="logo">
			<p class="top-header-left-text">Р у м т и б е т</p>
		</div>
		<div class="top-header-right">
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
			<button class="top-header-right-btn">Консультация</button>
		</div>
	</div>

	<div class="top-footer">
		<h1 class="top-footer-title">Насладись прогулкой в горах
			с командой единомышленников</h1>
		<form id="top-form" class="top-footer-form" action="#">
			<div>
				<select class="top-footer-select" name="location" form="top-form">
					<option value="empty">Локация для тура</option>
					<option value="Макалу">Макалу</option>
					<option value="Фудзияма">Фудзияма</option>
				</select>
				<p class="top-footer-text">выберите из списка</p>
			</div>
			<div>
				<input class="top-footer-date" type="date" name="date">
				<p class="top-footer-text">дата похода</p>
			</div>
			<div>
				<select class="top-footer-select" name="peoples" form="top-form">
					<option value="empty">Участники</option>
					<option value="2">2 человек</option>
					<option value="3">3 человек</option>
					<option value="4">4 человек</option>
				</select>
				<p class="top-footer-text">минимум 2 человека</p>
			</div>
			<input class="top-footer-btn" type="submit" value="Найти программу">
		</form>
	</div>

</div>
