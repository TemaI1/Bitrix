<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION -> SetTitle("Список опросов");

$uID = $USER -> GetID();
?>

<div class="center">

    <?
    $today = date("d.m.Y", strtotime(date("Y-m-d")));

    // получение значения поля у записи highloadblock
    $resHLVote = $HLVote ::getList([
        "select" => ["*"],
        "order"  => ["ID" => "DESC"],
        "filter" => [],
    ]);

    while ($rsVote = $resHLVote -> Fetch()) {

        // если пользователь есть в списке доступна к результатам
        if (in_array($uID, $rsVote["UF_VIEWED_USER"]) || $USER->IsAdmin()) {
            ?>
			<div class="add-answer-form">
				<div>
					<div class="info-vote-date">
						<p class="info-vote-date-title"><? print_r(date("d.m.Y", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"]))) ?></p>
						<p class="info-vote-date-subtitle">дата завершения</p>
					</div>
					<h3 class="answer-vote-title"><? print_r($rsVote["UF_VOTING_TOPIC"]) ?></h3>
				</div>
				<div class="download-vote-box">
					<form method="POST" action="/vote/ajax/resultVoteWord.php" enctype="multipart/form-data">
						<input style="display: none" readonly name="voteId" value="<?= $rsVote["ID"] ?>">
						<input class="download-vote-word" type="submit" value="Скачать word">
					</form>
					<form method="POST" action="/vote/ajax/resultVoteExel.php" enctype="multipart/form-data">
						<input style="display: none" readonly name="voteId" value="<?= $rsVote["ID"] ?>">
						<input class="download-vote-exel" type="submit" value="Скачать exel">
					</form>
					<input id="view-vote" class="view-vote" type="button" value="Посмотреть на портале" onclick="viewVote(<?= $rsVote["ID"] ?>)">
				</div>
			</div>
            <?

            // получение значения поля у записи highloadblock
            $resHLAnswersVote = $HLAnswersVote ::getList([
                "select" => ["*"],
                "order"  => ["ID" => "DESC"],
                "filter" => ["ID" => $rsVote["UF_VOTING_ANSWERS"]],
            ]);

            ?><div class="result-vote-box" id="result-vote-box-<?= $rsVote["ID"] ?>"><?

            while ($rsAnswersVot = $resHLAnswersVote -> Fetch()) {

                ?><div class="result-vote-box-item"><?
                ?><h4 class="result-vote-box-item-title"><? print_r($rsAnswersVot["UF_QUESTIONS"]) ?></h4><?

                $userList = [];
                foreach ($rsAnswersVot["UF_ANSWER_GIVEN"] as $key => $value) {
                    foreach (explode(", ", $value) as $keyUser => $valueUser) {
                        if ($valueUser != ""){
                            $rsUser = CUser::GetByID($valueUser);
                            $arUser = $rsUser->Fetch();
                            $userList[$key][] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"];
                        }
                    }
                }

                $countAnswer = [];
                foreach ($rsAnswersVot["UF_COUNT_VOTES"] as $key => $value) {
                    $countAnswer[] = $value;
                }

                ?><div class="result-vote-box-item-it"><?
                foreach ($rsAnswersVot["UF_ANSWERS"] as $key => $value) { ?>
					<div class="result-vote-answer-box">
						<p class="result-subtitle-text">Ответ:</p>
						<p class="result-subtitle-text2"><? print_r($value) ?></p>
					</div>

					<div class="result-vote-list-box">
						<p class="result-subtitle-text">Список ответивших: </p>
						<div class="result-vote-list-box-users">
                            <? foreach ($userList[$key] as $keyUser => $valueUser) { ?>
								<p class="result-subtitle-text2"><? print_r($valueUser) ?>, </p>
                            <? } ?>
						</div>
					</div>

					<div class="result-vote-count-answer-box">
						<p class="result-subtitle-text">Кол-во ответов:</p>
						<p class="result-subtitle-text2"><? print_r($countAnswer[$key]) ?></p>
					</div>
					<hr>
                    <?
                }

                ?></div><?
                ?></div><?

            }

            // получение значения поля у записи highloadblock
            $resHLQuestionsOwnVote = $HLQuestionsOwnVote ::getList([
                "select" => ["*"],
                "order"  => ["ID" => "DESC"],
                "filter" => ["ID" => $rsVote["UF_VOTING_QUESTIONS_OWN"]],
            ]);

            while ($rsQuestionsOwnVote = $resHLQuestionsOwnVote -> Fetch()) {
                if (!empty($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"])){

                    ?><div class="result-vote-box-item"><?
                    ?><h4 class="result-vote-box-item-title"><? print_r($rsQuestionsOwnVote["UF_QUESTIONS_OWN"]) ?></h4><?

                    $userList = [];
                    foreach ($rsQuestionsOwnVote["UF_ANSWER_OWN_GIVEN"] as $key => $value) {
                        $rsUser = CUser::GetByID($value);
                        $arUser = $rsUser->Fetch();
                        $userList[] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"];
                    }

                    ?><div class="result-vote-box-item-it"><?
                    foreach ($rsQuestionsOwnVote["UF_ANSWERS_OWN"] as $key => $value) { ?>

						<div class="result-vote-answer-own-box">
							<p class="result-subtitle-text2"><span class="result-subtitle-text">Ответ: </span><? print_r($value) ?></p>
							<p class="result-subtitle-text2"><span class="result-subtitle-text">Ответивший: </span><? print_r($userList[$key]) ?></p>
							<br>
							<hr>
						</div>
                        <?
                    }
                    ?></div><?

                    ?></div><?
                }
            }

            ?></div><?

        }
    }
    ?>

</div>

<script>

	function viewVote(voteId){
		let answerID = document.querySelector(`#result-vote-box-${voteId}`);
		if (answerID.style.display === "flex"){
			answerID.style.display = "none";
		} else {
			answerID.style.display = "flex";
		}
	}

</script>


<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
