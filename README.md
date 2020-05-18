# buffcs
Магазин покупки привилегий CS 1.6 (https://csonelove.ru/threads/11/)

**Используется mysql pdo и bootstrap 4**

#### ИНСТРУКЦИЯ
1. файлы закинуть в корень сайта(там где обычно index.php или index.html)
2. импортировать sql.sql в базу данных csbans
3. в тиблицу amx_admins_servers добавить 3 поля как на скрине https://prnt.sc/s82w13
4. настройки в app/configs/main.php и в db.php
5. настроить крон на выполнение раз в сутки https://site.ru/cron
6. не удаляйте файл unknown.png
7. на папку icons права 777
8. Видео как создать страницу (скоро)

#### МЕТОДЫ ДЛЯ КАСС

**success**
- метод GET
- url: site.ru/success

**error(fail)**
- метод GET
- url: site.ru/error

**result(обработчик)**
- метод POST
- url для freekassa: site.ru/merchant/freekassa
- url для robokassa: site.ru/merchant/robokassa
- url для unitpay: site.ru/merchant/unitpay

#### ВАЖНО
1. Для UnitPay все методы - GET
2. Что бы функция смены привилегии (https://buffcs.gq/account/profile/change) работала корректно, у привилегии должен быть выбор 30ти дней. Не важно какое кол-во дней вы сделаете, главное чтобы был выбор 30ти дней
