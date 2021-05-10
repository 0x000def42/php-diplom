# Общее

Внимательно читайте каждый файл проекта.
Удалите !ВСЕ! комментарии на русском, 
они излишне подробны, комиссия все поймет.

А так-же удалите этот файл :)

Для проекта требуется:
Установленный php (использовалась версия 7.4)
Установленный mysql
У php требуется расширение php-mysqli
В дев режиме можно запустить через php -S localhost:3000, находясь в корне проекта
Дамп базы данных прикладываю, его можно загрузить через phpmyadmin.
Креды для подключения к базе данных нужно заменить в файле api.php на строке 52
Можно запустить с помощью apache, можно использовать готовые сборки 
openserver/denver/xampp/lamp и так далее, закинув папку проекта
в одно из мест, рекомедуемое инструментом, я рекомедую openserver для виндовс.
При деплое необходимо настроить smtp сервер, а так же крон задачу, 
дергающую url api.php?action=notify раз в день.

# Разработка клиентской части юр. фирмы

Что бы сделано:
- Задизайнен макет
- Проведена разметка
- Написаны стили
- Добавлена обработка форм, а так же подтягивание данных с бекенда

В проекте имеется 3 файла (не считая картинок):
index.html
public/styles.css
public/scripts.js

Контент страницы поделен на 3 главные секции:
- заголовок
- основной контекнт
- футер

Заголовок представляет из себя панель навигации, которая позволяет 
переходить по артиклям с помощью html якорей, а так же область с 
названием компании и описанием, и картинкой на фоне.

Далее в основном контенте расположены следующие блоки:
- topics - описание чем занимется компания
- feedback-form - невидимый блок, которые появляется если в 
странице есть query параметр secret_code, например,
localhost:3000?secret_code=123456
- about - описание компании
- clients - блок с захардкоженными отзывами клиентов
- feedbacks - блок с обезличенными отзывами, которые идут с бекенда
- video - iframe с ютуба
- form - форма записи на консультацию

Немного о содержимом файлов.

Верстка построена семантическим образом, если элемент используется в js 
скрипте или в качестве якоря для перехода на него - у него есть id.

Стили в осномном представляют из себя работу со шрифтами, а так-же padding/margin 
и базовые возможности flexbox.

article - независимый блок, их можно менять местами, или вынести в другой проект
section - в основном, является контентной частью блока article

В каждом article, по возможности, есть свой тег-заголовок.
Класс для тега формируется в зависимости от его вложенности.

Разработка велась следующим образом:
- Был подготовлен шаблон на figma
- В первую очередь была описана html структура, проставлены классы
- Для каждого класса был добален соответствующий css селектор
- Сначала копировались стили из figma (шрифты, цвет, размеры, границы)
- Затем, самостоятельно писались стили позиционирования (padding, margin, flex, position и тп.)

# Разрабокта серверной части юр. фирмы

Что бы сделано:
- Спроктирована база данных
- Спроетировано api
- Реализован роутер
- Реализованы экшены
- Написаны sql запросы к базе

Задача состояла из разработки следующих ендпоинтов:

`GET ?action=get_feedbacks&page=num` - получить список отзывов.
Нужен для вывода отзывов на клиентской части проекта.
Возвращает 5 отзывов для конкретного параметра "page"
Формат ответа: [{message, date, name, id }]
id - id отзыва
message - отзыв пользователя
date - дата отзыва
name - имя автора отзыва
num - номер страницы

`POST ?action=create_consultation` - создать консультацию.
Нужен для создания консультации и уведомления администрации.
Формат запроса: {name, phone}
name - имя пользователя
phone - телефон пользователя
Формат ответа {secret_code}
secret-code - код, которй нужен будет для создания отзыва.
(отзыв можно оставить только если была консультация)

`POST ?action=create_feedback` - создать отзыв.
Нужен для добавления отзывов от клиентов на клиентское приложение.
Формат запроса: {secret_code, message}
secret_code - секретный код от создания консультации
message - тело сообщения
Строка ответа: {"ok":"ok"}.

`GET ?action=notify` - запускает скрипт, который отсылает
всем консультациям, которые старше недели и не получали ещё такой нотификации,
предложение оставить отзыв с ссылкой.

Каждый ендпоинт в случае неудачи отвечает в формате {error => message}.

Проект представляет из себя json-api, было создано 5 php файла:
api.php, actions/create_consultation.php, actions/create_feedback.php, actions/get_feedbacks.php, actions/notify.php

index.php - точка входа, инициализация подключение к базе данных, роутинг, подключение хандлеров, вызов хандлера.
Остальные файлы описывают класс и имеют 1 метод call, принимающий в качестве параметра клиент базы данных.
Внутри call происходит логика конкретного экшена.

Был использован вариант роутинга через query_parameters, как самый интуитивно простой в реализации.
Для подключения к базе данных используется адаптер mysqli, из-за большой поддержки хостингами, из альтернатив,
PDO требует, помимо расширения pdo для php ещё и сам адаптер pdo-mysql.

Любой ответ сервера имеет примерно следующий вызов:
`echo json_encode(["some param" => "some data"])`, после чего происходит обрыв запроса методом exit.