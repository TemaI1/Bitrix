<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION -> SetTitle("Уведомление по тестированию");
CJSCore ::Init(["jquery"]);
?>

<div class="notice">
	<h3 class="notice-title">Создайте уведомление для сотрудника</h3>
	<form id="notice-form" method="post" onsubmit="addNotice(event);">
		<div class="mb-3">
			<div class="ui-form-content">
                <? $APPLICATION -> IncludeComponent(
                    "bitrix:main.user.selector",
                    "",
                    [
                        "ID"                         => "users",
                        "LAZYLOAD"                   => 'Y',
                        "LIST"                       => [],
                        "INPUT_NAME"                 => 'USERS[]',
                        "USE_SYMBOLIC_ID"            => false,
                        "BUTTON_SELECT_CAPTION"      => "выбрать сотрудника",
                        "BUTTON_SELECT_CAPTION_MORE" => "добавить сотрудника",
                        "API_VERSION"                => 3,
                        "SELECTOR_OPTIONS"           => [
                            'lazyLoad'    => 'Y',
                            'context'     => 'GRATITUDE',
                            'contextCode' => 'D',
                            'disableLast' => 'Y',
                        ]
                    ]
                ); ?>
			</div>
		</div>
		<div class="notice-date-and-btn">
			<div class="notice-date-box">
				<input class="notice-date-input" type="date" id="noticeDate" name="noticeDate">
				<p class="notice-date-text">дата окончания</p>
			</div>
			<input type="submit" value="Создать запись" class="notice-btn">
		</div>
	</form>
</div>

<?
$testNoticeHL = getHLBTClass(13);
$rsTestNotice = $testNoticeHL ::getList(
    [
        "select" => ["*"],
        "order"  => ["UF_DATE" => "ASC"],
        "filter" => ["*"],
    ]
);
?>

<div class="notice-result">
	<hr>
	<h3 class="notice-title">Созданные уведомления</h3>
	<?
    while ($arTestNotice = $rsTestNotice -> Fetch()){
		?><div class="notice-result-info"><?
        ?><p><? print_r($arTestNotice["UF_USERS_NAME"]) ?></p><?
        ?><button class="notice-result-btn" onclick="delNotice(<?print_r($arTestNotice["ID"])?>)">X</button><?
        ?></div><?
        ?><p class="notice-result-date"><? print_r("действует до: " . $arTestNotice["UF_DATE"]) ?></p><?
    }
	?>
</div>

<style>

	.notice-date-and-btn{
		display: flex;
        justify-content: space-between;
		margin-top: 20px;
	}

	.notice-date-text{
		margin: 0;
		padding: 0;
        color: #2F6BA9;
        font-size: 12px;
        font-family: sans-serif;
        font-weight: bold;
	}

    .notice-title{
        color: #303F49;
        font-size: 20px;
        font-family: sans-serif;
        font-weight: bold;
    }

    .notice-date-input{
        font-size: 19px;
        font-family: sans-serif;
        padding: 10px 20px;
        border: 1px solid;
        border-radius: 7px;
        cursor: pointer;
    }

    .notice-btn{
        background-color: #2F6BA9;
        color: #FFFFFF;
        font-size: 19px;
        font-family: sans-serif;
        padding: 10px 20px;
        border: none;
        border-radius: 7px;
        cursor: pointer;
    }

    .notice-btn:hover{
        background-color: #1b4671;
        transition: 0.3s;
    }

	.notice-result{
		margin-top: 60px;
	}

	.notice-result-info{
		padding: 5px;
		display: flex;
		justify-content: space-between;
		border: 1px solid #303F49;
		border-radius: 10px;
		color: #303F49;
	}

	.notice-result-btn{
		padding: 5px 17px;
		background-color: #fb9e9e;
		border: none;
		border-radius: 10px;
        color: #303F49;
		cursor: pointer;
	}

    .notice-result-btn:hover{
        background-color: #df6262;
		color: #fff;
		transition: 0.3s;
	}

	.notice-result-date{
		padding: 0;
		margin: 0 0 20px 0;
        color: #2F6BA9;
        font-size: 12px;
        font-family: sans-serif;
        font-weight: bold;
	}

</style>

<script>

	function showPopupMessage($message, $title, $id, $width, $height){
		if (!$width && !$height){
			$width = 400;
			$height = 300;
		}
		if (!$title){
			$title = 'Сообщение';
		}
		if (!$id){
			$id = Math.floor(Math.random() * 1000);
		}
		var popup = BX.PopupWindowManager.create("popup-message-"+$id, BX('element'), {
			content: $message,
			width: $width, // ширина окна
			height: $height, // высота окна
			zIndex: 100, // z-index
			closeIcon: {
				// объект со стилями для иконки закрытия, при null - иконки не будет
				opacity: 1
			},
			titleBar: $title,
			closeByEsc: true, // закрытие окна по esc
			darkMode: false, // окно будет светлым или темным
			autoHide: true, // закрытие при клике вне окна
			draggable: true, // можно двигать или нет
			resizable: false, // можно ресайзить
			min_height: 100, // минимальная высота окна
			min_width: 100, // минимальная ширина окна
			lightShadow: true, // использовать светлую тень у окна
			angle: false, // появится уголок
			overlay: {
				// объект со стилями фона
				backgroundColor: 'black',
				opacity: 500
			},
			buttons: [
				new BX.PopupWindowButton({
					text: 'Закрыть', // текст кнопки
					id: 'close', // идентификатор
					className: 'ui-btn ui-btn-primary', // доп. классы
					events: {
						click: function() {
							popup.close();
						}
					}
				})
			]
		});
		popup.show();
	}

	// создание уведомления (в hl-блоке)
	function addNotice(e) {
		e.preventDefault();
		let formData = $('#notice-form').serialize();

		$.ajax({
			method: "POST",
			url: "/test-notice/notice.php",
			data: formData,
			dataType: 'json',
			success: function (response) {
				if (response.status == 'success') {
					showPopupMessage('<p align="center">'+response.text+'</p>');
					setTimeout(function() {
						window.location.reload();
					}, 3000)
				}else{
					showPopupMessage('<p align="center">'+response.error_text+'</p>');
				}
			}
		});
	}

	// удалить запись (в hl-блоке)
	function delNotice(elemHL) {
		$.ajax({
			method: "POST",
			url: "/test-notice/del-notice.php",
			data: {elemHL: elemHL},
			dataType: 'json',
			success: function (response) {
				if (response.status == 'success') {
					showPopupMessage('<p align="center">'+response.text+'</p>');
					setTimeout(function() {
						window.location.reload();
					}, 3000)
				}else{
					showPopupMessage('<p align="center">'+response.error_text+'</p>');
				}
			}
		});
	}

</script>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
