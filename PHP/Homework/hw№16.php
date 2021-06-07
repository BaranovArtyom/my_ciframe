<?php  

$i = 0;

while ($i <=50) {
    
    if ($i%11 xor $i==0) {
        echo "$i -непарное ",'<br>';
        $i++;
    }else {
        echo "$i - парное <br>";
        $i++;
    }
}