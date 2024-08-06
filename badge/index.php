<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("badge");
?>

<div class="container mt-3">
	<form method="POST" action="badge/badge.php" enctype="multipart/form-data">
		<div class="mb-3">
			<div class="ui-form-content">
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
		</div>
		<input type="submit" value="Сохранить" class="btn btn-secondary">
	</form>
</div>
