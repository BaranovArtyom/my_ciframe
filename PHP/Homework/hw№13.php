<?php
/**
 * Создаем переменную со значением времени (от 00 до 23)только часы, без минут.
Создаем вторую переменную (timeIs), присваивая туда значение 0;
Используя условный оператор определяем какое время и даем рекомендации
Например если промежуток с 23 по 06 присваиваем в переменную 
timeIs = 1, если с 07 по 15 timeIs = 2 и т.д.
Можете поделить 24 часа на любое количество, но минимум 3!
Подумайте при помощи чего вы будете проверять промежутки
Создайте второе условие if с перемешкой html и проверяя переменную timeIs значение выводите в теге <h1>Сейчас ночь </h1>, ну или что-то другое в зависимости от диапазона времени

 */
$setTime = 7;
$timels = 0;

if ($setTime>=23 || $setTime<=6):
    ?>
    <h1>Now night</h1>
    <?php
else:
    ?>
      <h1>Now morning</h1>
    <?php
    endif;
    ?>