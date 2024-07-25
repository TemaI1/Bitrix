<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Главная");
?>

<div class="content-info center">

	<div class="content-info-left">
		<img class="content-info-left-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/content-info-img.png" alt="img">
		<img class="content-info-left-img2" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/content-info-img2.png" alt="img">
	</div>

	<div class="content-info-right">
		<div>
			<p class="content-info-right-subtitle">о нашем походе</p>
			<h2 class="content-info-right-title">Исследуйте все горные массивы мира вместе с нами</h2>
		</div>
		<p class="content-info-right-text">Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть
			более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат
			Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur"и занялся его поисками в
			классической латинской литературе.</p>
		<button class="content-info-right-btn">Программа тура</button>
	</div>

</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
