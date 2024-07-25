<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Блог");
?>

<div class="content-blog center">

	<div class="content-blog-text">
		<p class="content-blog-text-subtitle">делимся впечатлениями</p>
		<h2 class="content-blog-text-title">Блог о путешествиях</h2>
	</div>

	<div class="content-blog-card">
		<div class="content-blog-card-item">
			<img class="content-blog-card-item-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/blog-card-item.png"
				 alt="img">
			<div class="content-blog-card-item-right">
				<h3 class="content-blog-card-item-right-title">Красивая Италия, какая она в реальности?</h3>
				<p class="content-blog-card-item-right-subtitle">Для современного мира базовый вектор развития
					предполагает независимые способы реализации соответствующих условий активизации.</p>
				<div class="content-blog-card-item-right-info">
					<p class="content-blog-card-item-right-info-date">01/04/2023</p>
					<p class="content-blog-card-item-right-info-read">читать статью</p>
				</div>
			</div>
		</div>
		<div class="content-blog-card-item">
			<img class="content-blog-card-item-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/blog-card-item2.png"
				 alt="img">
			<div class="content-blog-card-item-right">
				<h3 class="content-blog-card-item-right-title">Как подготовиться к путешествию в одиночку? </h3>
				<p class="content-blog-card-item-right-subtitle">Для современного мира базовый вектор развития
					предполагает.</p>
				<div class="content-blog-card-item-right-info">
					<p class="content-blog-card-item-right-info-date">01/04/2023</p>
					<p class="content-blog-card-item-right-info-read">читать статью</p>
				</div>
			</div>
		</div>
		<div class="content-blog-card-item">
			<img class="content-blog-card-item-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/blog-card-item3.png"
				 alt="img">
			<div class="content-blog-card-item-right">
				<h3 class="content-blog-card-item-right-title">Долой сомнения! Весь мир открыт для вас!</h3>
				<p class="content-blog-card-item-right-subtitle">Для современного мира базовый вектор развития
					предполагает независимые способы реализации соответствующих условий активизации, независимые способы
					реализации соответствующих условий активизации.</p>
				<div class="content-blog-card-item-right-info">
					<p class="content-blog-card-item-right-info-date">01/04/2023</p>
					<p class="content-blog-card-item-right-info-read">читать статью</p>
				</div>
			</div>
		</div>
		<div class="content-blog-card-item">
			<img class="content-blog-card-item-img" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/blog-card-item4.png"
				 alt="img">
			<div class="content-blog-card-item-right">
				<h3 class="content-blog-card-item-right-title">Индия ... летим?</h3>
				<p class="content-blog-card-item-right-subtitle">Для современного мира базовый.</p>
				<div class="content-blog-card-item-right-info">
					<p class="content-blog-card-item-right-info-date">01/04/2023</p>
					<p class="content-blog-card-item-right-info-read">читать статью</p>
				</div>
			</div>
		</div>
	</div>

	<button class="content-blog-btn">Другие материалы</button>

</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
