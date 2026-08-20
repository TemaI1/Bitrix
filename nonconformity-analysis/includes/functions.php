<?php
// Отключаем прямой доступ к файлу
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Получает массив данных HL-блока по имени его таблицы в БД
function getHlBlockByTableName($tableName) {
    return \Bitrix\Highloadblock\HighloadBlockTable::getList([
        'filter' => ['=TABLE_NAME' => $tableName]
    ])->fetch();
}

// Получает ФИО пользователя, почту и его подразделение
function getUserData($userId)
{
    // Проверяем корректность ID
    if ($userId <= 0) {
        return null;
    }

    // Получаем данные пользователя (ДОБАВЛЕНО ПОЛЕ EMAIL)
    $user = \CUser::GetList(
        'ID',
        'ASC',
        ['ID' => $userId],
        ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL']] // <-- Добавили EMAIL сюда
    )->Fetch();

    if (!$user) {
        return null;
    }

    // Формируем ФИО
    $fio = \CUser::FormatName('#LAST_NAME# #NAME# #SECOND_NAME#', $user, true, false);
    $fio = preg_replace('/\s+/', ' ', trim($fio));

    // Формируем базовую структуру ответа (ДОБАВЛЕНО ПОЛЕ email)
    $result = [
        'id'    => $userId,
        'fio'   => $fio,
        'email' => $user['EMAIL'] ?? '', // <-- Теперь email будет здесь
        'dept'  => null,
    ];

    // Получаем ID подразделений пользователя (может быть несколько)
    $deptIds = \CIntranetUtils::GetUserDepartments($userId);
    if (empty($deptIds)) {
        return $result; // У пользователя нет подразделения
    }

    // Получаем данные первого подразделения пользователя
    $dept = \CIBlockSection::GetList(
        [],
        ['ID' => (int)$deptIds[0]],
        false,
        ['ID', 'NAME']
    )->Fetch();

    if ($dept) {
        $result['dept'] = ['id' => (int)$dept['ID'], 'name' => $dept['NAME']];
    }

    return $result;
}

// Получает почту пользователя
function getUserEmail($userId): string
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return '';
    }

    $user = \CUser::GetList(
        'ID',
        'ASC',
        ['ID' => $userId],
        ['FIELDS' => ['EMAIL']]
    )->Fetch();

    return $user['EMAIL'] ?? '';
}

// Прибавляет к дате указанное количество рабочих дней (Пн-Пт), с учетом праздничных дней
function addWorkingDays($startDate, $days)
{
    // Нормализуем входную дату в формат, который понимает DateTime
    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $startDate)) {
        $currentDate = DateTime::createFromFormat('d.m.Y', $startDate);
    } else {
        $currentDate = new DateTime($startDate);
    }

    // Официальные праздничные дни РФ
    $holidays = [
        '01.01', '02.01', '03.01', '04.01', '05.01', '06.01', '07.01', '08.01', // Новогодние
        '23.02', // День защитника Отечества
        '08.03', // Международный женский день
        '01.05', // Праздник Весны и Труда
        '09.05', // День Победы
        '12.06', // День России
        '04.11'  // День народного единства
    ];

    $added = 0;
    while ($added < $days) {
        $currentDate->modify('+1 day');

        $dayOfWeek = (int)$currentDate->format('N'); // 1=Пн ... 7=Вс
        $currentDayMonth = $currentDate->format('d.m'); // Получаем дату в формате "ДД.ММ"

        // День засчитывается, если это будний день (Пн-Пт) И он не входит в список праздников
        if ($dayOfWeek < 6 && !in_array($currentDayMonth, $holidays, true)) {
            $added++;
        }
    }

    return $currentDate->format('d.m.Y');
}
