<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {die();} ?>

<? if (!empty($arResult)): ?>
	<div class="top-header-right">
        <? foreach ($arResult as $arItem): ?>

            <?if($arItem["SELECTED"]):?>
				<a href="<?=$arItem["LINK"]?>" class="top-header-right-link" style="color: #1A3E3E"><?=$arItem["TEXT"]?></a>
            <?else:?>
				<a href="<?= $arItem["LINK"] ?>" class="top-header-right-link"><?= $arItem["TEXT"] ?></a>
            <?endif?>

        <? endforeach ?>
	</div>
<? endif ?>
