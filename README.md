<p align="center">
    <h1 align="center">Вариант выполнения тестового задания PHP Developer от PerfectPanel</h1>
    <br>
</p>


ОТВЕТ НА ЗАДАНИЕ 1
------------------

~~~
SELECT u.id AS ID, CONCAT(u.first_name, " ", u.last_name) AS Name,  b.author AS Author, GROUP_CONCAT(" ", b.name) AS Books FROM users AS u 
INNER JOIN user_books AS ub ON ub.user_id=u.id 
INNER JOIN books AS b ON b.id=ub.book_id 
WHERE u.age BETWEEN 7 AND 17
GROUP BY u.id HAVING COUNT(ub.user_id)=2 AND COUNT(DISTINCT(b.author)) = 1
~~~

ЗАДАНИЕ 2
---------

### Реализация JSON API сервиса на языке php8 для работы с курсами обмена валют для биткоина (BTC) с помощью Docker


УСТАНОВКА
---------

### Установка с помощью Docker

Обновите пакеты поставщиков

    docker-compose run --rm php composer update --prefer-dist
    
Запустите триггеры установки (создание кода проверки файлов cookie)

    docker-compose run --rm php composer install    
    
Запустите контейнер

    docker-compose up -d
    
Приложение будет доступно по адресу:

    http://localhost

Phpmyadmin будет доступен по адресу:

    http://localhost:8888


2.1 Использование метода `rates`
-------------------------------

Для получения курсов валют отправляем `GET` запрос:

    http://localhost/api/v1?method=rates

Пример вывода:
```
{
    "status": "success",
    "code": 200,
    "data": {
        "GBP": 31367.58,
        "EUR": 37520.24,
        "CHF": 39176.66,
        "USD": 42722.64,
        "CAD": 53531.47,
        "SGD": 57276.93,
        "AUD": 59585.28,
        "NZD": 63110.48,
        "RON": 163913.63,
        "PLN": 171229.01,
        "BRL": 238349.62,
        "HRK": 277818.96,
        "DKK": 279076.68,
        "CNY": 281741.41,
        "SEK": 304174.95,
        "HKD": 336070.34,
        "TRY": 584445.74,
        "CZK": 920397.48,
        "THB": 1438898.58,
        "TWD": 1777194.32,
        "RUB": 3334224.5,
        "INR": 3418062.56,
        "JPY": 4902583.28,
        "ISK": 10550320.72,
        "HUF": 15727054.66,
        "CLP": 35350619.19,
        "KRW": 51810255.23
    }
}
```
Для получения курса одной валюты, необходимо в `GET` запросе также передать параметр `currency` с указанием необходимой валюты, например:

    http://localhost/api/v1?method=rates&currency=RUB

Пример вывода:
```
{
    "status": "success",
    "code": 200,
    "data": {
        "RUB": 3276725.96
    }
}
```

2.2 Использование метода `convert`
---------------------------------

Для конвертации валюты из `BTC` необходимо отправить `POST` запрос на `localhost/api/v1` со следующими параметрами:
 - `method`: convert
 - `currency_from`: BTC
 - `currency_to`: <валюта>
 - `value`: <количество валюты для обмена>

Например:

    http://localhost/api/v1?method=convert&currency_from=BTC&currency_to=RUB&value=0.01

Пример вывода:
```
{
    "status": "success",
    "code": 200,
    "data": {
        "currency_from": "BTC",
        "currency_to": "RUB",
        "value": "0.01",
        "converted_value": 32759.58,
        "rate": 3275958.1
    }
}
```
где `rate` - обменный курс, `converted_value` - сумма обмена.

Для конвертации валюты в `BTC` необходимо отправить `POST` запрос на `localhost/api/v1` со следующими параметрами:
 - `method`: convert
 - `currency_from`: <валюта>
 - `currency_to`: BTC
 - `value`: <количество валюты для обмена>

Например:

    http://localhost/api/v1?method=convert&currency_from=RUB&currency_to=BTC&value=1000000

Пример вывода:
```
{
    "status": "success",
    "code": 200,
    "data": {
        "currency_from": "RUB",
        "currency_to": "BTC",
        "value": "1000000",
        "converted_value": "0.3056000000",
        "rate": "0.0000003056"
    }
}
```
где `rate` - обменный курс, `converted_value` - сумма обмена.