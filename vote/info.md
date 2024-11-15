Необходимо подключение:

    jQuery
    Bootstrap


Нужно добавить hl-блоки:

    VoteAnswers
    VoteQuestionsOwn
    VoteMain


Нужно добавить пользовательские поля для hl-блока VoteAnswers:

    * Название поля    Тип данных *
    UF_QUESTIONS	Строка
    UF_ANSWER_GIVEN	Строка (Множественное)
    UF_COUNT_VOTES	Целое число (Множественное)
    UF_MULTIPLE_CHOICE	Да/Нет (Подписи для значений:	да-нет, нет-да)
    UF_ANSWERS	Строка (Множественное)


Нужно добавить пользовательские поля для hl-блока VoteQuestionsOwn:

    * Название поля    Тип данных *
    UF_ANSWER_OWN_GIVEN	Строка (Множественное)
    UF_ANSWERS_OWN	Строка (Множественное)
    UF_QUESTIONS_OWN	Строка


Нужно добавить пользовательские поля для hl-блока VoteMain:

    * Название поля    Тип данных *
    UF_VOTING_ANSWERS_OWN_GIVEN  Привязка к элементам highload-блоков (Множественное) привязка к VoteQuestionsOwn (UF_ANSWER_OWN_GIVEN)
    UF_VOTING_ANSWERS_OWN  Привязка к элементам highload-блоков (Множественное) привязка к VoteQuestionsOwn (UF_ANSWERS_OWN)
    UF_VOTING_QUESTIONS_OWN  Привязка к элементам highload-блоков (Множественное) привязка к VoteQuestionsOwn (UF_QUESTIONS_OWN)
    UF_VOTING_ANSWERS_GIVEN  Привязка к элементам highload-блоков (Множественное) привязка к VoteAnswers (UF_ANSWER_GIVEN)
    UF_VOTING_ANSWERS  Привязка к элементам highload-блоков (Множественное) привязка к VoteAnswers (UF_ANSWERS)
    UF_VOTING_QUESTIONS  Привязка к элементам highload-блоков (Множественное) привязка к VoteAnswers (UF_QUESTIONS)
    UF_VIEWED_USER  Привязка к сотруднику (Множественное)
    UF_VOTING_COMPLETION_DATE  Дата
    UF_VOTING_TOPIC  Строка
