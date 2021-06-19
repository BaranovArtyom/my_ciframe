<?php
ini_set('display_errors', 'on');
require_once "funcs.php";

// $s = __DIR__;
// dd($s);exit;

// dd($_SERVER['DOCUMENT_ROOT']);exit;
/**получение сборщиков */
    $getSborshik = getSborshik();
    $nameZborchikov = array();
        foreach($getSborshik as $name){
            $nameZborchikov[] = $name->name;
        }
    // dd($nameZborchikov);

/**получение контроллеров*/
$getControler = getControler();
$nameControler = array();
    foreach($getControler as $name){
        $nameControler[] = $name->name;
    }
// dd($nameControler);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <div class="py-5 text-center container" >
        <div class = "row py-lg-5">
            <a href="https://ciframe.com.ua/" target="_blank"><img src='https://ciframe.com//images/ciframe_logo_small.png'></a></br></br></br>
                <form action="ms_demand.php" method="post">
                    <h2>Дата от:</h2>
                    <input class="form-control" type="date" name="data_from" required></br>
                    <h2>Дата до:</h2>
                    <input class="form-control" type="date" name="data_to" required></br></br>
                    <h2>Сборщик:</h2>
                    <select class="form-control" name="sborList[]" multiple >
                        <?php $sbor = [];?>
                            <?php foreach($nameZborchikov as $nameZb): ?>
                                
                                <option value="<?= $nameZb;?>">
                                    <?= $nameZb;?>
                                </option>
                                <?php $sbor[] = $nameZb;?> 
                                    
                            <?php endforeach;?>
                        
                    </select></br></br>
                    <h2>Котроллёр:</h2>
                    <select class="form-control" name="controlList[]" multiple >
                        <?php $con = [];?>
                            <?php foreach($nameControler as $nameCn): ?>
                                
                                <option value="<?= $nameCn;?>">
                                    <?= $nameCn;?>
                                </option>
                                <?php $con[] = $nameCn;?> 
                                    
                            <?php endforeach;?>
                        
                    </select></br></br>
                    </br></br>
                    <!-- <button class="btn btn-primary" type="button">Сохранить в файл</button> -->
                    <input class="btn btn-success" type="submit" value="Сохранить в файл">
                </form>
        </div> 
    </div>                          
 </body>
</html>

