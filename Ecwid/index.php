<?php 

require_once 'functions.php';
// ini_set('display_errors', 'on');
/**
 * получение одного заказа из ecwid
 */

$orders_dir = 'sync_orders/';       

if (!file_exists($orders_dir)) {      //создание папки для хранения id заказов
    mkdir($orders_dir, 0777, true);
}

// $orders = curlGet('https://app.ecwid.com/api/v3/39009855/orders?token=secret_v9Uh2jaEqNFLXZcR67eEsXgQyTiKgGpW&limit=1&paymentStatus=PAID');
$orders = curlGet('https://app.ecwid.com/api/v3/40285098/orders?token=secret_FT8XpgDf46C8cAByFdpMpRwVJS1zijzk&fulfillmentStatus=AWAITING_PROCESSING');
// $orders = curlGet('https://app.ecwid.com/api/v3/40285098/orders?token=secret_FT8XpgDf46C8cAByFdpMpRwVJS1zijzk&vendorOrderNumber=WXVDF');

// $orders = curlGet('https://app.ecwid.com/api/v3/39009855/orders?token=secret_v9Uh2jaEqNFLXZcR67eEsXgQyTiKgGpW&vendorOrderNumber=V7SSF');
// dd($orders);die();
$orderEcwidPaid = [];
// $CustomerEcwidPersonData = CustomerEcwidPersonData($orders->items); //получение данных по заказам
// dd($CustomerEcwidPersonData);
$productsShopify = curlGet('https://966f3aa3f94d3e0e052ecd5f661161c6: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@keepongivingjournals.myshopify.com/admin/api/2020-10/products.json');

