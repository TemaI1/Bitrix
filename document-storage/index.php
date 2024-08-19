<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
\Bitrix\Main\UI\Extension ::load("ui.icons.b24");
\Bitrix\Main\UI\Extension ::load("ui.icons.service");

use Bitrix\Main\Application;

global $APPLICATION;
global $USER;
$APPLICATION -> SetTitle("База документов");
?>

<?
//добавление колонок грида
$arResult['GRID']["COLUMNS"] = [
    ['id' => 'ICON', 'name' => '', 'width' => '36', 'default' => true],
    ['id' => 'PREVIEW_TEXT', 'name' => 'Название', 'default' => true],
    ['id' => 'COMMENT', 'name' => 'Комментарий', 'default' => true],
];

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();
$sectionId = $post['sectionValue'];

//ID Информационного блока
$iblockId = 10;

//определяем фильтр
if (empty($sectionId)) {
    $arFilterList = ["IBLOCK_ID" => $iblockId];
} else {
    $arFilterList = ["IBLOCK_ID" => $iblockId, "SECTION_ID" => $sectionId];
}

//добавление раздела и строк грида
$resSection = CIBlockSection ::GetList([],
    $arFilterList,
    false,
    ["ID", "NAME", "PROPERTY_DOCUMENT", "PROPERTY_COMMENT", "IBLOCK_SECTION_ID"]
);
while ($ob = $resSection -> Fetch()) {
    if ($ob["IBLOCK_SECTION_ID"] == $sectionId) {
        $elementRow = [
            'data'          => [
                "ID"           => $ob["ID"],
                "ICON"         => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-folder align-middle" viewBox="0 0 16 16"><path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/></svg>',
                "PREVIEW_TEXT" => $ob["NAME"],
            ],
            'actions'       => [
                [
                    'text'      => "Перейти",
                    'onclick'   => 'BX.PreventDefault(); openSection(' . $ob['ID'] . ');',
                    'className' => $ob['ID'],
                ],
            ],
            'columnClasses' => [
                'ICON' => "icon-open-section",
            ],
        ];
        $arResult['GRID']["ROWS"][] = $elementRow;
    }
}

//добавление документа и строк грида
$resElement = CIBlockElement ::GetList(
    [],
    $arFilterList,
    false,
    false,
    ["ID", "NAME", "PROPERTY_DOCUMENT", "PROPERTY_COMMENT", "IBLOCK_SECTION_ID"]
);
while ($ob = $resElement -> Fetch()) {
    if ($ob["IBLOCK_SECTION_ID"] == $sectionId) {
        $pathFile = CFile ::GetPath($ob["PROPERTY_DOCUMENT_VALUE"]);

        // определяем список действий для грида
        if ($USER -> IsAdmin()){
            $actions = [
                [
                    'text'   => "Скачать",
                    'href'   => $pathFile,
                    'target' => "_blank",
                ],
                [
                    'text'   => "Удалить",
                    'onclick' => 'BX.PreventDefault(); deleteFile(' . $ob['ID'] . "," . $ob["IBLOCK_SECTION_ID"] .');',
                ],
            ];
        } else {
            $actions = [
                [
                    'text'   => "Скачать",
                    'href'   => $pathFile,
                    'target' => "_blank",
                ],
            ];
        }

        $elementRow = [
            'data'    => [
                "ID"           => $ob["ID"],
                "ICON"         => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-text align-middle" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>',
                "PREVIEW_TEXT" => $ob["NAME"],
                "COMMENT"      => $ob["PROPERTY_COMMENT_VALUE"],
            ],
            'actions' => $actions,
        ];
        $arResult['GRID']["ROWS"][] = $elementRow;
    }
}
?>

