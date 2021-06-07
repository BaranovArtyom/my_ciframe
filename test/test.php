
<?php

// сюда нужно вписать токен вашего бота
define('TELEGRAM_TOKEN', '1533075351:AAGh_xtaGd5mwyzU__2NEXwVQEmk7VGkVCU');

// сюда нужно вписать ваш внутренний айдишник
define('TELEGRAM_CHATID', '573747406');



message_to_telegram('Привет!sasha');

function message_to_telegram($text)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => TELEGRAM_CHATID,
                'text' => $text,
            ),
        )
    );
    curl_exec($ch);
}
