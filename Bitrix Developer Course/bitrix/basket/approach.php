<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Basket");
?>

<?php
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Bitrix\Currency\CurrencyManager;
Loader::includeModule('sale');

// ид текущего сайта
$siteId = Context::getCurrent()->getSite();

// ид текущего покупателя (не пользователя)
$fUserId = Fuser::getId();

// ид покупателя для пользователья с ид = $id
//$fUserId = Fuser::getIdByUserId(3);

// создаём объект корзины
$basket = Basket::create($siteId);
$basket->setFUserId($fUserId);

// загружаем объект корзины для конкретного покупателя конкретного сайта
$basket = Basket::loadItemsForFUser($fUserId, $siteId);
$price = $basket->getPrice(); // конечная цена (с учётом скидки)
$baseprice  = $basket->getBasePrice(); // базовая цена (без скидки)
$weight = $basket->getWeight(); // вес корзины

// объект корзины не хранится в базе, хранятся только элементы корзины
// поэтому чтобы удалить корзину, нужно удалить все элементы
/*
$basket->clearCollection();
$result = $basket->save();
if (!$result->isSuccess()) {
    // обработка ошибок
    var_dump($result->getErrorMessages());
} else {
    echo "Success deleted!";
}
//*/

// удаляем корзины, созданные более 5 дней назад
//$deletedRowsCount = Basket::deleteOld(5);

//Создание элементов корзины
/*
$moduleId = 'sale';
$productId = 42; //ID простого товара или торгового предложения
$basketItem = $basket->createItem($moduleId, $productId);
$result = $basketItem->setField('QUANTITY', 10);
// можно указывать сразу несколько полей
$result = $basketItem->setFields([
    'NAME' => 'Название товара',
    'PRICE' => 100,
    'BASE_PRICE' => 100,
    'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency()
]);
if (!$result->isSuccess())
{
    // обработка ошибок
    //var_dump($result->getErrorMessages());
}
$saveResult = $basket->save();
//*/

//Создание элементов корзины через провайдера
//провайдер - класс, созданный для обработки элементов какой-то сущности (в данном случае - элементов корзины)
/*
$moduleId = 'sale';
$productId = 340;
$basketItem = $basket->createItem($moduleId, $productId);
// если указан провайдер данных, то поля товара можно не заполнять, они заполнятся автоматически
$result = $basketItem->setFields([
    'QUANTITY' => 2,
    'PRODUCT_PROVIDER_CLASS' => Bitrix\Catalog\Product\CatalogProvider::class,
]);
if (!$result->isSuccess())
{
    // обработка ошибок
    var_dump($result->getErrorMessages());
}
$saveResult = $basket->save();
//*/

//Чтение элементов корзины
//$basketItem = $basket->getItemById(32); // ID строки корзины в БД b_sale_basket
//$basketItem = $basket->getItemByXmlId("bx_64ea430b43f1f"); //  XML_ID строки корзины в БД b_sale_basket
//$basketItem = $basket->getItemByIndex(2); // порядковый индекс в корзине
//echo '<pre>'; var_dump($basketItem); echo '</pre>';
//$var = $basketItem->getQuantity(); // количество
//$var = $basketItem->getPrice(); // цена с учётом скидки
//$var = $basketItem->getBasePrice(); // базовая цена без скидки
//$var = $basketItem->getDiscountPrice(); // сумма скидки
//$var = $basketItem->getWeight(); // вес
// получать любые поля можно с помощью метода - аргументом будет код поля, или колонка в таблице b_sale_basket
//$var = $basketItem->getField('DATE_INSERT');
//echo '<pre>'; var_dump($var); echo '</pre>';

//Чтение элементов корзины в цикле
// перебирать элементы корзины можно в цикле
// т.к. объект корзины является итератором
/*
foreach ($basket as $basketItem)
{
    $basketItem->getField('...');
    echo '<pre>'; var_dump($basketItem); echo '</pre>';
    echo '<pre>'; var_dump($basketItem->getBasketCode()); echo '</pre>';
}
*/

//Удаление элементов корзины
/*
foreach ($basket as $basketItem)
{
    $result = $basketItem->delete();
    if (!$result->isSuccess())
    {
        // обрабатываем ошибки удаления
        //var_dump($result->getErrorMessages());
    }
}
$saveResult = $basket->save();
*/

// удалить можно также сразу по индексу в корзине
/*
$basket->deleteItem(1);
// чтобы элементы удалились из базы данных, нужно сохранить изменения
$result = $basket->save();
if (!$result->isSuccess())
{
    // обрабатываем ошибки сохранения
}
*/

//Свойства элементов корзины
/*
$basketItem = $basket->getItemByIndex(2);
$properties = $basketItem->getPropertyCollection();
$result = $properties->createItem()->setFields([
    'NAME' => 'Размер',
    'CODE' => 'SIZE',
    'VALUE' => 'xs',
]);
$result = $properties->createItem()->setFields([
    'NAME' => 'Цвет',
    'CODE' => 'COLOR',
    'VALUE' => 'красный',
]);
// не забываем сохранить корзину, чтобы изменения записались в базу
$result = $basketItem->getBasket()->save();
*/

// если свойство есть в коллекции, но отсутствует во входных данных, то свойство будет удалено
// если свойство есть в коллекции, а также присутствует во входных данных, то свойство обновится
// если свойства нет в коллекции, но присутствует во входных данных, то свойство добавится
/*
$basketItem = $basket->getItemByIndex(2);
$properties = $basketItem->getPropertyCollection();
$basketItem->getPropertyCollection()->redefine([
    [
        'NAME' => 'Размер',
        'CODE' => 'SIZE',
        'VALUE' => 'xl',
    ],
    [
        'NAME' => 'Цвет',
        'CODE' => 'COLOR',
        'VALUE' => 'зеленый',
    ],
    [
        'NAME' => 'Бренд',
        'CODE' => 'BRAND',
        'VALUE' => 'Дусенька-кукусенька',
    ],
]);
// не забываем сохранить корзину, чтобы изменения записались в базу
$result = $basketItem->getBasket()->save();
*/

//echo '<pre>'; var_dump($weight); echo '</pre>';

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