<div class="procurement">
	<div class="row col-12">

		<button id="btn-open-section" class="btn btn-outline-primary m-1" disabled="true">Назад</button>

		<button class="btn btn-outline-primary m-1" data-toggle="modal" data-target="#searchDocTarget">
			<span class="align-middle">Найти документ&nbsp;</span>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search align-middle" viewBox="0 0 16 16">
				<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
			</svg>
		</button>
		<div class="modal fade" id="searchDocTarget" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<form id="searchDocForm" method="post" onsubmit="return false;">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Найти документ</h5>
							<button type="button" class="close" data-dismiss="modal">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
						<textarea class="form-control mt-2" id="search-doc-name" rows="2" cols="40" name="searchDocName"
								  placeholder="Введите название документа" required></textarea><br>
						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-outline-primary" value="Поиск" id="btn-search-doc"
								   onclick="searchDoc()">
						</div>
					</form>
					<div class="ml-4">
						<p id="text-search-list"></p>
						<ul id="search-list"></ul>
					</div>
				</div>
			</div>
		</div>

		<? if ($USER -> IsAdmin()): ?>
		<button class="btn btn-outline-primary m-1" data-toggle="modal" data-target="#addSectionTarget">
			<span class="align-middle">Добавить раздел&nbsp;</span>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder align-middle" viewBox="0 0 16 16">
				<path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
			</svg>
		</button>
		<?endif; ?>
		<div class="modal fade" id="addSectionTarget" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<form id="addSectionForm" method="post" onsubmit="return false;">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Добавить раздел</h5>
							<button type="button" class="close" data-dismiss="modal">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
						<textarea class="form-control mt-2" id="section-name" rows="2" cols="40" name="sectionName"
								  placeholder="Введите название раздела" required></textarea><br>
						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-outline-primary" value="Добавить" id="btn-add-section"
								   onclick="addSection()">
						</div>
					</form>
				</div>
			</div>
		</div>

		<? if ($USER -> IsAdmin()): ?>
		<button class="btn btn-outline-primary m-1" data-toggle="modal" data-target="#addDocTarget">
			<span class="align-middle">Добавить документ&nbsp;</span>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text align-middle" viewBox="0 0 16 16">
				<path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
				<path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
			</svg>
		</button>
		<?endif; ?>
		<div class="modal fade" id="addDocTarget" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<form id="addDocForm" method="post" onsubmit="return false;">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Добавить документ</h5>
							<button type="button" class="close" data-dismiss="modal">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
						<textarea class="form-control mt-2" id="document-name" rows="2" cols="40" name="documentName"
								  placeholder="Введите название документа" required></textarea>
							<textarea class="form-control mt-2" id="document-comment" rows="2" cols="40" name="documentComment"
									  placeholder="Введите комментарий к документу"></textarea>
							<div class="mt-2">
								<input class="form-control" type="file" id="document-file" required>
							</div>

						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-outline-primary" value="Добавить" id="btn-add-doc"
								   onclick="addDoc()">
						</div>
					</form>
				</div>
			</div>
		</div>

	</div>

	<div class="m-2 row col-12">
		<?
		//вызов компонента (грид)
		$APPLICATION -> IncludeComponent(
			'bitrix:main.ui.grid',
			'',
			[
				'GRID_ID'                   => 'documents',
				'COLUMNS'                   => $arResult['GRID']["COLUMNS"],
				'ROWS'                      => $arResult['GRID']["ROWS"],
				'AJAX_MODE'                 => 'Y',
				'AJAX_OPTION_JUMP'          => 'N',
				'AJAX_OPTION_HISTORY'       => 'N',
				'SHOW_ROW_CHECKBOXES'       => false,
				'SHOW_CHECK_ALL_CHECKBOXES' => false,
				'SHOW_SELECTED_COUNTER'     => false,
				'SHOW_TOTAL_COUNTER'        => false,
			]
		);
		?>
	</div>
</div>

