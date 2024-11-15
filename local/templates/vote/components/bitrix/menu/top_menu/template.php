<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {die();} ?>

<? if (!empty($arResult)): ?>
	<div class="header-menu-link">
        <? foreach ($arResult as $arItem): ?>

            <?if($arItem["SELECTED"]):?>
				<a href="<?=$arItem["LINK"]?>" class="header-menu-link-elem" style="color: #2F6BA9"><?=$arItem["TEXT"]?></a>
            <?else:?>
				<a href="<?= $arItem["LINK"] ?>" class="header-menu-link-elem"><?= $arItem["TEXT"] ?></a>
            <?endif?>

        <? endforeach ?>
	</div>
<? endif ?>
