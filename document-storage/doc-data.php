<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Application;

//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();

//ID Информационного блока
$iblockId = 10;

//создание раздела
if (!empty($post['sectionName'])) {
    $el = new CIBlockSection;
    $sectionIdAdd = $el -> Add([
        "ACTIVE"            => 'Y',
        "IBLOCK_SECTION_ID" => $post['sectionId'],
        "IBLOCK_ID"         => $iblockId,
        "NAME"              => $post['sectionName'],
    ]);
}

//создание документа
if (!empty($post['documentName'])) {
    if(!empty($_FILES['documentFile']['name'])) {

        // получение файла и загрузка на сервер
        $file = $_FILES['documentFile']['tmp_name'];
        $fileName = $_FILES['documentFile']['name'];
        $filePath = '/upload/documents-db/' . $fileName;
        move_uploaded_file($file, $_SERVER['DOCUMENT_ROOT'] . $filePath);

        // создание документа, добавление данных
        $el = new CIBlockElement;
        $documentIdAdd = $el -> Add([
            "ACTIVE"            => 'Y',
            "IBLOCK_SECTION_ID" => $post['sectionId'],
            "IBLOCK_ID"         => $iblockId,
            "NAME"              => $post['documentName'],
            "PROPERTY_VALUES" => [
                "COMMENT"  => $post['documentComment'],
                "DOCUMENT" => CFile::MakeFileArray($filePath),
            ],
        ]);

//        ДОБАВИТЬ УДАЛЕНИЕ ФАЙЛА из базы, после добавления его в свойство инфоблока типа файл (для исключения дублирования)
    }
}

//удаление документа
if (!empty($post['deleteDocValue'])){
    CIBlockElement::Delete($post['deleteDocValue']);
}

//поиск документа
if (!empty($post['searchName'])){
    $res = CIBlockElement::GetList([], ["IBLOCK_ID" => $iblockId], false, false, []);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields(); // содержит основные поля элемента инфоблока
        $arProps = $ob->GetProperties(); // содержит свойства элемента инфоблока

        $filePath = "https://" . $_SERVER["SERVER_NAME"] . CFile::GetPath($arProps["DOCUMENT"]["VALUE"]); // путь к файлу
        $filesName[$filePath] = $arFields['NAME']; // полный список документов (ключ - путь к файлу, значение - имя файла)
    }

    // выводим результирующий список документов
    $searchItem = $post['searchName'];
    $resultSearch = array_filter($filesName, function($item) use($searchItem) {
        return strpos($item, $searchItem) !== false;
    });
    echo json_encode($resultSearch);
}
