<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Application;

global $APPLICATION;
global $USER;
$APPLICATION -> SetTitle("Создание опроса");

$today = date("Y-m-d");
?>

<div class="center">

	<p class="message-deletion"><span>!</span>&emsp;Невозможно оставить менее двух ответов</p>
	<p class="empty-fields"><span>!</span>&emsp;Остались пустые поля</p>
	<p class="question-missing"><span>!</span>&emsp;Нужно добавить вопрос</p>
	<p class="creating-vote"><span>!</span>&emsp;Опрос успешно создается, дождитесь перезагрузки страницы</p>

	<form id="add-vote-form" class="form-vote" action="" method="post">
		<div class="vote-head-box">
			<div>
				<textarea class="vote-name" type="text" id="vote-name" name="voteName" required autocomplete="off"></textarea>
				<p class="vote-subtitle">тема вашего опроса</p>
			</div>
			<div>
				<div class="viewed-user-box">
                    <?
                    $APPLICATION->IncludeComponent(
                        'bitrix:main.user.selector',
                        '',
                        [
                            "ID" => "viewed_user",
                            "API_VERSION" => 3,
                            "LIST" => "",
                            "INPUT_NAME" => "VIEWED_USER[]",
                            "USE_SYMBOLIC_ID" => false,
                            "BUTTON_SELECT_CAPTION"      => "выбрать сотрудника",
                            "BUTTON_SELECT_CAPTION_MORE" => "добавить сотрудника",
                            "SELECTOR_OPTIONS" =>
                                [
                                    'contextCode' => 'U',
                                    'enableUsers' => 'Y',
                                    'userSearchArea' => 'I',
                                ]
                        ]
                    );
                    ?>
				</div>
				<p class="vote-subtitle">доступ к результатам</p>
			</div>
			<div class="date-box">
				<input class="vote-date-input" type="date" id="vote-date" name="completionDate" value="<?= $today ?>" min="<?= $today ?>">
				<label class="vote-date-text" for="vote-date">дата завершения</label>
			</div>
		</div>
		<div id="vote-inputs" class="vote-inputs">
			<input id="add-vote-btn" type="submit" class="add-vote-btn" value="Создать опрос">
			<input id="add-question-own-answer" class="add-question-own-answer" type="button" value="Добавить вопрос">
			<input id="add-question" class="add-question" type="button" value="Добавить вопрос с ответами">
		</div>
	</form>
</div>

<style>

	.ui-tile-selector-item-remove{
		background-color: #A92F2F;
		border-radius: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
	}

    .ui-tile-selector-item, .ui-tile-selector-more{
        background-color: #f4f4f4;
        color: #303F49 !important;
	}

	.ui-tile-selector-input{
		padding: 10px;
		border-radius: 5px;
	}

	.ui-tile-selector-select{
		all: unset;
		cursor: pointer;
        color: #303F49;
        font-size: 18px;
        font-family: sans-serif;
        font-weight: normal;
		/*margin: 0;*/
	}

	.ui-tile-selector-select-container{
        width: 200px;
        height: 50px;
        overflow: auto;
        margin-top: -10px;
	}

    .ui-tile-selector-selector-wrap{
		background: none;
		border: none;
	}

</style>