if (!empty($orders)){ 
    foreach ( $orders->items as $orderEcw ) {
        // dd($orderEcw->id);die();
        if (!file_exists($orders_dir . $orderEcw->id)) {
            file_put_contents($orders_dir . $orderEcw->id, '');  // записываем id заказа в папку заказов для проверки был уже или нет
            $positions = array();
            $prices = array();
            $transactions = array();
            $shippingLines = array();
            $customer = array();
            $discount = array();
            $tax_lines = array();
            $discount_applications= array();
          
            foreach ( $orderEcw->items as $item ) {    
            //    dd($item->discounts);die();
                foreach($item->discounts as $disc){
                    // dd($disc->discountInfo->value);die();
                    // if ($disc->discountInfo->type == 'PERCENT') {
                    //     $type = 'percentage';
                    // }
                    // $discount_applications[] = [
                    //     "description" => "Custom discount",
                    //     "value" => '30',
                    //     "value_type" => 'percentage',
                    // ];
                }
                foreach ($item->taxes as $tax){
                    // dd($tax);die();
                    $tax_lines[] = [
                        'price' => $tax->total,
                        'rate' => $tax->value/100,
                        'title' => $tax->name,
                    ];
                }          
                foreach( $productsShopify->products as $product ) {
                    foreach( $product->variants as $variant ) {
                        if ( $item->sku == $variant->sku )  {
                            $prices[] = $variant->price;
                          
                            $positions[] = [
                                'variant_id' => $variant->id,
                                'quantity' => $item->quantity,
                            ];
                            $transactions[] = [
                                'kind' => 'sale',
                                'status' => 'success',
                                // 'amount' => $variant->price*$item->quantity,
                                'amount' => round($variant->price,2)*$item->quantity,

                            ];
                            $shippingLines[] = [
                                'title' => $orderEcw->shippingOption->shippingMethodName,
                                'price' => $orderEcw->shippingOption->shippingRate,
                                'code' => $orderEcw->shippingOption->shippingMethodName
                            ];
                            
                            // $discount[] = [
                            //     "code" => "Custom discount",
                            //     // "amount" => $orderEcw->discount,
                            //     "type" => "fixed_amount",
                            // ];
                        }
                       
                         //создаем body для заказа 
                        $shippingAddress['first_name'] = $orderEcw->shippingPerson->firstName;
                        $shippingAddress['last_name'] = $orderEcw->shippingPerson->lastName;

                        if (empty($shippingAddress['first_name']) || empty($shippingAddress['last_name'])) {
                            $shippingAddress['first_name'] = $orderEcw->shippingPerson->name;
                            $shippingAddress['last_name'] = $orderEcw->shippingPerson->name;
                        }
                        $shippingAddress['country'] = $orderEcw->shippingPerson->countryName;
                        $shippingAddress['address1'] = $orderEcw->shippingPerson->street;

                        $shippingAddress['country_code'] = $orderEcw->shippingPerson->countryCode;
                        $shippingAddress['province_code'] = $orderEcw->shippingPerson->stateOrProvinceCode;
                        $shippingAddress['city'] = $orderEcw->shippingPerson->city;
                       
                        $shippingAddress['province'] = $orderEcw->shippingPerson->stateOrProvinceName;
                        $shippingAddress['zip'] = $orderEcw->shippingPerson->postalCode;
                        
                        $shippingAddress['phone'] = $orderEcw->shippingPerson->phone;

                        // dd($shippingAddress['phone']);die();
                        if ($shippingAddress['phone'][0] == '0' && $shippingAddress['country'] == 'France' && $shippingAddress['phone'][1] != '0') {
                            $shippingAddress['phone']  = '+33'.substr($shippingAddress['phone'], 1);  
                        }
                        if ($shippingAddress['phone'][1] == '0' && $shippingAddress['country'] == 'France') {
                            $shippingAddress['phone']  = '+'.substr($shippingAddress['phone'], 2);  
                        }
                        // if ($shippingAddress['phone'][0] == '0' && $shippingAddress['country'] == 'Spain') {
                        //     $shippingAddress['phone']  = '+'.substr($shippingAddress['phone'], 2);  
                        // }
                        if ($shippingAddress['phone'][0] == '3' && $shippingAddress['country'] == 'Portugal') {
                            $shippingAddress['phone']  = '+'.substr($shippingAddress['phone'], 0);  
                        }
                        if ($shippingAddress['phone'][1] == '+') {
                            $shippingAddress['phone']  = substr($shippingAddress['phone'], 1);  
                        }
                        if ($shippingAddress['phone'][0] == '0' && $shippingAddress['phone'][1] == '0') {
                            $shippingAddress['phone']  = '+'.substr($shippingAddress['phone'], 2);  
                        }
                        if ($shippingAddress['phone'][0] == '0' && $shippingAddress['country'] == 'Ireland') {
                            $shippingAddress['phone']  = '+353'.substr($shippingAddress['phone'], 1);  
                        }
                        if ($shippingAddress['phone'][0] == '6' && $shippingAddress['country'] == 'Greece') {
                            $shippingAddress['phone']  = '+30'.substr($shippingAddress['phone'], 0);  
                        }
                        if ($shippingAddress['phone'][0] == '0' && $shippingAddress['country'] == 'United Kingdom') {
                            $shippingAddress['phone']  = '+44'.substr($shippingAddress['phone'], 1);  
                        }
                        // dd($shippingAddress);die();
                 
                        switch ( $orderEcw->paymentStatus ) {
                            case 'AWAITING_PAYMENT':
                                $ord['financial_status']  = 'pending';
                                break;
                            case 'PAID':
                                $ord['financial_status']  = 'paid';
                                break;
                            case 'REFUNDED':
                                $$ord['financial_status']  = 'refunded';
                                break;
                            case 'CANCELLED':
                                $ord['financial_status']  = 'voided';
                                break;
                            case 'PARTIALLY_REFUNDED':
                                $ord['financial_status']  = 'partially_refunded';
                                break;
                            default;
                                $ord['financial_status']  = 'unpaid';
                                break;
                        }
                        $customer = [
                            
                            'phone' => $shippingAddress['phone'],
                            // 'note' => $orderEcw->paymentMethod
                        ];
                        
                    }
                    

                }
                
            } 
            // dd($customer);die();
            $amount_dis = 0;
            $shipping = 0;
            foreach ($transactions as $transaction) {
                $amount_dis += $transaction['amount'];
                
            }
            if ($amount_dis > 50) {
                foreach ($transactions as $key=>$transaction) {
                    $transactions[$key]['amount'] = $transactions[$key]['amount']-$transactions[$key]['amount']*0.3;
                    
                }
                $discount[] = [
                    "code" => "Custom discount",
                    "type" => 'percentage',
                    "amount" => "30.0",
                ];
                $shippingLines = [];
            }else {
                foreach ($transactions as $key=>$transaction) {
                    $transactions[$key]['amount'] = $transactions[$key]['amount']+$orderEcw->shippingOption->shippingRate;
                    
                }
            }
        
            //  dd($transactions);die();
            // foreach ($shippingLines as $key=>$shippingLine) {
            //     $shippingLine['price'] = 0;
            //     $shippingLines[$key]['price'] = 0;
            // }
            // dd($shippingLine);
            // dd($shippingLines);die();
            // if ($amount_dis > 50) {
            //     $amount_dis = $amount_dis-$amount_dis*0.3;
            //     foreach ($shippingLines as $key=>$shippingLine) {
            //         $shippingLine['price'] = 0;
            //         $shippingLines[$key]['price'] = 0;
            //     }
            // }
            
            // dd($amount_dis);die();
            // dd($shipping);die();
            $ord['total_price'] = $orderEcw->shippingOption->shippingRate + $variant->price*$item->quantity;
            $ord["discount_applications"] = $discount_applications;
            $ord["discount_codes"] = $discount;
            $ord['total_line_items_price'] = $orderEcw->shippingOption->shippingRate + $variant->price*$item->quantity;
            // $ord["tax_lines"] = $tax_lines;
            $ord['orderComments'] = $orderEcw->orderComments;
            $ord["line_items"] = $positions;
            $ord['email'] = $orderEcw->email;
            $ord['note'] = $orderEcw->paymentMethod;
            $ord['name'] = '#'.$orderEcw->id;
            $ord["customer"] = $customer;
            $ord["shipping_address"] = $shippingAddress;
            $ord["transactions"] = $transactions;
            $ord["shipping_lines"] = $shippingLines;

            // $ord["total_price_set"] =  $total_price_set;
            $body["order"] = $ord;
            // dd($body);die();
            $orderNumber = $orderEcw->orderNumber;
            // dd($orderNumber);die();
            // dd($transactions);
            $createOrders = myCurlPost('https://966f3aa3f94d3e0e052ecd5f661161c6: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@keepongivingjournals.myshopify.com/admin/api/2020-10/orders.json',$body);
            if ( !isset($createOrders->errors)) {
                $updateOrdersEcwid = myCurlPut("https://app.ecwid.com/api/v3/40285098/orders/{$orderNumber}?token=secret_FT8XpgDf46C8cAByFdpMpRwVJS1zijzk");
            }else{
                unlink("sync_orders/$orderEcw->id");
            }
        }else {
            echo "заказ существует";
        }

    }
}
// dd($createOrders);