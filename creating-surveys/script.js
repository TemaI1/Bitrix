//создание поля хайлоадблока (создание вопроса)
$(document).on('submit', '#question-answer-form', function(event) {
	let formData = $('#question-answer-form').serialize();
	// event.preventDefault();
	$.ajax({
		method: "POST",
		url: "/creating-surveys/sendQuestion.php",
		data: formData,
		dataType: 'json',
		success: function (data) {
			$('#question-answer').modal('hide');
		},
	});
});

//обновление поля хайлоадблока (создание ответа)
function giveAnswer(idAnswer) {
	$(document).off('submit', `.give-answer-form-${idAnswer}`); // позволяет удалить обработчик
	$('#give-answer').modal('show');
	$('#give-answer-form').removeClass().addClass(`give-answer-form-${idAnswer}`);
	$(document).on('submit', `.give-answer-form-${idAnswer}`, function (event) {
		let formData = $('#give-answer-form').serialize();
		formData += "&idAnswer=" + idAnswer;
		// event.preventDefault();
		$.ajax({
			method: "POST",
			url: "/creating-surveys/sendQuestion.php",
			data: formData,
			dataType: 'json',
		});
	});
}

//обновление поля хайлоадблока (создание доп. вопроса)
function clarifyQuestion(idQuestion) {
	$(document).off('submit', `.clarify-question-form-${idQuestion}`); // позволяет удалить обработчик
	$('#clarify-question').modal('show');
	$('#clarify-question-form').removeClass().addClass(`clarify-question-form-${idQuestion}`);
	$(document).on('submit', `.clarify-question-form-${idQuestion}`, function (event) {
		let formData = $('#clarify-question-form').serialize();
		formData += "&idQuestion=" + idQuestion;
		// event.preventDefault();
		$.ajax({
			method: "POST",
			url: "/creating-surveys/sendQuestion.php",
			data: formData,
			dataType: 'json',
		});
	});
}

//обновление поля хайлоадблока (закрытие вопроса)
function closeQuestion(idCloseQuestion) {
	$.ajax({
		method: "POST",
		url: "/creating-surveys/sendQuestion.php",
		data: {idCloseQuestion: idCloseQuestion},
		dataType: 'json',
		success: function (response) {
			window.location.reload();
		},
	});
}

//просмотр истории вопроса
function historyQuestion(idHistoryQuestion) {
	$('#history-question-form').removeClass().addClass(`history-question-form-${idHistoryQuestion}`);
	$.ajax({
		method: "POST",
		url: "/creating-surveys/sendQuestion.php",
		data: {idHistoryQuestion: idHistoryQuestion},
		dataType: 'json',
		success: function (data) {
			$('#history-question-title').text(`История вопроса № ${idHistoryQuestion}`);
			$('#history-question-text').text(`${data}`);
			$('#history-question').modal('show');
		},
	});
}
