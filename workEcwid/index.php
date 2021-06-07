<?php 
require_once 'functions.php';
ini_set('display_errors', 'on');
/**
 * получение одного заказа из ecwid
 */
//$orders = curlGet('https://app.ecwid.com/api/v3/39009855/orders?token=secret_v9Uh2jaEqNFLXZcR67eEsXgQyTiKgGpW&limit=1&paymentStatus=PAID');
$orders = curlGet('https://app.ecwid.com/api/v3/39009855/orders?token=secret_v9Uh2jaEqNFLXZcR67eEsXgQyTiKgGpW&limit=1');
// dd($orders);die();
$orderEcwidPaid = [];
foreach ( $orders->items as $orderEcw ) {
    // получение персональных данных клиента 
    $CustomerEcwid['fullName'] = $orderEcw->shippingPerson->name;
    $CustomerEcwid['firstName'] = $orderEcw->shippingPerson->firstName;
    $CustomerEcwid['lastName'] = $orderEcw->shippingPerson->lastName;
    $CustomerEcwid['street'] = $orderEcw->shippingPerson->street;
    $CustomerEcwid['city'] = $orderEcw->shippingPerson->city;
    $CustomerEcwid['country'] = $orderEcw->shippingPerson->countryName;
    $CustomerEcwid['postalCode'] = $orderEcw->shippingPerson->postalCode;
    $CustomerEcwid['phone'] = $orderEcw->shippingPerson->phone;
    $CustomerEcwid['email'] = $orderEcw->email;
    $CustomerEcwid['shippingOption'] = $orderEcw->shippingOption->shippingMethodName;
    $CustomerEcwid['shippingRate'] = $orderEcw->shippingOption->shippingRate;
    $CustomerEcwid['paymentStatus'] = $orderEcw->paymentStatus;
    
    // dd($orderEcw->shippingPerson);die();
    // dd($CustomerEcwid);die();
  
    if ( $orderEcw->paymentStatus ) {                         // статус заказа
        if ( !empty( $orderEcw->items ) ) {
            $orderShopify['id'] = $orderEcw->id; // создания в shopify
            // $orderShopify  = (array)$orderEcw->items;

            foreach ( $orderEcw->items as $item ) { 
                $orderShopify['sku'][] = $item->sku;
                $orderShopify['quantity'][] = $item->quantity;
                // $orderEcwidPaid[] = $orderShopify;
            } 
            $orderEcwidPaid = $orderShopify;
        }
    }
}
dd($orderEcwidPaid);
// exit();
// die();
// dd($orderEcw->paymentStatus );die();
/**
 * получение продуктов из Shopify для variant_id по sku
 */
if( !empty( $orderEcwidPaid ) ){
    $productsShopify = curlGet('https://42fd554a8afea079134933a5babaa41a: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@dashahappyway-pl.myshopify.com/admin/api/2020-10/products.json');
        // dd($productsShopify->products);
        // dd($orderEcwidPaid['sku']);
        foreach( $orderEcwidPaid['sku']  as $key=>$skuEcwid ) {  //$skuEcwid[->sku]
            foreach( $productsShopify->products as $product ) {
                        // dd($product);die();
                        // dd($key);
                        foreach( $product->variants as $variant ) {
                                dd($variant->sku);
                            if ($skuEcwid == $variant->sku ) {
                                // $positions[] = ['variant_id'=$variant->id, 'q']
                                // $variant_id[] = $variant->id;  
                                // $quantity[] = $orderShopify['quantity'][$key];
                                $prices[] = $variant->price;
                                $positions[] = [
                                    'variant_id' => $variant->id,
                                    'quantity' => $orderShopify['quantity'][$key],
                                ];
                            }
                        }
                       
                    }

            
        }
        // dd($positions);die();
        // $quantity[1] = 2;
        // dd($variant_id);
        // dd($quantity);


        // foreach( $orderEcwidPaid  as $order ) {
        //     // dd( $order );
        //     // dd($order['sku'][0]);
        //     // foreach ($order['sku'] as $sku) {
        //     //     dd($sku);
        //     // }
        //     foreach( $productsShopify->products as $product ) {
        //         // dd($key);
        //         foreach( $product->variants as $variant ) {
        //             // foreach ($order['sku'] as $key=>$sku) {
        //             //     // dd($sku);
        //             //     // dd($variant->sku );
        //             //     // dd($order['sku'][$key]);
        //             //     // dd($order['sku']);
        //             //     if( $sku[$key] == $variant->sku ) {
        //             //         $variant_id[] = $variant->id;                // вариант для создания заказа в shopify
        //             //         $quantity[] = $order['quantity'][$key];      // количество для создания заказа в shopify
        //             //         $price[] = $variant->price;
        //             //     }
        //             // }

        //         }
        //     }  
        // }           
}    