<script>

	let arrSection = [];

	// обновление грида, добавление раздела в список секций, добавление атрибутов согласно таблице
	function openSection(value) {
		// обновление грида
		const gridObject = BX.Main.gridManager.getById('documents');
		const reloadParams = {sectionValue: value};
		gridObject.instance.reloadTable('POST', reloadParams);

		// добавление раздела в список секций (проверка необходима, что бы не создавались дубликаты)
		if (arrSection[arrSection.length - 1] != value){
			arrSection.push(value);
		}

		// добавляем атрибут (кнопка назад), создаем временное ограничение по нажатию
		if (typeof value !== "undefined") {
			document.getElementById("btn-open-section").setAttribute('onclick', `backSection(${arrSection[arrSection.length - 2]})`);
			document.getElementById("btn-open-section").setAttribute('disabled', 'true');
			setTimeout(() => {
				document.getElementById("btn-open-section").removeAttribute('disabled');
			}, 1000);
		} else {
			document.getElementById("btn-open-section").setAttribute('disabled', 'true');
		}

		// добавляем атрибуты (кнопка создания раздела, документа)
		document.getElementById("btn-add-section").setAttribute('onclick', `addSection(${arrSection[arrSection.length - 1]})`);
		document.getElementById("btn-add-doc").setAttribute('onclick', `addDoc(${arrSection[arrSection.length - 1]})`);

		// вызов функции (переход по клику)
		setTimeout(() => {
			visitSection();
		}, 700);
	}

	// обновление грида, удаление раздела из списка секций, добавление атрибутов согласно таблице
	function backSection(value) {
		// обновление грида
		const gridObject = BX.Main.gridManager.getById('documents');
		const reloadParams = {sectionValue: value};
		gridObject.instance.reloadTable('POST', reloadParams);

		// удаление раздела из списка секций
		arrSection.pop();

		// добавляем атрибут (кнопка назад), создаем временное ограничение по нажатию
		if (typeof value !== "undefined") {
			document.getElementById("btn-open-section").setAttribute('onclick', `backSection(${arrSection[arrSection.length - 2]})`);
			document.getElementById("btn-open-section").setAttribute('disabled', 'true');
			setTimeout(() => {
				document.getElementById("btn-open-section").removeAttribute('disabled');
			}, 1000);
		} else {
			document.getElementById("btn-open-section").setAttribute('disabled', 'true');
		}

		// добавляем атрибуты (кнопка создания раздела, документа)
		document.getElementById("btn-add-section").setAttribute('onclick', `addSection(${arrSection[arrSection.length - 1]})`);
		document.getElementById("btn-add-doc").setAttribute('onclick', `addDoc(${arrSection[arrSection.length - 1]})`);

		// вызов функции (переход по клику)
		setTimeout(() => {
			visitSection();
		}, 700);
	}

	//  удаление документа, обновление грида
	function deleteFile(deleteValue, sectionElem){
		console.log(deleteValue);
		$.ajax({
			method: "POST",
			url: "/ajax/doc-data.php",
			data: {deleteDocValue: deleteValue, sectionId: sectionElem},
			dataType: 'json',
			success: function (response) {
				console.log('ok');
			},
		});

		setTimeout(() => {
			openSection(sectionElem);
		}, 1000);
	}

	// переход по клику
	function visitSection() {
		document.querySelectorAll('.icon-open-section').forEach(function (item) {
			item.parentNode.addEventListener('dblclick', function () {
				openSection(item.closest("tr").getAttribute('data-id'));
			});
		});
	}
	visitSection();

	// создание раздела
	function addSection(sectionElem) {
		let sectionName = $("#section-name").val();
		$.ajax({
			method: "POST",
			url: "/ajax/doc-data.php",
			data: {sectionName: sectionName, sectionId: sectionElem},
			dataType: 'json',
			success: function (response) {
				console.log('ok');
			},
		});

		// обновление грида, закрытие модального окна
		if (sectionName !== "") {
			setTimeout(() => {
				openSection(sectionElem);
				$('#addSectionTarget').modal('hide');
			}, 1000);
		}
	}

	// создание документа
	function addDoc(sectionElem) {
		let formData = new FormData();
		let documentName = $("#document-name").val();
		let documentFile = document.getElementById('document-file').files[0];
		formData.append('sectionId', sectionElem);
		formData.append('documentName', documentName);
		formData.append('documentComment', $("#document-comment").val());
		formData.append('documentFile', documentFile);

		$.ajax({
			method: "POST",
			url: "/ajax/doc-data.php",
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				console.log('ok');
			},
		});

		// обновление грида, закрытие модального окна
		if (documentName !== "" && documentFile !== undefined) {
			setTimeout(() => {
				openSection(sectionElem);
				$('#addDocTarget').modal('hide');
			}, 1000);
		}
	}

	// поиск документа
	function searchDoc(){
		let searchName = $("#search-doc-name").val();
		$.ajax({
			method: "POST",
			url: "/ajax/doc-data.php",
			data: {searchName: searchName},
			dataType: 'json',
			success: function(data) {
				// добавление в дом-дерево документов для скачивания
				document.getElementById('search-list').innerHTML = '';
				for (let i in data) {
					let itemLi = document.createElement('li');
					let itemA = document.createElement('a');
					itemLi.appendChild(itemA);
					itemA.textContent = data[i];
					itemA.setAttribute("href", i);
					itemA.setAttribute("target", "_blank");
					document.getElementById('search-list').appendChild(itemLi);
				}
				if (document.getElementById('search-list').firstChild){
					document.getElementById('text-search-list').textContent = 'Для скачивания нажмите на нужный файл:';
				} else {
					document.getElementById('text-search-list').textContent = 'Документ не найден!';
				}
			},
		});
	}

</script>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
