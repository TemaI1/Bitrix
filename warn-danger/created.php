<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION -> SetTitle("Созданные обращения");
?>

<div class="top-menu">
    <a href="/warn-danger/">Создать обращение</a>
</div>

<hr>

<?
$warnDangerHL = getHLBTClass(15);
$rsWarnDanger = $warnDangerHL ::getList(
    [
        "select" => ["*"],
        "order"  => ["ID" => "ASC"],
        "filter" => ["*"],
    ]
);
?>

<?
while ($arWarnDanger = $rsWarnDanger -> Fetch()){

	// получение пользователя
    $resUsers = \Bitrix\Main\UserTable ::getList([
        'filter' => [
            'ID' => $arWarnDanger["UF_USERS"],
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

    ?><div class="notice-result-info"><?
    ?><p class="danger-text"><span>Cтруктурное подразделение: </span><? print_r($arWarnDanger["UF_DEPARTMEN"]) ?></p><?
    ?><p class="danger-text"><span>Адрес: </span><? print_r($arWarnDanger["UF_ADDRES"]) ?></p><?
    ?><p class="danger-text"><span>ФИО: </span><? print_r(rtrim($users, ", ")) ?></p><?
    ?><p class="danger-text"><span>Категория: </span><? print_r($arWarnDanger["UF_CATEGORY"]) ?></p><?
    ?><p class="danger-text"><span>Дата и время: </span><? print_r(date("d.m.Y H:i:s", strtotime($arWarnDanger["UF_DATETIME"]))) ?></p><?
    ?><p class="danger-text"><span>Описание: </span><? print_r($arWarnDanger["UF_DESCRIPTION"]) ?></p><?
    ?><p class="danger-text"><span>Меры: </span><? print_r($arWarnDanger["UF_MEASURES"]) ?></p><?
    ?></div><?
    ?><hr><?
}
?>

<style>

    .top-menu{
        margin: 20px;
    }

    .top-menu a{
        background-color: #f8f8f8;
        color: #303F49;
        font-size: 19px;
        font-family: sans-serif;
        padding: 10px 20px;
        border: 1px solid #686868;
        border-radius: 7px;
        cursor: pointer;
    }

    .top-menu a:hover{
        background-color: #e5e5e5;
        transition: 0.3s;
    }

    .danger-text span{
        margin: 0;
        padding: 0;
        color: #2F6BA9;
        font-size: 14px;
        font-family: sans-serif;
        font-weight: bold;
    }

    .danger-text {
        margin: 10px;
        padding: 0;
        color: #303F49;
        font-size: 14px;
        font-family: sans-serif;
    }

</style>

<script>

</script>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
