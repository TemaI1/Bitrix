<?php

// номер года и месяца, с которых начинать расчёт
$yearMonthNum = [2025, 4];

// количество месяцев, для которых производить расчёт
$numberMonths = 1;

function getWorkSchedule($yearMonthNum, $numberMonths){

    for ($i=0; $i < $numberMonths; $i++) { 
        $date = DateTime::createFromFormat('!m', $yearMonthNum[1]);
        $monthName = $date->format('F'); 
        echo "\n" . "Название месяца: " . $monthName;
        if ($yearMonthNum[1] >= 12) {
            $yearMonthNum[1] = 1;
        } else {
            $yearMonthNum[1]++;
        }

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $yearMonthNum[1], $yearMonthNum[0]);

        echo "\n" . "Список всех дней месяца " . $monthName . ": ";

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = new DateTime();
            $date->setDate($yearMonthNum[0], $yearMonthNum[1], $day);

            // Получаем номер дня недели
            $dayOfWeek = ($day - 1) % 3;

            $weekendDay= (int)$date->format('N');

            if ($weekendDay >= 6) {
                echo " - \033[33m$day\033[0m"; // желтый
                continue; // пропускаем текущий день
            }

            // Определяем, рабочий или выходной
            if ($dayOfWeek == 0) {
                echo " - \033[32m$day\033[0m"; // зеленый
            } else {
                echo " - \033[31m$day\033[0m"; // красный
            }
        }
        echo "\n";
    }

}

getWorkSchedule($yearMonthNum, $numberMonths);