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
        if ($today <= $completionDate){
            continue;
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
            <p class="info-vote-date-title"><? print_r(date("d.m.Y", strtotime($rsVote["UF_VOTING_COMPLETION_DATE"]))) ?></p>
            <p class="info-vote-date-subtitle">дата завершения</p>
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
        }

        if ($userVote == false) {
            ?>
            <p class="vote-completed" style="color: #A92F2F">Опрос не пройден</p>
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

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
