<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION -> SetTitle("Создать обращение");
CJSCore ::Init(["jquery"]);
?>

<div class="top-menu">
	<a href="/warn-danger/created.php">Созданные обращения</a>
</div>

<hr>

<div class="danger">
	<h3 class="danger-title">Увидел опасность, напиши нам</h3>
	<form id="danger-form" method="post" onsubmit="addDanger(event);">
		<div class="danger-content">
			<div class="danger-date-box">
				<select class="danger-date-input select" id="department" name="department" required>
					<option value="0" disabled="" selected="selected">Не выбрано</option>
					<option value="Москва">г. Москва</option>
					<option value="Новосибирск">г. Новосибирск</option>
					<option value="Зеленогорск">г. Зеленогорск</option>
					<option value="Электросталь">г. Электросталь</option>
				</select>
				<p class="danger-date-text">cтруктурное подразделение<span style="color: red;">*</span></p>
			</div>
			<div class="danger-date-box">
				<input class="danger-date-input" type="text" id="addres" name="addres">
				<p class="danger-date-text">адрес</p>
			</div>
			<div>
				<div class="danger-date-input">
                    <?
                    $APPLICATION -> IncludeComponent(
                        'bitrix:main.user.selector',
                        ' ',
                        [
                            "ID"                         => "user",
                            "API_VERSION"                => 3,
                            "LIST"                       => "",
                            "INPUT_NAME"                 => "USERS[]",
                            "USE_SYMBOLIC_ID"            => true,
                            "BUTTON_SELECT_CAPTION"      => "выбрать сотрудника",
                            "BUTTON_SELECT_CAPTION_MORE" => "добавить сотрудника",
                            "SELECTOR_OPTIONS"           =>
                                [
                                    "departmentSelectDisable" => "Y",
                                    'context'                 => 'MAIL_CLIENT_CONFIG_QUEUE',
                                    'contextCode'             => 'U',
                                    'enableAll'               => 'N',
                                    'userSearchArea'          => 'I',
                                ],
                        ]
                    );
                    ?>
				</div>
				<p class="danger-date-text">фио<span style="color: red;">*</span></p>
			</div>
			<div class="danger-date-box">
				<select class="danger-date-input select" id="category" name="category" required>
					<option value="0" disabled="" selected="selected">Не выбрано</option>
					<option value="Небезопасные условия">Небезопасные условия</option>
					<option value="Небезопасные действия">Небезопасные действия</option>
					<option value="Улучшение условий труда">Улучшение условий труда</option>
				</select>
				<p class="danger-date-text">категория<span style="color: red;">*</span></p>
			</div>
			<div class="danger-date-box">
				<input class="danger-date-input" type="datetime-local" id="date" name="date" required>
				<p class="danger-date-text">дата и время<span style="color: red;">*</span></p>
			</div>
			<div class="danger-date-box">
				<textarea class="danger-date-input" type="text" id="description" name="description" cols="10" rows="5" required></textarea>
				<p class="danger-date-text">описание<span style="color: red;">*</span></p>
			</div>
			<div class="danger-date-box">
				<textarea class="danger-date-input" type="text" id="measures" name="measures" cols="10" rows="5"></textarea>
				<p class="danger-date-text">принятые меры/предлагаемые меры</p>
			</div>
			<div class="danger-date-box-btn">
				<input type="submit" value="Отправить" class="danger-btn">
			</div>
		</div>
	</form>
</div>

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

	.danger{
		margin: 20px;
		width: 820px;
		padding: 10px 30px 20px 30px;
		background-color: rgb(248, 248, 248);
		border: 1px solid #686868;
		border-radius: 10px;
	}

	.danger-content{
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

    .danger-title{
        color: #303F49;
        font-size: 24px;
        font-family: sans-serif;
        font-weight: bold;
		margin-bottom: 40px;
    }

    .danger-date-text{
        margin: 0;
        padding: 0;
        color: #2F6BA9;
        font-size: 12px;
        font-family: sans-serif;
        font-weight: bold;
    }

    .select{
        width: 820px !important;
    }

    .danger-date-input{
        font-size: 19px;
        font-family: sans-serif;
        padding: 10px 10px;
        border: 1px solid #686868;
        border-radius: 7px;
        cursor: pointer;
    	width: 800px;
    }

    .danger-btn{
        background-color: #2F6BA9;
        color: #FFFFFF;
        font-size: 19px;
        font-family: sans-serif;
        padding: 10px 20px;
        border: none;
        border-radius: 7px;
        cursor: pointer;
    }

    .danger-btn:hover{
        background-color: #1b4671;
        transition: 0.3s;
    }

    .danger-date-box-btn{
    	display: flex;
        justify-content: end;
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

	// создание записи (в hl-блоке)
	function addDanger(e) {
		e.preventDefault();
		let formData = $('#danger-form').serialize();

		$.ajax({
			method: "POST",
			url: "/warn-danger/add-danger.php",
			data: formData,
			dataType: 'json',
			success: function (response) {
				if (response.status == 'success') {
					showPopupMessage('<p align="center">'+response.text+'</p>');
					setTimeout(function() {
						window.location.reload();
					}, 1000)
				}else{
					showPopupMessage('<p align="center">'+response.error_text+'</p>');
				}
			}
		});
	}

</script>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
