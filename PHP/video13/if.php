<?php
//if

// $flag = false;
// if ($flag) {
//     echo 'flag - true';
// }else {
//     echo 'flag - false';
// }

// elseif

$alpha = 'a';
if ($alpha == 'b'):
    ?>
        <h1>Text h1</h1>
    <?php
        elseif ($alpha == 'a'):
    ?>
        <h2>Text h2</h2>
    <?php 
        else:
        endif;
    ?>