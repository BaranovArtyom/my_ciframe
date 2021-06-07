<?php

// function getMult( $var1 , int $var2) {
//     global $result; 
//     $result = $var1 * $var2;
    
//     return $result;
// }

// echo getMult( 2, 3);
// echo "<hr>";
// echo getMult($result,3);

function getInfo($name, $age) {
    $mouth = $age *12;
    $day = $mouth * 30;

    return ['name' => $name ,
            'age' => $age ,
            'mouth' => $mouth,
            'day' => $day
            ];

}

echo '<pre>';
print_r(getInfo('Sasha',36));
echo '<pre>';

