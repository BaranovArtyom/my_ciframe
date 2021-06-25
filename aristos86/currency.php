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
        $curs = round($getCurrency->RUB,0);

        if ($currency->isoCode == 'USD') {/**изменение валют в мс данными из exchange */
            $changeCurrency = changeCurrency($cur['id'], $cur['isoCode'], $cur['code'], $curs, $cur['name']);
            dd($changeCurrency);
            file_put_contents('logger.log',date('Y-m-d H:i:s').'  update - '.$getCurrency->RUB."\n",FILE_APPEND);
        }

        $currency_ms[] = $cur;
    }