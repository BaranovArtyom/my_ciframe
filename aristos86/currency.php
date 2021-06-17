<?php 
ini_set('display_errors', 'on');
require_once 'funcs.php';

/**получение курса валют из exchange*/
$getCurrency = getCurrency();
// dd($getCurrency);
// dd($getCurrency->CNY);
// dd($getCurrency->UAH);
// dd($getCurrency->RUB);

/**получение валют из мс */
$getCurrencyMC = getCurrencyMC();
// dd($getCurrencyMC);
$currency_ms = array();
    foreach($getCurrencyMC as $currency) {
        // dd($currency);
        $cur['id'] = $currency->id;
        $cur['name'] = $currency->name;
        $cur['rate'] = $currency->rate;
        $cur['code'] = $currency->code;
        $cur['isoCode'] = $currency->isoCode;

        if ($currency->isoCode == 'USD') {/**изменение валют в мс данными из exchange */
            $changeCurrency = changeCurrency($cur['id'], $cur['isoCode'], $cur['code'], $getCurrency->RUB, $cur['name']);
            dd($changeCurrency);
        }

        $currency_ms[] = $cur;
    }
// dd($currency_ms);
// exit;

// $UAH = $getCurrency->UAH;               // получение курса гривны
// $GBR = $UAH/$getCurrency->GBP;          // получение курса фунта в грн
// $RUB = $UAH/$getCurrency->RUB;          // получение курса фунта в грн
// $CNY = $UAH/$getCurrency->CNY; 
// $UAH = number_format($UAH, 2, '.', ''); // округление до 2 цифр после запятой
// echo $UAH.'   '.$GBR.'  '.$RUB.' '.$CNY;


