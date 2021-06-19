<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php

    
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');

    $xml = simplexml_load_file('xml.xml');

    $link = mysqli_connect("127.0.0.1", "sasha", "пароль", "for_xml")
    or die("Ошибка " . mysqli_error($link));
    mysqli_set_charset($link, 'utf8');


    // echo "<pre>";
    // print_r($xml->vendor->goods);
    // echo "<pre>";
    
    // foreach ($xml->vendor as $key){
    //     // print_r($key->goods->code); }

    //     $artnumber = $key->goods->code;
    //     $title = $key->goods->gname;
    //     $price = $key->goods->sprice;
    //     $category_id = $key->goods->categoryId;
    //     $name = $key->goods->category;
    //     $id_product = $category_id;
  
    //     $balance = $key->goods->warehouse1 + $key->goods->warehouse44 +$key->goods->warehouse55 + $key->goods->warehouse5;
                
    

    //     $sql = "INSERT INTO products VALUES (NULL,'$category_id','$price','$title','$balance','$artnumber')";
    //     $result = mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
        
    //     $sql = "INSERT INTO categories VALUES (NULL,'$name','$id_product')";
    //     $result = mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
    // }
    // if($result)
    //             {
    //                 echo "Выполнение запроса прошло успешно";
    //             }
    
?>

<?php 
$query = mysqli_query($link, "SELECT * FROM categories");
$categories = [];

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)){
            $categories[] = $row;
        }
    }
    // var_dump($categories);
?>
<?php 
$query = mysqli_query($link, "SELECT * FROM products");
$products = [];

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)){
            $products[] = $row;
        }
    }
    // var_dump($categories);
?>
    <form action="#" method="post">
        <!-- <select name="categoryName">
        <?//php $categoryList = [];?> 
            <?//php foreach($categories as $category): ?>
               
                <?//php if (!(in_array($category['name'], $categoryList))) :?>    
                            <option value="<?//= $category['id_product'];?> "><?//= $category['name'];?> </option>
                            <?//php $categoryList[] = $category['name'];?> 
                <?//php endif;?>
            
	        <?//php endforeach;?>
            
        </select> -->
        <!-- <select name="artnumberList"> -->

            <?//php foreach($products as $product): ?>
                    <!-- <option value="<?//= $product['balance'];?>"> <?//= $product['artnumber'];?> </option> -->
            <?//php endforeach;?>
        <!-- </select> -->
       
        <!-- <input type="email" value="my_email" maxlength="25" size="20" name="email" autocomplete="off"> -->
        <!-- <button type="submit" value="Submit">Submit</button> -->
    
    </form>
    
    <!-- выводит товары выбранной категории -->
    <?php 
    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         // var_dump($_POST['sku']);
    //         $categoryProduct = $_POST['categoryName'];
    //         $query = mysqli_query($link, "SELECT * FROM products WHERE category_id = '$categoryProduct' ");
                
            
    //             if ($query) {
    //                 while ($row = mysqli_fetch_assoc($query)){
    //                     $products[] = $row;
    //                 }
    //             }
    //             if ($products) {
                   
    //                 foreach($products as $product){
    //                     echo $product['title']."<br>";
    //                 }
                    
    //             }
          
    //     }
    ?>

    <!-- выводит остаток выбранного артикула -->
    <?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $title = $_POST['artnumberList'];
            // var_dump($title);
            echo $title ;
           
          
        }
    ?>
    <?php
    // // USERPWD — реквизиты аутентификации;
    // // RETURNTRANSFER — не только отправляем запрос, но и записываем ответ;
    // function setupCurl($apiSettings)
    //   {
    //       $curl = curl_init();
    //       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //       curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
      
    //     //   $userName = $apiSettings[MOYSKLAD_USERNAME];
    //     //   $userPassword = $apiSettings[MOYSKLAD_PASSWORD];
    //       curl_setopt($curl, CURLOPT_USERPWD, "$userName:$userPassword");
    //       curl_setopt($curl, CURLOPT_USERAGENT, $apiSettings[MOYSKLAD_USER_AGENT]);
    //       return $curl;
    //   }

    // function ms_query_send($link,$data,$type){
    //     global $MS_AUTH;
    //     $send_data=json_encode($data);
    //     $curl=curl_init();
    //     curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    //     curl_setopt($curl,CURLOPT_URL,$link);
    //     curl_setopt($curl,CURLOPT_POST,0);
        
    //     curl_setopt($curl,CURLOPT_USERPWD,$MS_AUTH);
    //     curl_setopt($curl,CURLOPT_HEADER,false);
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    //         'Content-Type: application/json',
    //         'Authorization: Basic '.base64_encode($MS_AUTH)
    //     ));
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "$type");
    //     curl_setopt($curl, CURLOPT_POSTFIELDS,            $send_data);
    //     $out=curl_exec($curl);
    //     curl_close($curl);
    //     $json=json_decode($out, JSON_UNESCAPED_UNICODE);
    //     return $json;
    // }
      
// создание нового ресурса cURL
$ch = curl_init();

// установка URL и других необходимых параметров
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");
curl_setopt($ch, CURLOPT_URL, "https://online.moysklad.ru/api/remap/1.2/entity/assortment");
// curl_setopt($ch, CURLOPT_HEADER, 0);



// загрузка страницы и выдача её браузеру
$out=curl_exec($ch);

// завершение сеанса и освобождение ресурсов
$json=json_decode($out, true);
// $json=json_decode($out, JSON_UNESCAPED_UNICODE);
curl_close($ch);
echo '<pre>';
print_r($json);
echo '</pre>';
for($i=0;$i<count($json['rows']);$i++){
    echo $json['rows'][$i]['name'],"<br>"; 
    echo $json['rows'][$i]['quantity'],"<br>"; 
}  

// echo count($json['rows']);
// var_dump($json);
// function ms_query($link){
// 	global $MS_AUTH;
// 	$curl=curl_init();
// 	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
// 	curl_setopt($curl,CURLOPT_URL,$link);
// 	curl_setopt($curl,CURLOPT_POST,0);
	
//     curl_setopt($curl,CURLOPT_USERPWD,$MS_AUTH);
//     // curl_setopt($curl, CURLOPT_USERPWD, "admin@sashasergienko8385:$userPassword");
// 	curl_setopt($curl,CURLOPT_HEADER,false);
// 	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//         'Content-Type: application/json',
//         'Authorization: Basic '.base64_encode($MS_AUTH)
// 	));
// 	$out=curl_exec($curl);
// 	curl_close($curl);
// 	$json=json_decode($out, JSON_UNESCAPED_UNICODE);
// 	if(isset($json['errors'])){
	
// 		$hand=fopen("errors.log","a+");
// 		fwrite($hand,date("d-m-Y H:i:s",time())." \n $link \n  $out \n");
// 		fclose($hand);
// 	}
// 	return $json;
// }
// ms_query("https://online.moysklad.ru/api/remap/1.2/entity/product");
// var_dump($json);
// $MS_AUTH='admin@sashasergienko8385:9b08fa6d7b';
// $MS_AUTH='login:password';
?>


</body>
</html>
