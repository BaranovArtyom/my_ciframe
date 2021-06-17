/**замена rate в валюте мс */
    function changeCurrency($id, $isoCode, $code, $rate, $name) {

        $curl = curl_init();
        $postData = array();
        $postData['name'] = $name;
        $postData['code']= $code;
        $postData['rate']= $rate;
        $postData['isoCode']= $isoCode;
        
        $postData = json_encode($postData, 256);
        // dd($postData);exit;

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/currency/'.$id,
        CURLOPT_USERPWD=> "admin@wp_test_ciframe:43c89f9f27",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response);

        curl_close($curl);
        return $response;
}