На странице оплаты:

```php
$handler = new SberbankHandler([
    'LOGIN' => '', //логин
    'PASSWORD' => '', //пароль
    'ORDER_ID' => 1000, // номер заказа
    'ORDER_AMOUNT' => 1000, //сумма для оплаты
    'CURRENCY' => 'RUB', //валюта для оплаты
    'ORDER_DESCRIPTION' => 1000, // описание заказа
    'NOTIFY_URL' => 'http://domain/result.php', //куда будут отправляться результаты оплаты
    'RETURN_URL' => 'http://domain/result_redirect.php', //куда отправлять пользователя после оплаты
    'TAX_DEFAULT' => 0, // тип НДС [ 0='>Без НДС', 1=>'НДС по ставке 0%', 2=>'НДС чека по ставке 10%', 4=>'НДС чека по ставке 10/110', 6=>'НДС чека по ставке 20%', 7=>'НДС чека по ставке 20/120']
    'TEST_MODE' => 0, // тестовая среда?
    'LOGGING' => 0, // писать логи?
    'NAME' => 'Иван', // имя плательщика
    'EMAIL' => 'info@domain.com', // email плательщика
    'PHONE' => '89009999999', // телефон плательщика

]);
$result = $handler->initiatePay();
```

Результат вызова:

```php
$result = array(
    'sberbank_result' => '...', //ответ сервера банка
    'payment_link' => '...', //ссылка для оплаты
    'currency' => '...', //Валюта оплаты
);
```

На странице NOTIFY_URL:

```php
$handler = new SberbankHandler([
    'LOGIN' => '', //логин
    'PASSWORD' => '', //пароль
    'TEST_MODE' => 0, // тестовая среда?
]);
$result = $handler->processRequest();
```
Результат вызова:

```php
$result = array(
    '...', // данные об оплаченном заказе из банка
    'success' => true, //удача/неудача оплаты
);
```
