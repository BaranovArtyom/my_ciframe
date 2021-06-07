<?php
// echo $_FILES['filename']['size']."<br>";
// echo $_FILES['filename']['name']."<br>";
// echo $_FILES['filename']['tmp_name']."<br>";

if (move_uploaded_file($_FILES['filename']['tmp_name'],'temp/'.$_FILES['filename']['name'])){
    if ($_FILES['filename']['size']> 2*1024*1024){
        echo 'size file more 2 mb';
        exit();
    }else{
        echo 'file copy on server <br>';
        echo 'character file:<br>';
        echo 'name file: ';
        echo $_FILES['filename']['name'].'</br>';
        echo 'size file: ';
        echo $_FILES['filename']['size'].'<br>';
        echo 'type file: ';
        echo $_FILES['filename']['type'].'<br>';
    }
}else {
    echo 'file not copy';
}

?>