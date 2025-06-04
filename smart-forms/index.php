<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$APPLICATION -> SetTitle("Смарт форма");

use Bitrix\Main\Page\Asset;

CJSCore ::Init(["jquery"]);
CModule ::IncludeModule('highloadblock');
Asset ::getInstance() -> addJs("/smart-forms/script.js");
Asset ::getInstance() -> addCss("/smart-forms/style.css");
?>

<div class="all-content">
	<p class="notification"></p>
	<table class="form-table" style="border-collapse: separate; border-spacing: 1px;">
		<tr>
			<td class="main-point">№</td>
			<td class="main-point">Дата проблемы</td>
			<td class="main-point">Инициатор</td>
			<td class="main-point">Описание проблемы</td>
			<td class="main-point">Описание решения</td>
			<td class="main-point">Описание этапа</td>
			<td class="main-point">Исполнитель</td>
			<td class="main-point">Срок</td>
			<td class="main-point">Пояснение</td>
			<td class="main-point">Перенос</td>
			<td class="main-point">Дата закрытия</td>
			<td class="main-point">Статус</td>
			<td class="main-point">Действия</td>
		</tr>
        <?

        // получение объекта сущности highloadblock
        function getHBbyName($code)
        {
            $hlblock = Bitrix\Highloadblock\HighloadBlockTable ::getList([
                'filter' => ['=TABLE_NAME' => $code],
            ]) -> fetch();
            $entity_data_class = (Bitrix\Highloadblock\HighloadBlockTable ::compileEntity($hlblock)) -> getDataClass();
            return $entity_data_class;
        }

        // получение проблем
        $smartFormHL = getHBbyName("b_hlbd_smartform");
        $rsSmartForm = $smartFormHL ::getList(
            [
                "select" => ["*"],
                "order"  => ["ID" => "ASC"],
                "filter" => [],
            ]
        );
        $problems = [];
        $problemsData = [];
        while ($arSmartForm = $rsSmartForm -> Fetch()) {
            $problems[] = $arSmartForm["ID"];
            $problemsData[$arSmartForm["ID"]]["UF_PROBLEM_DATE"] = $arSmartForm["UF_PROBLEM_DATE"];
            $problemsData[$arSmartForm["ID"]]["UF_INITIATOR"] = $arSmartForm["UF_INITIATOR"];
            $problemsData[$arSmartForm["ID"]]["UF_DESCRIPTION_PROBLEM"] = $arSmartForm["UF_DESCRIPTION_PROBLEM"];
        }

        // получение решений
        $smartFormSolutionHL = getHBbyName("b_hlbd_smartformsolution");
        $rsSmartFormSolution = $smartFormSolutionHL ::getList(
            [
                "select" => ["*"],
                "order"  => ["ID" => "ASC"],
                "filter" => ['UF_ID_PROBLEM' => $problems],
            ]
        );
        $solutions = [];
        $problemsIDStages = [];
        while ($arSmartFormSolution = $rsSmartFormSolution -> Fetch()) {
            $solutions[] = $arSmartFormSolution["ID"];
            $problemsIDStages[$arSmartFormSolution["ID"]] = $arSmartFormSolution["UF_ID_PROBLEM"];
            $problemsData[$arSmartFormSolution["UF_ID_PROBLEM"]]["UF_SOLUTION"][$arSmartFormSolution["ID"]] = $arSmartFormSolution["UF_SOLUTION"];
        }

        // получение этапов
        $smartFormStagesHL = getHBbyName("b_hlbd_smartformstages");
        $rsSmartFormStages = $smartFormStagesHL ::getList(
            [
                "select" => ["*"],
                "order"  => ["ID" => "ASC"],
                "filter" => ['UF_ID_SOLUTION' => $solutions],
            ]
        );
        $stages = [];
        while ($arSmartFormStages = $rsSmartFormStages -> Fetch()) {
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["STAGES"] = $arSmartFormStages["UF_STAGES"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["EXECUTOR"] = $arSmartFormStages["UF_EXECUTOR"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["DECISION_DATE"] = $arSmartFormStages["UF_DECISION_DATE"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["EXPLANATION"] = $arSmartFormStages["UF_EXPLANATION"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["CHANGE_DATE"] = $arSmartFormStages["UF_CHANGE_DATE"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["CLOSING_DATE"] = $arSmartFormStages["UF_CLOSING_DATE"];
            $problemsData[$problemsIDStages[$arSmartFormStages["UF_ID_SOLUTION"]]]["UF_STAGES"][$arSmartFormStages["UF_ID_SOLUTION"]][$arSmartFormStages["ID"]]["STATUS"] = $arSmartFormStages["UF_STATUS"];
        }

        foreach ($problems as $id => $problem) {
            $totalCount = 0;
            foreach ($problemsData[$problem]["UF_STAGES"] as $subArray) {
                $totalCount += count($subArray);
            }

            ?>
		<form id="smart-form<?= $problem ?>" method="post" onsubmit="updateCell(event);">
			<input type="hidden" value="<?= $problem ?>" name="elemHL" readonly>
			<tr>
				<td rowspan="<?= $totalCount + 1 ?>">
					<p><?= $problem ?></p>
				</td>
				<td rowspan="<?= $totalCount + 1 ?>">
					<p><?= date("d.m.Y", strtotime($problemsData[$problem]["UF_PROBLEM_DATE"])) ?></p>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<input type="date" class="smart-input" name="problemDate">
					</div>
				</td>
				<td rowspan="<?= $totalCount + 1 ?>">
					<p><?= $problemsData[$problem]["UF_INITIATOR"] ?></p>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<select class="smart-input" name="initiator">
							<option value="0" disabled="" selected="selected">Не выбрано</option>
							<option value="нзхк">нзхк</option>
							<option value="проминн">проминн</option>
							<option value="схк">схк</option>
						</select>
					</div>
				</td>
				<td rowspan="<?= $totalCount + 1 ?>">
					<p><?= $problemsData[$problem]["UF_DESCRIPTION_PROBLEM"] ?></p>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<textarea class="smart-input" type="text" name="descriptionProblem"></textarea>
					</div>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<input class="problem<?= $problem ?> add-solution-btn" type="button" value="Добавить решение &#8594" onclick="addSolution(<?= $problem ?>)">
					</div>

				</td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td style="visibility: hidden; margin: 0; padding: 0; border: none;"></td>
				<td rowspan="<?= $totalCount + 1 ?>">
					<div class="box-btns-cell">
						<input class="box-btns-btn change-result-btn-save change-result-btn-save<?= $problem ?>" type="submit" value="Сохранить" style="display: none">
						<input class="box-btns-btn change-result-btn change-result-btn<?= $problem ?>" type="button" value="Изменить" onclick="changeItem(<?= $problem ?>)">
						<input class="box-btns-btn change-result-btn-delete change-result-btn-delete<?= $problem ?>"
							   type="button" value="Удалить" style="display: none" onclick="dellCell(<?= $problem ?>)">
					</div>
				</td>
			</tr>

            <?
            foreach ($problemsData[$problem]['UF_SOLUTION'] as $solID => $solData) {
                ?>
				<tr>
				<td rowspan="<?= count($problemsData[$problem]['UF_STAGES'][$solID]) ?>" style="position: relative;">
					<p><?= $solData ?></p>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<textarea class="smart-input" type="text" name="descriptionSolution<?= $solID ?>"></textarea>
					</div>
					<div class="smart-box<?= $problem ?>" style="display: none">
						<input class="solution<?= $solID ?> add-stages-btn" type="button" value="Добавить этап &#8594" onclick="addStage(<?= $solID ?>)">
					</div>
					<div class="smart-box<?= $problem ?> del-solution-btn-box" style="display: none">
						<input class="solution<?= $solID ?> del-solution-btn" type="button" value="x" onclick="delSolution(<?= $problem ?>, <?= $solID ?>)">
					</div>
				</td>
				</td>
                <?
                foreach ($problemsData[$problem]['UF_STAGES'][$solID] as $stageID => $stageData) {
                    ?>
					<td style="position: relative;">
						<p><?= $stageData["STAGES"] ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<textarea class="smart-input" type="text" name="descriptionStages<?= $stageID ?>"></textarea>
						</div>
						<div class="smart-box<?= $problem ?> del-stages-btn-box" style="display: none">
							<input class="stages<?= $stageID ?> del-stages-btn" type="button" value="x" onclick="delStage(<?= $solID ?>, <?= $stageID ?>)">
						</div>
					</td>
					<td>
						<p><?= $stageData["EXECUTOR"] ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<textarea class="smart-input" type="text" name="executorStages<?= $stageID ?>"></textarea>
						</div>
					</td>
					<td>
						<p class="date-info"><?= date("d.m.Y", strtotime($stageData["DECISION_DATE"])) ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<input type="date" class="smart-input" name="decisionDateStages<?= $stageID ?>">
						</div>
					</td>
					<td>
						<p><?= $stageData["EXPLANATION"] ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<textarea class="smart-input" type="text" name="explanationStages<?= $stageID ?>"></textarea>
						</div>
					</td>
					<td>
						<p class="date-info"><?= date("d.m.Y", strtotime($stageData["CHANGE_DATE"])) ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<input type="date" class="smart-input" name="changeDateStages<?= $stageID ?>">
						</div>
					</td>
					<td>
						<p class="date-info"><?= date("d.m.Y", strtotime($stageData["CLOSING_DATE"])) ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<input type="date" class="smart-input" name="closingDateStages<?= $stageID ?>">
						</div>
					</td>
					<td>
						<p class="status-info"><?= $stageData["STATUS"] ?></p>
						<div class="smart-box<?= $problem ?>" style="display: none">
							<select class="smart-input" name="statusStages<?= $stageID ?>">
								<option value="0" disabled="" selected="selected">Не выбрано</option>
								<option value="В работе">В работе</option>
								<option value="Завершено">Завершено</option>
							</select>
						</div>
					</td>
					</tr>
                    <?
                }
            }

            ?></form><?
        }
        ?>

	</table>
	<div class="box-btns-form">
		<input class="form-btn" type="button" value="Добавить строку" onclick="addCell()">
	</div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