<script>

	function addVote() {
		let formData = $('#add-vote-form').serialize();

		// если ПОСТ запрос слишком большой, создаем опрос несколькими запросами (ПОСТ поочередно записывается в файл longPOST.txt, считывается, когда находит в тексте EXIT и выполняется)
		if (formData.length < 6000){
			$.ajax({
				method: "POST",
				url: "/vote/ajax/creatingVote.php",
				data: formData,
				dataType: 'json',
				success: function () {
					window.location.reload();
				},
			});
		} else {
			let chunksForm = formData.match(new RegExp('.{1,' + 3000 + '}', 'g'));
			chunksForm.forEach((elem, index, arr) => {
				if (arr.length === index + 1){
					elem += "EXIT";
				}
				setTimeout(() => {
					$.ajax({
						method: "POST",
						url: "/vote/ajax/creatingVoteLongPOST.php",
						data: {elem: elem},
						dataType: 'json',
						success: function () {
							setTimeout(() => {
								window.location.reload();
							}, 600*(arr.length + 1));
						},
					});
				}, 500*(index+1));
			})
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		// Весь код, расположенный здесь, будет выполнен сразу после готовности DOM

		let addVoteForm = document.querySelector("#add-vote-form");
		let voteInputs = document.querySelector("#vote-inputs");

		let questions = 0;
		let answers = 0;

		addQuestionOwnFunc();
		addQuestionFunc();
		valid();

		let addQuestion = document.querySelector("#add-question");
		addQuestion.addEventListener("click",function() {
			addQuestionFunc();
		});

		let addQuestionOwn = document.querySelector("#add-question-own-answer");
		addQuestionOwn.addEventListener("click",function() {
			addQuestionOwnFunc();
		});

		//добавление вопроса с вариантами ответов
		function addQuestionFunc(){
			questions++;
			answers++;

			let divQuestion = document.createElement('div');
			divQuestion.classList.add('question-box');
			divQuestion.setAttribute("id", `question${questions}-box`);
			addVoteForm.insertBefore(divQuestion, voteInputs);

			let questionBox = document.createElement('div');
			questionBox.classList.add('question-box-content');
			divQuestion.append(questionBox);

			let questionBoxInputs = document.createElement('div');
			questionBoxInputs.classList.add('question-box-content-inputs');
			questionBox.append(questionBoxInputs);

			let questionsName = document.createElement('textarea');
			questionsName.classList.add('questions-name');
			questionsName.setAttribute("id", `questions-name${questions}`);
			questionsName.setAttribute("type", "text");
			questionsName.setAttribute("name", `questionName${questions}`);
			questionsName.setAttribute("required", "");
			questionsName.setAttribute("autocomplete", "off");
			questionBoxInputs.append(questionsName);

			let questionsNameP = document.createElement('p');
			questionsNameP.classList.add('questions-name-subtitle');
			questionsNameP.textContent = 'вопрос';
			questionBoxInputs.append(questionsNameP);

			let questionBoxInputs2 = document.createElement('div');
			questionBoxInputs2.classList.add('question-box-content-inputs');
			questionBox.append(questionBoxInputs2);

			let answerName = document.createElement('textarea');
			answerName.classList.add('answer-name');
			answerName.setAttribute("id", `answer-name${answers}`);
			answerName.setAttribute("type", "text");
			answerName.setAttribute("name", `qu${questions}answerName${answers}`);
			answerName.setAttribute("required", "");
			answerName.setAttribute("autocomplete", "off");
			questionBoxInputs2.append(answerName);
			answers++;

			let questionsNameP2 = document.createElement('p');
			questionsNameP2.classList.add('questions-name-subtitle');
			questionsNameP2.textContent = 'ответ';
			questionBoxInputs2.append(questionsNameP2);

			let questionBoxInputs3 = document.createElement('div');
			questionBoxInputs3.classList.add('question-box-content-inputs');
			questionBox.append(questionBoxInputs3);

			let answerName2 = document.createElement('textarea');
			answerName2.classList.add('answer-name');
			answerName2.setAttribute("id", `answer-name${answers}`);
			answerName2.setAttribute("type", "text");
			answerName2.setAttribute("name", `qu${questions}answerName${answers}`);
			answerName2.setAttribute("required", "");
			answerName2.setAttribute("autocomplete", "off");
			questionBoxInputs3.append(answerName2);

			let questionsNameP3 = document.createElement('p');
			questionsNameP3.classList.add('questions-name-subtitle');
			questionsNameP3.textContent = 'ответ';
			questionBoxInputs3.append(questionsNameP3);

			let checkboxBox = document.createElement('div');
			checkboxBox.classList.add('checkbox-box');
			divQuestion.append(checkboxBox);

			let btnDelQuestion = document.createElement('input');
			btnDelQuestion.classList.add('question-box-del');
			btnDelQuestion.setAttribute("id", `question${questions}-box-del`);
			btnDelQuestion.setAttribute("type", "button");
			btnDelQuestion.setAttribute("value", "x");
			checkboxBox.append(btnDelQuestion);

			let buttonAdd = document.createElement('input');
			buttonAdd.classList.add('question-box-answer-btn');
			buttonAdd.setAttribute("id", `question${questions}-box-answer-btn`);
			buttonAdd.setAttribute("type", "button");
			buttonAdd.setAttribute("value", "Добавить ответ");
			checkboxBox.append(buttonAdd);

			let buttonDel = document.createElement('input');
			buttonDel.classList.add('question-box-answer-btn-del');
			buttonDel.setAttribute("id", `question${questions}-box-answer-btn-del`);
			buttonDel.setAttribute("type", "button");
			buttonDel.setAttribute("value", "Удалить ответ");
			checkboxBox.append(buttonDel);

			let checkboxName = document.createElement('input');
			checkboxName.setAttribute("id", `checkbox-name${questions}`);
			checkboxName.classList.add('checkbox-mult-box');
			checkboxName.setAttribute("type", "checkbox");
			checkboxName.setAttribute("name", `checkboxName${questions}`);
			checkboxBox.append(checkboxName);

			let checkboxLabel = document.createElement('label');
			checkboxLabel.setAttribute("for", `checkbox-name${questions}`);
			checkboxLabel.classList.add('checkbox-mult-text');
			checkboxLabel.textContent = "Множественный выбор";
			checkboxBox.append(checkboxLabel);

			//удаление вопроса
			let delQuestion = document.querySelectorAll(".question-box-del");
			delQuestion.forEach((el, index, arr) => {
				if (index === arr.length - 1){
					el.addEventListener("click", function () {
						let questionEl = el.id.replace(/[^0-9]/g,"");
						let elBox = document.getElementById(`question${questionEl}-box`);
						elBox.remove();
					});
				}
			});

			//добавление ответа
			let addAnswer = document.querySelectorAll(".question-box-answer-btn");
			addAnswer.forEach((el, index, arr) => {
				if (index === arr.length - 1){
					el.addEventListener("click", function () {
						let questionEl = el.id.replace(/[^0-9]/g,"");
						answers++;

						let questionBoxInputs = document.createElement('div');
						questionBoxInputs.classList.add('question-box-content-inputs');
						questionBox.append(questionBoxInputs);

						let answerName = document.createElement('textarea');
						answerName.classList.add('answer-name');
						answerName.setAttribute("id", `answer-name${answers}`);
						answerName.setAttribute("type", "text");
						answerName.setAttribute("name", `qu${questionEl}answerName${answers}`);
						answerName.setAttribute("required", "");
						answerName.setAttribute("autocomplete", "off");
						questionBoxInputs.append(answerName);

						let questionsNameP = document.createElement('p');
						questionsNameP.classList.add('questions-name-subtitle');
						questionsNameP.textContent = 'ответ';
						questionBoxInputs.append(questionsNameP);
					});
				}
			});

			//удаление ответа
			let delAnswer = document.querySelectorAll(".question-box-answer-btn-del");
			delAnswer.forEach((el, index, arr) => {
				if (index === arr.length - 1){
					el.addEventListener("click", function () {
						let questionEl = el.id.replace(/[^0-9]/g,"");
						let elBox = document.getElementById(`question${questionEl}-box`);
						let elAnswers = elBox.querySelectorAll(".answer-name");
						if (elAnswers.length > 2){
							elAnswers[elAnswers.length - 1].parentNode.remove();
						} else {
							let message = document.querySelector(".message-deletion");
							message.style.visibility = "visible";
							message.style.opacity = "1";

							setTimeout(() => {
								message.style.opacity = "0";
								message.style.visibility = "hidden";
							}, 2000);
						}
					});
				}
			});

		}

		//добавление вопроса с ответом сотрудника
		function addQuestionOwnFunc(){
			questions++;
			answers++;

			let divQuestion = document.createElement('div');
			divQuestion.classList.add('qu-own-box');
			divQuestion.setAttribute("id", `qu-own${questions}-box`);
			addVoteForm.insertBefore(divQuestion, voteInputs);

			let questionBox = document.createElement('div');
			questionBox.classList.add('qu-own-box-content');
			divQuestion.append(questionBox);

			let questionsName = document.createElement('textarea');
			questionsName.classList.add('qu-own-name');
			questionsName.setAttribute("id", `qu-own-name${questions}`);
			questionsName.setAttribute("type", "text");
			questionsName.setAttribute("name", `quOwnName${questions}`);
			questionsName.setAttribute("required", "");
			questionsName.setAttribute("autocomplete", "off");
			questionBox.append(questionsName);

			let questionsNameP = document.createElement('p');
			questionsNameP.classList.add('questions-name-subtitle');
			questionsNameP.textContent = 'вопрос (ответ ожидается от сотрудника)';
			questionBox.append(questionsNameP);

			let checkboxBox = document.createElement('div');
			checkboxBox.classList.add('checkbox-box');
			divQuestion.append(checkboxBox);

			let btnDelQuestion = document.createElement('input');
			btnDelQuestion.classList.add('qu-own-box-del');
			btnDelQuestion.setAttribute("id", `qu-own${questions}-box-del`);
			btnDelQuestion.setAttribute("type", "button");
			btnDelQuestion.setAttribute("value", "x");
			checkboxBox.append(btnDelQuestion);

			//удаление вопроса
			let delQuestion = document.querySelectorAll(".qu-own-box-del");
			delQuestion.forEach((el, index, arr) => {
				if (index === arr.length - 1){
					el.addEventListener("click", function () {
						let questionEl = el.id.replace(/[^0-9]/g,"");
						let elBox = document.getElementById(`qu-own${questionEl}-box`);
						elBox.remove();
					});
				}
			});
		}

		//создание опроса
		function valid() {
			let container = document.getElementById('add-vote-form');
			let input = container.getElementsByTagName('textarea');

			container.addEventListener('click', function (e) {
				// e.preventDefault();
				let not = false;
				let notQuestion = false;

				if (e.target === document.getElementById('add-vote-btn')) {
					for (let i = 0; i < input.length; i++) {
						if (input[i].value) {
							input[i].classList.remove('error');
						} else if (!input[i].value) {
							input[i].classList.add('error');
							not = true;
						}
					}
					for (let i = 0; i < input.length; i++) {
						if (input[i].className === "qu-own-name" || input[i].className === "questions-name"){
							notQuestion = true;
						}
					}

					if (!not) {
						if (!notQuestion){
							$('#add-vote-btn').css('background', '#A92F2F');
							e.preventDefault();
							let message = document.querySelector(".question-missing");
							message.style.visibility = "visible";
							message.style.opacity = "1";
							setTimeout(() => {
								message.style.opacity = "0";
								message.style.visibility = "hidden";
							}, 2000);
						} else {
							addVote();
							$('#add-vote-btn').attr('disabled', '');
							$('#add-vote-btn').css('background', '#2fa967');

							let message = document.querySelector(".creating-vote");
							message.style.visibility = "visible";
							message.style.opacity = "1";
							setTimeout(() => {
								message.style.opacity = "0";
								message.style.visibility = "hidden";
							}, 4000);
						}
					} else {
						$('#add-vote-btn').css('background', '#A92F2F');

						let message = document.querySelector(".empty-fields");
						message.style.visibility = "visible";
						message.style.opacity = "1";
						setTimeout(() => {
							message.style.opacity = "0";
							message.style.visibility = "hidden";
						}, 2000);
					}
				}
			});
		}

	});

</script>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
