<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Направления");
?>

<div class="content-rating center">

	<div class="content-rating-text">
		<p class="content-rating-text-subtitle">по версии отдыхающих</p>
		<h2 class="content-rating-text-title">Популярные направления</h2>
	</div>

	<div class="content-rating-card center">
		<div class="content-rating-card-item content-rating-card-item-first">
			<div class="content-rating-card-item-rating">
				<img class="content-rating-card-item-rating-icon"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/item-star.svg" alt="img">
				<p class="content-rating-card-item-rating-count">4.9</p>
			</div>
			<div class="content-rating-card-item-info">
				<div>
					<h3 class="content-rating-card-item-info-title">Озеро возле гор</h3>
					<p class="content-rating-card-item-info-subtitle">романтическое приключение</p>
				</div>
				<p class="content-rating-card-item-info-price">480 $</p>
			</div>
		</div>
		<div class="content-rating-card-item content-rating-card-item-second">
			<div class="content-rating-card-item-rating">
				<img class="content-rating-card-item-rating-icon"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/item-star.svg" alt="img">
				<p class="content-rating-card-item-rating-count">4.5</p>
			</div>
			<div class="content-rating-card-item-info">
				<div>
					<h3 class="content-rating-card-item-info-title">Ночь в горах</h3>
					<p class="content-rating-card-item-info-subtitle">в компании друзей</p>
				</div>
				<p class="content-rating-card-item-info-price">500 $</p>
			</div>
		</div>
		<div class="content-rating-card-item content-rating-card-item-third">
			<div class="content-rating-card-item-rating">
				<img class="content-rating-card-item-rating-icon"
					 src="<?= SITE_TEMPLATE_PATH ?>/assets/img/item-star.svg" alt="img">
				<p class="content-rating-card-item-rating-count">4.8</p>
			</div>
			<div class="content-rating-card-item-info">
				<div>
					<h3 class="content-rating-card-item-info-title">Йога в горах</h3>
					<p class="content-rating-card-item-info-subtitle">для тех, кто забоится о себе</p>
				</div>
				<p class="content-rating-card-item-info-price">230 $</p>
			</div>
		</div>
	</div>

	<button class="content-rating-btn">Рейтинг направлений</button>

</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
