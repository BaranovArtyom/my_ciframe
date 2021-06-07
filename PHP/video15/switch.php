<?php

//переключение switch

// $someText = 'ok';
// switch ($someText) {
//     case 'value':
//         echo 'equal ok';
//         break;

//     case 'ok':
//         echo 'equal ok my';
//         break;

//     default:
//         echo 'no equal ok';
//         break;
// }
$num = 100;
switch (true) {
    case ($num<100):
        echo 'equal ok';
        break;

    case 'ok':
        echo 'equal ok my';
        break;

    default:
        echo 'no equal ok';
        break;
}