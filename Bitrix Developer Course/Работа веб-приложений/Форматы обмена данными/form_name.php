<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>

</head>
<body>
<?php
// выводим данные запроса
echo "<pre>";
var_dump($_REQUEST);
echo "</pre>";

$arUserInfo = array("name"=>$_REQUEST['user_name'], "second_name"=>$_REQUEST['user_second_name'],"last_name"=>$_REQUEST['user_last_name'], "address_city"=>$_REQUEST['user_address_city'], "address_street"=>$_REQUEST['user_address_street'], "address_house"=>$_REQUEST['user_address_house'], "address_apartment"=>$_REQUEST['user_address_apartment']);

// преобразуем arUserInfo в JSON (без экранирования, читаемый)
$strUserInfo = json_encode($arUserInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>

	<form action="" method="POST">
		<strong>Ваше имя<span class="mf-req">*</span></strong><br>
		<input type="text" name="user_name" id="user_name" value=""><br>

		<strong>Ваше отчество<span class="mf-req">*</span></strong><br>
		<input type="text" name="user_second_name" id="user_second_name" value=""><br>

		<strong>Ваша фамилия<span class="mf-req">*</span></strong><br>
		<input type="text" name="user_last_name" id="user_last_name" value=""><br>

		<strong>Ваш адрес<span class="mf-req">*</span></strong><br>
        <div style="display: flex; gap: 5px;">
            <input type="text" name="user_address_city" id="user_address_city" value="" placeholder="город">
            <input type="text" name="user_address_street" id="user_address_street" value="" placeholder="улица">
            <input type="text" name="user_address_house" id="user_address_house" value="" placeholder="дом">
            <input type="text" name="user_address_apartment" id="user_address_apartment" value="" placeholder="квартира">
        </div>
        <br>

		<input type="submit" name="submit" id="submit" value="Отправить">
	</form>

    <div id="result">
    <!-- вывод переменной PHP в верстку HTML -->
    <p><?= $strUserInfo ?></p>
    </div>
    
</body>
</html>