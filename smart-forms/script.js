document.addEventListener("DOMContentLoaded", function (){

	// заменяем пустую дату в ячейках
	let dateElements = document.querySelectorAll('p.date-info');
	dateElements.forEach(function(el) {
		let text = el.textContent.trim();
		if (text === '01.01.1970') {
			el.textContent = '-';
		}
	});

	// добавляем цвет ячейки в зависимости от статуса
	let statusElements = document.querySelectorAll('p.status-info');
	statusElements.forEach(function(el) {
		let text = el.textContent.trim();
		if (text === 'В работе') {
			el.parentNode.style.backgroundColor = 'rgb(47 107 169 / 30%)';
		} else if (text === 'Завершено'){
			el.parentNode.style.backgroundColor = 'rgb(35 169 102 / 30%)';
		}
	});

});

// элементы редактирования
function changeItem(elemHL){
	document.querySelectorAll(`.smart-box${elemHL}`).forEach(function (e){
		let changeBtn = document.querySelector(`.change-result-btn${elemHL}`);
		let changeSaveBtn = document.querySelector(`.change-result-btn-save${elemHL}`);
		let changeDelBtn = document.querySelector(`.change-result-btn-delete${elemHL}`);

		if (e.style.display === "none"){
			e.style.display = "block";
			changeSaveBtn.style.display = "block";
			changeDelBtn.style.display = "block";
			changeBtn.value = "Отмена";
			changeBtn.style.backgroundColor = "#a98b23";
		} else {
			e.style.display = "none";
			changeSaveBtn.style.display = "none";
			changeDelBtn.style.display = "none";
			changeBtn.value = "Изменить";
			changeBtn.style.backgroundColor = "#2F6BA9";
		}
	});

}

// обновление записи (проблема, решения, этапы)
function updateCell(e) {
	e.preventDefault();
	let formData = $(`#${e.target.id}`).serialize();

	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/update-element.php",
		data: formData,
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// удаление записи в hl-блоке (проблема, решения, этапы)
function dellCell(elem) {
	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/delete-element.php",
		data: {elemHL: elem},
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// создание записи в hl-блоке (проблема, решение, этап)
function addCell(){
    $.ajax({
		method: "POST",
		url: "/smart-forms/ajax/add-element.php",
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}
		}
	});
}

// добавление записи в hl-блоке (решение)
function addSolution(elem){
	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/add-solution.php",
		data: {elemHL: elem},
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// добавление записи в hl-блоке (этап)
function addStage(elem){
	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/add-stage.php",
		data: {elemHL: elem},
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// удаление записи в hl-блоке (решение)
function delSolution(elemProblem, elemSolution){
	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/delete-solution.php",
		data: {elemProblem: elemProblem, elemSolution: elemSolution},
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// удаление записи в hl-блоке (этап)
function delStage(elemSolution, elemStage){
	$.ajax({
		method: "POST",
		url: "/smart-forms/ajax/delete-stage.php",
		data: {elemSolution: elemSolution, elemStage: elemStage},
		dataType: 'json',
		success: function (response) {
			if (response.status == 'success') {
				showNotification(response.text, true);
				setTimeout(function() {
					window.location.reload();
				}, 1000)
			}else{
				showNotification(response.error_text, false);
			}
		}
	});
}

// уведомление
function showNotification(text, result){
	let notification = document.querySelector(".notification");
	notification.classList.add("notification-show");
	notification.textContent = text;
	if (result){
		notification.style.backgroundColor = "#23a966";
	} else {
		notification.style.backgroundColor = "#a92323";
	}
	setTimeout(() => {
		notification.classList.remove("notification-show");
		notification.textContent = "";
	}, 3000)
}
