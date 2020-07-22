# buffcs
Магазин покупки привилегий CS 1.6 (https://csonelove.ru/threads/11/)

**Используется mysql pdo и bootstrap 4**

#### ИНСТРУКЦИЯ
1. файлы закинуть в корень сайта(там где обычно index.php или index.html)
2. импортировать sql.sql в базу данных csbans
3. в тиблицу amx_admins_servers добавить 3 поля как на скрине https://prnt.sc/s82w13
4. в таблицу amx_amxadmins добавить 1 поле как на скрине https://prnt.sc/sqcyam
5. в таблице amx_admins_servers изменить поле custom_flags как на скрине https://prnt.sc/sqq6ub
6. настройки в app/configs/main.php и в db.php
7. настроить крон на выполнение раз в сутки(0 0 * * *) команда: 
- **/usr/bin/wget --no-check-certificate -O - -q -t 1 https://site.ru/cron**
8. не удаляйте файл unknown.png
9. на папку icons права 777
10. Видео как создать страницу https://www.youtube.com/watch?v=RaotL9pQAQk
11. Файл log.txt должен быть доступен для записи, права 0777

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
3. Если в папке куда вы планируете устанавить магазин, будет распалагаться csbans какая либо статистика и т.п. они перестанут работать, во избежания этого, установите магазин либо на пустой домен/поддомен либо в отдельную папку 

#### - ДРУГОЕ -
Если хотите из amx_amxadmins перенести все icq в amx_admins_servers в vk, выполните sql запрос:
UPDATE amx_admins_servers t1 INNER JOIN amx_amxadmins t2 ON t1.admin_id = t2.id SET t1.vk = t2.icq
