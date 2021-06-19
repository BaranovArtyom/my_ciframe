<?php 

function dd($val)
{
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}

function getCollections($url) 
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "$url",
    CURLOPT_USERAGENT=> 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
    'Cookie: secure_customer_sig=; cart_currency=USD;
     _y=8689fd9d-5e64-46a8-a141-8b849dd76693;
      _s=897d8864-60f2-4e2b-ab8a-55dc24708576; 
      _shopify_y=8689fd9d-5e64-46a8-a141-8b849dd76693; 
      _shopify_s=897d8864-60f2-4e2b-ab8a-55dc24708576;
       _shopify_fs=2021-02-04T08%3A03%3A34Z'
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);
    curl_close($curl);
    // dd($response);die();
    // получение массива данных из коллекции
    $coll = array();
    foreach($response->collections as $collection) {
        // dd($collection->handle);
        $coll['name'][] = $collection->handle;
        $coll['count_product'][] = $collection->products_count;
    }
    return $coll;
}

function getProductsCollection($urlNameCollection){
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "$urlNameCollection",
  CURLOPT_USERAGENT=> 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Cookie: secure_customer_sig=; cart_currency=USD; _y=8689fd9d-5e64-46a8-a141-8b849dd76693; _s=897d8864-60f2-4e2b-ab8a-55dc24708576; _shopify_y=8689fd9d-5e64-46a8-a141-8b849dd76693; _shopify_s=897d8864-60f2-4e2b-ab8a-55dc24708576; _shopify_fs=2021-02-04T08%3A03%3A34Z'
  ),
));

$response = curl_exec($curl);
$response = json_decode($response) ;
// dd($response);die();
curl_close($curl);

// получение массива данных из продукта
$product = array();
foreach($response->products as $product) {
    // dd($product);
    foreach($product->variants as $var){
        // dd($var);die();
        $prod["colection_id"] = $var->product_id;
        $prod["colection_handle"] = $product->handle;
        $prod["colection_title"] = $product->title;
        $prod["colection_body_html"] = $product->body_html;
        $prod["colection_vendor"] = $product->vendor;
        $prod["colection_product_type"] = $product->product_type;
        $prod["colection_tags"] = implode(",", $product->tags);
        $prod["product_id"] = $var->id;
        $prod["product_title"]= $var->title;
        $prod["product_sku"] = $var->sku;
        $prod["product_price"] = $var->price;
        // $items[] = $prod;
        $prod["images"]=array();
        foreach($product->images as $image) {
            $prod["images"][] = $image->src;
            
        }
        $items[] = $prod;
    }
    
    
}

return $items;
}

// /**
//  * создание xml для заказа в dalli
//  */

// function createXML () {
//     $xml = new DomDocument('1.0','utf-8');
//     $xml->formatOutput = true;

//     $basketcreate = $xml->createElement('basketcreate');   // создание тега basketcreate
//     $xml->appendChild($basketcreate);
//     $auth = $xml->createElement('auth'); 
        


//     // echo '<xmp>'.$xml->saveXML().'</xmp>';
//     return $xml->saveXML();
// }