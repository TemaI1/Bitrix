/*

// HTML
<form action="" method="post" id="question-form">
	<input type="text" class="form-control" name="questionUserId" placeholder="fio" required>
	<input type="text" class="form-control" name="questionDate" placeholder="date" required>
	<select name="questionName" class="form-control" required>
		<option value="first">first option</option>
		<option value="second">second optino</option>
	</select>
	<textarea rows="8" name="questionText" class="form-control" placeholder="question" required></textarea>
	<button class="btn btn-primary">Отправить</button>
</form>

// PHP
//Получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();
echo json_encode($post); //возвращаем JSON данные

// JS
$(document).on('submit', '#question-form', function(event) {
	let formData = $('#question-form').serialize(); //собираем данные формы
	// event.preventDefault(); //отмена действия по умолчанию
	$.ajax({
		method: "POST", //метод передачи
		url: "/ajax/question.php", //обрабатываем данные в question.php
		data: formData, //передаем данные
		dataType: 'json', //тип данных в ответе
		success: function (response) { //функция будет выполнена после успешного запроса
      console.log(response); //выводим обработанные данные
		},
	});
});

*/



/*

// HTML
<form method="post" onsubmit="return false;">
	<textarea class="form-control" id="section-name" rows="2" cols="40" name="sectionName" placeholder="section name" required></textarea><br>
	<input type="submit" class="btn btn-outline-primary" value="Добавить" id="btn-add-section" onclick="addSection(10)">
</form>

// PHP
//получаем список POST параметров
$request = Application ::getInstance() -> getContext() -> getRequest();
$post = $request -> getPostList() -> toArray();
$sectionId = $post['sectionId'];
$sectionName = $post['sectionName'];

// JS
function addSection(elem) {
  let section = $("#section-name").val(); //получаем занчение
  $.ajax({
    method: "POST", //метод передачи
    url: "/ajax/database.php", //обрабатываем данные в database.php
    data: {sectionName: section, sectionId: elem}, //передаем данные
    dataType: 'json', //тип данных в ответе
    success: function (response) { //функция будет выполнена после успешного запроса
      window.location.reload(); //перезагрузить страницу
    },
  });
}

*/
