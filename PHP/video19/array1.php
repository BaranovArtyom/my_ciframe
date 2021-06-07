<?php   
//ассоциативные и индексные массивы

// $arr = ['name' => 'sasha', 'age' => 31];
// $arr2 = ['sasha',31];

// echo '<pre>';
// print_r($arr);
// echo '</pre>';

// echo '<pre>';
// print_r($arr2);
// echo '</pre>';
// многомерные массивы

$people = [
    'Ivan' => ['age' => 21 , 'weight' => 63],
    'Oleg' => ['age' => 25 , 'weight' => 73],
    'Nika' => ['age' => 29 , 'weight' => 53]

];

// echo $people['Oleg']['weight'];

// echo '<pre>';
// print_r($people['Oleg']);
// echo '</pre>';

//интерполяция элемента массива в строку

// $arr3[0] = 11;
$arr3['time'] = 11;

echo " Сейчас у нас $arr3[time] утра";

echo "Олег имеет вес в  {$people['Oleg']['weight']} кг.";






