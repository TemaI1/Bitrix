Нужно добавить hl-блок:

    QuestionsAnswers (id-1)

Нужно добавить пользовательские поля для hl-блока:

    * Объект    Название поля    Тип данных *
    HLBLOCK_32  UF_QUESTION  Строка (Вопрос)
    HLBLOCK_32	UF_QUESTION_TOPICS  Список (Раздел) id6-Разъяснения, id7-Особые, id8-Приложение, id9-Иное
    HLBLOCK_32	UF_QUESTION_STATUSES  Список (Статус) id1-Создан, id2-Получен ответ, id3-Требуется разъяснение, id4-Закрыт, id5-Неактивен
    HLBLOCK_32	UF_ANSWER  Строка (Ответ)
    HLBLOCK_32	UF_QUESTION_CREATOR  Привязка к сотруднику (Создатель вопроса)
    HLBLOCK_32	UF_ANSWER_CREATOR  Привязка к сотруднику (Отвечающий)
    HLBLOCK_32	UF_HISTORY_QUESTION  Строка (История вопроса)
    HLBLOCK_32	UF_DATE_ANSWER  Дата (Дата вопроса)
