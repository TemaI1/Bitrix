<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {die();} ?>

<? if (!empty($arResult)): ?>
	<div class="top-header-right">
        <? foreach ($arResult as $arItem): ?>
			<a href="<?= $arItem["LINK"] ?>" class="top-header-right-link"><?= $arItem["TEXT"] ?></a>
        <? endforeach ?>
	</div>
<? endif ?>