// dd( $order);
// dd($variant_id);
// die();
/**
 * получение всех заказов на shopify
 */
$orderAllShopify = curlGet('https://42fd554a8afea079134933a5babaa41a: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@dashahappyway-pl.myshopify.com/admin/api/2020-10/orders.json?status=any');
// dd($orderAllShopify);die();
foreach ( $orderAllShopify->orders as $orderShop ) {
    $ordersName[] = $orderShop->name;
}
// dd($ordersName);die();
// dd($orderShopify['id']);die();
if ( in_array('#'.$orderShopify['id'], $ordersName)) {  //проверка на существование заказа ecwid в shopify
    echo 'уже есть заказ';
}else {                                                // получаем данный для заказа и добавляем заказ
    // $lineItems['variant_id'] =  $variant_id;
    // $lineItems['quantity'] = $quantity;
    
    $shippingAddress['first_name'] = $CustomerEcwid['firstName'];
    $shippingAddress['last_name'] = $CustomerEcwid['lastName'];

    if (empty($shippingAddress['first_name']) || empty($shippingAddress['last_name'])) {
        $shippingAddress['first_name'] = $CustomerEcwid['fullName'];
        $shippingAddress['last_name'] = $CustomerEcwid['fullName'];
    }
    $shippingAddress['address1'] = $CustomerEcwid['street'];
    $shippingAddress['phone'] = $CustomerEcwid['phone'];
    $shippingAddress['city'] = $CustomerEcwid['city'];
    $shippingAddress['country'] = $CustomerEcwid['country'];

    $shippingLines['title'] = $CustomerEcwid['shippingOption'];
    $shippingLines['price'] = $CustomerEcwid['shippingRate'];
    $shippingLines['code'] = $CustomerEcwid['shippingOption'];

    $transactions['kind'] = 'sale';
    $transactions['status'] = 'success';
    foreach ( $prices as $key=>$price ){
        // dd($price);die();
        $transactions['amount'] +=  $price * $orderEcwidPaid['quantity'][$key] ;
    }
    dd($transactions['amount']);

    $discount_codes['code'] = $orderShopify['id'];
    // $discount_codes['amount'] = "9.00";
    // $discount_codes['type'] = 'percentage';
    

    
    // $customer['email'] = $CustomerEcwid['email'];
    // $customer['first_name'] = $CustomerEcwid['firstName'];
    
    $ord["line_items"] = $positions;
    $ord['email'] = $CustomerEcwid['email'];
    $ord['name'] = '#'.$orderShopify['id'];
    $ord["shipping_address"] = $shippingAddress;
    $ord["transactions"][] = $transactions;

    switch ( $CustomerEcwid['paymentStatus'] ) {
        case 'AWAITING_PAYMENT':
            $CustomerEcwid['paymentStatus']  = 'pending';
            break;
        case 'PAID':
            $CustomerEcwid['paymentStatus']  = 'paid';
            break;
        case 'REFUNDED':
            $CustomerEcwid['paymentStatus']  = 'refunded';
            break;
        case 'CANCELLED':
            $CustomerEcwid['paymentStatus']  = 'voided';
            break;
        case 'PARTIALLY_REFUNDED':
            $CustomerEcwid['paymentStatus']  = 'partially_refunded';
            break;
        default;
            $CustomerEcwid['paymentStatus']  = 'unpaid';
            break;
    }
  
    // dd($CustomerEcwid['paymentStatus']);die();
    $ord['financial_status'] = $CustomerEcwid['paymentStatus'];
    $ord["discount_codes"][] = $discount_codes;
    $ord["shipping_lines"][] = $shippingLines;

    // $body = [];
    $body["order"] = $ord;
    dd($body);
    $createOrders = myCurlPost('https://42fd554a8afea079134933a5babaa41a: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@dashahappyway-pl.myshopify.com/admin/api/2020-10/orders.json',$body);
    
    // if ($createOrders)
    dd($createOrders);
    echo "success";
}
die();
