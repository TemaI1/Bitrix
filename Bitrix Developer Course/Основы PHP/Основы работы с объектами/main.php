<?php

// номер года и месяца, с которых начинать расчёт
$yearMonthNum = [2025, 10];

// количество месяцев, для которых производить расчёт
$numberMonths = 5;

function getWorkSchedule($yearMonthNum, $numberMonths){

    for ($i=0; $i < $numberMonths; $i++) { 
        $date = DateTime::createFromFormat('!m', $yearMonthNum[1]);
        $monthName = $date->format('F'); 
        echo "\n" . "Название месяца: " . $monthName;

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $yearMonthNum[1], $yearMonthNum[0]);

        echo "\n" . "Список всех дней месяца " . $monthName . ": ";

        $nonWorkingDays = 2;

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = new DateTime();
            $date->setDate($yearMonthNum[0], $yearMonthNum[1], $day);

            $weekendDay= (int)$date->format('N');
            if ($weekendDay >= 6) {
                $nonWorkingDays++;
                echo " - \033[33m$day\033[0m"; // желтый - сб, вс
            } else if ($nonWorkingDays < 2){
                $nonWorkingDays++;
                echo " - \033[31m$day\033[0m"; // красный - выходной день
            } else {
                $nonWorkingDays = 0;
                echo " - \033[32m$day\033[0m"; // зеленый - рабочий день
            }
        }

        if ($yearMonthNum[1] >= 12) {
            $yearMonthNum[1] = 1;
            $yearMonthNum[0]++;
        } else {
            $yearMonthNum[1]++;
        }

        echo "\n";
    }

}

getWorkSchedule($yearMonthNum, $numberMonths);