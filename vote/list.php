<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Список опросов");

$uID = $USER -> GetID();

?>

<div class="center">

    <?
    $today = date("Y.m.d");

    // получение значения поля у записи highloadblock
    $resHLVote = $HLVote ::getList([
        "select" => ["*"],
        "order"  => ["ID" => "DESC"],
        "filter" => [],
    ]);

    while ($rsVote = $resHLVote -> Fetch()) {

		// пропустить опрос, если дата завершения больше сегодняшней даты
        $completionDate = date("Y.m.d", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"]));
		if ($today > $completionDate){
			continue;
        } else {
            $date1 = date_create(date("Y-m-d"));
            $date2 = date_create(date("Y-m-d", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"])));
            $diff = date_diff($date1, $date2);
            $countDay = $diff->format('%a');

            // если осталось менее двух дней, менять цвет
			if ($countDay <= 1){
                $countDayColor = "color: #A92F2F;";
			}else{
                $countDayColor = "color: #2fa967;";
			}
		}

        $userVote = 0;

        // получение значения поля у записи highloadblock
        $resHLAnswersVote = $HLAnswersVote ::getList([
            "select" => ["*"],
            "order"  => ["ID" => "DESC"],
            "filter" => ["ID" => $rsVote["UF_VOTING_ANSWERS"]],
        ]);

		?><form class="add-answer-form" action="" id="add-answer-form-<?= $rsVote["ID"] ?>" method="post" onsubmit="return false;"><?
        ?>
		<div class="info-vote-date">
			<p class="info-vote-date-title" style="<?= $countDayColor ?>"><? print_r($countDay + 1) ?></p>
			<p class="info-vote-date-subtitle">дней до завершения</p>
		</div>
		<?
        ?><h3 class="answer-vote-title"><? print_r($rsVote["UF_VOTING_TOPIC"]) ?></h3><?

        while ($rsAnswersVote = $resHLAnswersVote -> Fetch()) {

            // проверка голоса пользователя
            foreach ($rsAnswersVote["UF_ANSWER_GIVEN"] as $key => $value) {
                if (strpos($value, $uID . ',') !== false) {
					$userVote = 1;
					break;
                }else{
					$userVote = 0;
                }
            }

        	if ($userVote == false) {
                ?><h4 class="vote-question-title"><? print_r($rsAnswersVote["UF_QUESTIONS"]) ?></h4><?

                foreach ($rsAnswersVote["UF_ANSWERS"] as $key => $answer) {
                    // формируем уникальный ID для каждого ответа (опрос-ответ)
                    $resultIdAnswer = "question" . $rsAnswersVote["ID"] . "answer-" . $key;

                    // формируем уникальный ID для каждого ответа (опрос)
                    $resultIdAnswerName = "radioquestion" . $rsAnswersVote["ID"];

                    // выводим список ответов
                    if ($rsAnswersVote["UF_MULTIPLE_CHOICE"] == true) {
                        ?>
						<div>
							<input type="radio" id="<?= $resultIdAnswer ?>" name="<?= $resultIdAnswerName ?>" value="<?= $answer ?>" required>
							<label class="vote-answer-text" for="<?= $resultIdAnswer ?>"><?= $answer ?></label>
						</div>
                        <?
                    } else {
                        ?>
						<div>
							<input type="checkbox" id="<?= $resultIdAnswer ?>" name="<?= $resultIdAnswer ?>">
							<label class="vote-answer-text" for="<?= $resultIdAnswer ?>"><?= $answer ?></label>
						</div>
                        <?
                    }
                }
			}
        }

        // получение значения поля у записи highloadblock
        $resHLQuestionsOwnVote = $HLQuestionsOwnVote ::getList([
            "select" => ["*"],
            "order"  => ["ID" => "DESC"],
            "filter" => ["ID" => $rsVote["UF_VOTING_QUESTIONS_OWN"]],
        ]);

        while ($rsQuestionsOwnVote = $resHLQuestionsOwnVote -> Fetch()) {

            // проверка голоса пользователя
            foreach ($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"] as $key => $value) {
                if ($value === $uID) {
                    $userVote = 1;
                    break;
                }else{
                    $userVote = 0;
                }
            }

            if ($userVote == false) {
                ?>
				<h4 class="vote-question-title"><? print_r($rsQuestionsOwnVote["UF_QUESTIONS_OWN"]) ?></h4>
				<div>
					<textarea class="qu-own-name" id="qu-own-name<?= $rsQuestionsOwnVote["ID"] ?>" type="text" name="quOwnName<?= $rsQuestionsOwnVote["ID"] ?>" required=""></textarea>
				</div>
                <?
            }
        }

        if ($userVote == false) {
            // формируем уникальный ID для каждого опроса
            $resultIdQuestion = $rsVote["ID"] . "-vote";
			?>
			<input type="submit" class="add-answer-btn" value="Ответить" id="<?= $resultIdQuestion ?>-btn" onclick="addAnswer(<?= $rsVote["ID"] ?>, <?= $uID ?>)">
            </form>
			<?
        } else {
            ?>
			<p class="vote-completed" style="color: #2fa967">Опрос пройден</p>
			</form>
            <?
        }

    }
    ?>

</div>

<script>

	function addAnswer(voteId, userId) {
		let formData = $(`#add-answer-form-${voteId}`).serialize();
		formData += "&voteId=" + voteId;
		formData += "&userId=" + userId;
		$.ajax({
			method: "POST",
			url: "/vote/ajax/listVote.php",
			data: formData,
			dataType: 'json',
			success: function (data) {
				if (data === "response received"){
					let voteBtn = $(`#${voteId}-vote-btn`);
					voteBtn.attr("disabled", "");
					window.location.reload();
				}
			},
		});
	}

</script>


<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
