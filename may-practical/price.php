<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Стоимость");
?>

<div class="content-price center">

	<div class="content-price-left">
		<div>
			<p class="content-price-left-subtitle">наше предложение</p>
			<h2 class="content-price-left-title">Лучшие программы для тебя</h2>
		</div>
		<p class="content-price-left-text">Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть
			более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа.</p>
		<div class="content-price-left-box">
			<div class="content-price-left-box-left">
				<img class="content-price-left-box-left-img"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-left-img.svg" alt="img">
			</div>
			<div class="content-price-left-box-right">
				<h3 class="content-price-left-box-right-title">Опытный гид</h3>
				<p class="content-price-left-box-right-subtitle">Для современного мира базовый вектор развития
					предполагает независимые способы реализации соответствующих условий активизации.</p>
			</div>
		</div>
		<div class="content-price-left-box">
			<div class="content-price-left-box-left">
				<img class="content-price-left-box-left-img"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-left-img2.svg" alt="img">
			</div>
			<div class="content-price-left-box-right">
				<h3 class="content-price-left-box-right-title">Безопасный поход</h3>
				<p class="content-price-left-box-right-subtitle">Для современного мира базовый вектор развития
					предполагает независимые способы реализации соответствующих условий активизации.</p>
			</div>
		</div>
		<div class="content-price-left-box">
			<div class="content-price-left-box-left">
				<img class="content-price-left-box-left-img"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-left-img3.svg" alt="img">
			</div>
			<div class="content-price-left-box-right">
				<h3 class="content-price-left-box-right-title">Лояльные цены</h3>
				<p class="content-price-left-box-right-subtitle">Для современного мира базовый вектор развития
					предполагает независимые способы реализации соответствующих условий активизации.</p>
			</div>
		</div>
		<button class="content-price-left-btn">Стоимость программ</button>
	</div>

	<div class="content-price-right">
		<img class="content-price-right-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-right-img.png" alt="img">
		<img class="content-price-right-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-right-img2.png" alt="img">
		<img class="content-price-right-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-right-img3.png" alt="img">
		<img class="content-price-right-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/price-right-img4.png" alt="img">
	</div>

</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
