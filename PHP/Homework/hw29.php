<?php 

$arr = [2,'tr', 'ds', 5];
echo $str = implode(',',$arr);

$bigStr = "A, b, c, d, e, f, g, e, 
        i, h,a, b, c, d, e, f, g, e, i, ha,
        b, c, d, e, f, g, e, i, ha, b, c, d,
        e, f, g, e, i, ha, b, c, d, e, f, g,
        e, i, ha, b, c, d, e, f, g, e, i, ha,
        b, c, d, e, f, g, e, i, ha, b, c, d,
        e, f, g, e, i, ha, b, c, d, e, f, g,
        e, i, ha, b, c, d, e, f, g, e, i,
        ha, b, c, d, e, f, g, e, i, h";
echo str_ireplace("a" , "+", $bigStr);