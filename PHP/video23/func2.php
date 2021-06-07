<?php

// function getResult($var) {
//     $var = $var * 2;
//     return $var;
// }

// $new = 10 ;
// echo getResult($new)."<br>";

//обязательные параметры

// function getResult ($var1 = 2 , $var2 = 4) {
//     return $var1 * $var2;
// }

// echo  getResult();

// переменное или изменяемое  количество параметров

function mylist (...$item) {
    foreach ($item as $v) {
        echo $v . "<br>" ; 
    }
}

mylist('Nike', 'Mike' , 'Sara');

$some = ['PHP', 'Python' , 'JS' , 'HTML' ];
mylist(...$some);
