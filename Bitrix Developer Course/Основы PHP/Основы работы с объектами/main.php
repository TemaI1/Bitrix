<?php

// номер года и месяца, с которых начинать расчёт
$yearMonthNum = [2025, 2];

// количество месяцев, для которых производить сквозной расчёт графика
$numberMonths = 5;

function getWorkSchedule($yearMonthNum){

    $date = DateTime::createFromFormat('!m', $yearMonthNum[1]);
    $monthName = $date->format('F'); 
    return $monthName;
}

echo getWorkSchedule($yearMonthNum);