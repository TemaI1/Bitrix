<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Добавить подарок в корзину");

use Bitrix\Main\Loader;

// Подключаем необходимые модули
Loader::includeModule('iblock');
Loader::includeModule('sale');
Loader::includeModule('catalog');

$resultMessage = "";
$messageType = "success";

$siteId = "s1";

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    $userId = intval($_POST['user_id']);
    $maxPrice = floatval($_POST['max_price']);
    $iblockId = 2;

    $user = \CUser::GetByID($userId)->Fetch();
    
    if (!$user) {
        $resultMessage = "Ошибка: Пользователь с ID {$userId} не найден.";
        $messageType = "error";
    } else {
        // Получаем FUSER_ID только для существующего пользователя
        $fuserId = \CSaleBasket::GetBasketUserID($userId);

        // Формируем фильтр по цене
        $priceFilter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
        if ($maxPrice > 0) {
            $priceFilter['<=CATALOG_PRICE_1'] = $maxPrice;
        } else {
            $priceFilter['>CATALOG_PRICE_1'] = 0;
        }

        // Выбираем случайный активный товар из каталога
        $res = \CIBlockElement::GetList(
            ['RAND' => 'ASC'],
            $priceFilter,
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME', 'CATALOG_PRICE_1', 'DETAIL_PAGE_URL']
        );
        
        $product = $res->GetNext();

        if ($product && $fuserId) {
            // Проверяем наличие товара в корзине
            $basketItem = \CSaleBasket::GetList(
                [],
                [
                    'FUSER_ID' => $fuserId,
                    'LID' => $siteId,
                    'PRODUCT_ID' => $product['ID'],
                    'ORDER_ID' => 'NULL'
                ]
            )->GetNext();

            if ($basketItem) {
                \CSaleBasket::Update($basketItem['ID'], ['QUANTITY' => $basketItem['QUANTITY'] + 1]);
                $resultMessage = "Товар \"{$product['NAME']}\" ({$product['CATALOG_PRICE_1']} ₽) уже в корзине (кол-во увеличено).";
            } else {
                \CSaleBasket::Add([
                    'FUSER_ID' => $fuserId,
                    'LID' => $siteId,
                    'PRODUCT_ID' => $product['ID'],
                    'PRICE' => $product['CATALOG_PRICE_1'],
                    'CURRENCY' => 'RUB',
                    'QUANTITY' => 1,
                    'NAME' => $product['NAME'],
                    'MODULE' => 'sale',
                    'PRODUCT_PROVIDER' => 'catalog',
                    'DELAY' => 'N',
                    'CAN_BUY' => 'Y',
                ]);
                $resultMessage = "Товар \"{$product['NAME']}\" за {$product['CATALOG_PRICE_1']} ₽ успешно добавлен в корзину.";
            }
        } elseif (!$product) {
            $resultMessage = "Ошибка: В каталоге нет товаров" . ($maxPrice > 0 ? " стоимостью до {$maxPrice} ₽." : ".");
            $messageType = "error";
        }
    }
}
?>

<?php if ($USER->IsAdmin()): ?>
    <div class="gift-form-container">
        <h2>Добавить подарок</h2>
        <p>Случайный товар из каталога "Одежда" (не дороже указанной суммы) будет добавлен в корзину пользователя.</p>
        
        <?php if ($resultMessage): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $resultMessage ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= bitrix_sessid_post() ?>
            
            <div class="form-group">
                <label for="user_id">ID Пользователя:</label>
                <input type="number" name="user_id" id="user_id" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label for="max_price">Максимальная цена (₽):</label>
                <input type="number" name="max_price" id="max_price" value="5000" min="0" step="0.01" placeholder="Например: 5000">
                <small>Оставьте пустым или 0, чтобы выбрать любой товар</small>
            </div>

            <button type="submit">Отправить подарок</button>
        </form>
    </div>
<?php else: ?>
<p>Доступ запрещён. Только для администраторов.</p>
<?php endif; ?>

<style>
.gift-form-container {
    max-width: 500px;
    margin: 50px auto;
    padding: 20px;
}

.gift-form-container h2 {
    margin-top: 0;
    margin-bottom: 10px;
}

.gift-form-container p {
    color: #666;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}

.form-group small {
    color: #888;
    font-size: 12px;
}

.alert {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

button[type="submit"] {
    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button[type="submit"]:hover {
    background: #0056b3;
}
</style>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
