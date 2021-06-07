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
     * @param string $filter Фильтрация по имени поля и значение.
     * Например отсортировать по статусу ["name"=>"state", "value"=>"idForMyState"]
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function getProducts(string $filter = '', $limit = 1000, $offset = 0)
    {
        if (!$filter) {
            return $this->endpoint("product?limit=$limit&offset=$offset");
        } else {
            return $this->endpoint("product?limit=$limit&offset=$offset&filter=" . $filter);
        }
    }

    public function getCurrency($id = '', $name = '')
    {
        if (!empty($id)) {
            return $this->endpoint("currency/$id");
        } elseif (!empty($name)) {
            return $this->endpoint("currency?filter=name=$name");
        } else {
            return $this->endpoint('currency');
        }
    }

    /**
     * @param string $filter
     * example
     * $filter = 'state=idForMyState'
     * $filter = ["name"=>"state", "value"=>"idForMyState"] outdated
     * @param int $limit
     * @param array $filters
     * @return mixed
     */
    public function getAssortment(string $filter = '', $limit = 1000, $filters = [])
    {
        $url = "assortment?limit=$limit";
        if (!empty($filters)) {

        }
        $url = "assortment?limit=$limit";
        if (!empty($filter))
            $url .= "&filter={$filter}";

        return $this->endpoint($url);
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
     * @param string $id
     * @return mixed
     */
    public function getProductFolders(array $filter = [], $id = '')
    {
        if (!$filter) {
            if ($id)
                return $this->endpoint("productfolder/$id");
            else
                return $this->endpoint("productfolder");
        } else {
            return $this->endpoint('productfolder?filter=' . $filter['name'] . '=' . $filter['value']);
        }
    }

    /**
     * @param string $api
     * @param array $content
     * @param string $method
     * @param array $points
     * @return mixed
     */
    private function endpoint(string $api, array $content = [], $method = 'GET', $points = [])
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/{$api}";
        $reply = [];
        switch ($method) {
            case 'POST':
                $reply = $this->sendAPIRequest($url, $content, 'POST');
                break;
            case 'GET':
                $reply = $this->sendAPIRequest($url, []);
                break;
            case 'MULTI':
                if ($points) {
                    $urls = [];
                    foreach ($points as $point) {
                        $urls[] = "https://online.moysklad.ru/api/remap/1.2/entity/{$point}";
                    }
                    $reply = $this->sendMultiRequest($urls);
                }
                break;

        }
        return json_decode($reply);
    }

    public function sendMultiRequest($urls = [])
    {
        $curls = [];
        foreach ($urls as $url) {
            $ch = curl_init();
            $auth = $this->token ? "Authorization: Bearer {$this->token}" : "Authorization: Basic {$this->auth}";
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [$auth]
            ];
            curl_setopt_array($ch, $options);
            $curls[] = $ch;
        }

        $mh = curl_multi_init();
        foreach ($curls as $curl) {
            curl_multi_add_handle($mh, $curl);
        }


        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        $responses = [];
        foreach ($curls as $curl) {
            $responses[] = curl_multi_getcontent($curl);
        }
        curl_multi_close($mh);
        return $responses;
    }

    public function sendAPIRequest($url, array $content = [], $method = 'GET')
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
//        elseif (empty($agent->rows) and !empty($counterparty['name']))
//            $agent = $this->endpoint("counterparty?search={$counterparty['name']}");
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

                    if (!empty($image_data->download->downloadHref))
                        $download_url = (string)$image_data->download->downloadHref;
                    else
                        $download_url = (string)$image_data->meta->downloadHref;
                    $this->downloadFile($download_url, $download_path);
                }
                $download_path = "https://i.spey.ru/saas/shopbot_prod/data/images/$app_id/$filename";
                return $download_path;
            }
        return $download_path;
    }

    function get_id_from_href($href)
    {
        $t = explode('/', $href);
        $id = explode('?', $t[count($t) - 1])[0];
        return $id;
    }

    function getMetadata($entity, $metaname = '')
    {
        $url = "$entity/metadata/$metaname";
        return $this->endpoint($url);
    }
}
