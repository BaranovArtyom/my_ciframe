<?php

namespace MySklad;

// no time
//use TelegramErrorLogger;

use stdClass;

class MoySklad
{
    private $auth;
    private $token;
    private $log_errors;
    private $data;

    public function __construct($auth, $token = null, $log_errors = true)
    {
        $this->auth = $auth;
        $this->token = $token;
        $this->log_errors = $log_errors;
    }

    /**
     * Получить список товаров.
     * @param array $filter Фильтрация по имени поля и значение.
     * Например отсортировать по статусу ["name"=>"state", "value"=>"idForMyState"]
     * @param int $limit
     * @return mixed
     */
    public function getProducts(array $filter = [], $limit = 1000)
    {
        if (!$filter['name']) {
            return $this->endpoint("product?limit=$limit&offset=".$filter['offset']);
        } else {
            return $this->endpoint("product?limit=$limit&filter=" . $filter['name'] . '=' . $filter['value']);
        }
    }

    /**
     * Получить список услуг.
     * @param array $filter Фильтрация по имени поля и значение.
     * Например отсортировать по статусу ["name"=>"state", "value"=>"idForMyState"]
     * @return mixed
     */
    public function getServices(array $filter = [])
    {
        if (!$filter) {
            return $this->endpoint('service');
        } else {
            return $this->endpoint('service?filter=' . $filter['name'] . '=' . $filter['value']);
        }
    }

    /**
     * Получить список групп товаров.
     * @param array $filter Фильтрация по имени поля и значение.
     * Например отсортировать по статусу ["name"=>"state", "value"=>"idForMyState"]
     * @return mixed
     */
    public function getProductFolders(array $filter = [])
    {
        if (!$filter) {
            return $this->endpoint('productfolder');
        } else {
            return $this->endpoint('productfolder?filter=' . $filter['name'] . '=' . $filter['value']);
        }
    }

    /**
     * Contacts the various API's endpoints
     * \param $api the API endpoint
     * \param $content the request parameters as array
     * \param $post boolean tells if $content needs to be sends
     * \return the JSON Telegram's reply.
     * @param string $api
     * @param array $content
     * @param string $method
     * @return mixed
     */
    public function endpoint(string $api, array $content = [], $method = 'GET')
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/{$api}";
        $reply = null;
        switch ($method) {
            case 'POST':
                $reply = $this->sendAPIRequest($url, $content, 'POST');
                break;
            case 'GET':
                $reply = $this->sendAPIRequest($url, []);
                break;

        }
        return json_decode($reply);
    }

    private function sendAPIRequest($url, array $content = [], $method = 'GET')
    {
        $ch = curl_init();
        $auth = $this->token ? "Authorization: Bearer {$this->token}" : "Authorization: Basic {$this->auth}";
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [$auth]
        ];
        if ($method == 'POST' and $content) {
            $options[CURLOPT_POSTFIELDS] = json_encode($content);
            $options[CURLOPT_HTTPHEADER] = [
                $auth,
                "Content-Type: application/json"
            ];
        }

        curl_setopt_array($ch, $options);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HEADER, ["Authorization: Basic {$this->auth}"]);
//        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        if ($method == 'POST') {
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
//            curl_setopt($ch, CURLOPT_HEADER, [
//                "Authorization: Basic {$this->auth}",
//                "Content-Type: application/json"
//            ]);
//        }
        $result = curl_exec($ch);
        if ($result === false) {
            $result = json_encode(['ok' => false, 'curl_error_code' => curl_errno($ch), 'curl_error' => curl_error($ch)]);
        }
        curl_close($ch);
//        if ($this->log_errors) {
//            if (class_exists('TelegramErrorLogger')) {
//                $loggerArray = ($this->getData() == null) ? [$content] : [$this->getData(), $content];
//                TelegramErrorLogger::log(json_decode($result, true), $loggerArray);
//            }
//        }

        return $result;
    }

    /**
     * Get the POST request of a user in a Webhook.
     */
    public function getData()
    {
        if (empty($this->data)) {
            $rawData = file_get_contents('php://input');

            return json_decode($rawData);
        } else {
            return $this->data;
        }
    }

    public function getOrganization($name = '')
    {
        if (empty($name)) {
            return $this->endpoint('organization')->rows[0];
        } else {
            return $this->endpoint("organization?filter=name=$name")->rows[0];
        }
    }

    public function getAgent(array $counterparty, $create = false)
    {
//        if ($counterparty['phone'])
//            $agent = $this->endpoint("counterparty?filter=phone={$counterparty['phone']}");
//        if ($counterparty['name'] and empty($agent->rows))
//            $agent = $this->endpoint("counterparty?filter=name={$counterparty['name']}");
        if (!empty($counterparty['phone']))
            $agent = $this->endpoint("counterparty?search={$counterparty['phone']}");
        elseif (empty($agent->rows) and !empty($counterparty['name']))
            $agent = $this->endpoint("counterparty?search={$counterparty['name']}");
        else
            $agent = false;

        if (empty($agent->rows) and $create) {
            $body = [
                'name' => $counterparty['name'],
                'phone' => $counterparty['phone']
            ];
            $agent = $this->endpoint('counterparty', $body, 'POST');
        } elseif ((!empty($agent->rows) and $create == true) or (!empty($agent->rows) and $create == false)) {
            $agent = $agent->rows[0];
        }
        return $agent;
    }

    public function createOrder($body)
    {
        return $this->endpoint('customerorder', $body, 'POST');
    }

    function downloadFile($url, $save_to)
    {
        $auth = $this->auth ? "Basic " . $this->auth : "Bearer " . $this->token;

//    set_time_limit(0);
//This is the file where we save the    information
        $fp = fopen($save_to, 'w+');
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
// write curl response to file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $auth"]);
// get curl response
//        curl_exec($ch);

        if (curl_exec($ch) === false) {
            echo 'Ошибка curl: ' . curl_error($ch);
        }

        curl_close($ch);
        fclose($fp);
    }

    /**
     * @param $image_meta_url
     * @param $app_id
     * @return string
     */
    public function getImage($image_meta_url, $app_id)
    {
        $ms_product_images = json_decode($this->sendAPIRequest($image_meta_url));
        $download_path = '';
        if (!empty($ms_product_images->rows))
            foreach ($ms_product_images->rows as $image_data) {
                $filename = $image_data->filename;
                $download_path = "data/images/$app_id/$filename";
                if (!file_exists($download_path)) {
                    @mkdir("data/images/$app_id/", 0777, true);
                }
                if (!empty($image_data->download->downloadHref))
                    $download_url = (string)$image_data->download->downloadHref;
                else
                    $download_url = (string)$image_data->meta->downloadHref;
                $this->downloadFile($download_url, $download_path);
                $download_path = "https://i.spey.ru/saas/shopbot_prod/data/images/$app_id/$filename";
                return $download_path;
            }
        return $download_path;
    }
}
