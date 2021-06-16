<?php
namespace Ciframe{
    class Moysklad{
        private $baseLink = "https://online.moysklad.ru/api/";
        private $msLogin = "";
        private $msPass = "";
        private $msAuth = "";
        private $apiVersion = "1.2";
        private $type = "remap";

        /**
         * Moysklad constructor.
         * @param string $login
         * @param string $password
         * @param string $apiVersion
         */
        public function __construct($login,$password, $apiVersion = "1.2", $type = "remap")
        {
            $this->msLogin = $login;
            $this->msPass = $password;
            $this->apiVersion = $apiVersion;
            $this->type = $type;
            $this->msAuth = $this->msLogin.":".$this->msPass;
            $this->baseLink = $this->baseLink.$type."/".$apiVersion."/";
        }

        /**
         * @param string $entity
         * @param int $limit
         * @param int $offset
         * @param string $params
         * @param string $expand
         * @return mixed
         */
        public function get($entity, $limit = 100, $offset = 0, $params = "",  $expand = ""){
            $msLink = $this->baseLink.$entity."?limit=".$limit."&offset=".$offset;

            if($params!=""){
                $msLink.="&".$params;
            }

            if($expand!=""){
                $msLink.="&expand=".$expand;
            }

            $curl = $this->initCurl($msLink);
            curl_setopt($curl, CURLOPT_POST, 0);
            $out = curl_exec($curl);

            curl_close($curl);
            $json = json_decode($out, JSON_UNESCAPED_UNICODE);
            if (isset($json['errors'])) {
                $this->logging("GET", $msLink, $out);
            }

            unset($out);
            return $json;
        }

        /**
         * @param $msLink
         * @return mixed
         */
        public function getByLink($msLink){
            $curl = $this->initCurl($msLink);
            curl_setopt($curl, CURLOPT_POST, 0);
            $out = curl_exec($curl);

            curl_close($curl);
            $json = json_decode($out, JSON_UNESCAPED_UNICODE);

            if (isset($json['errors'])) {
                $this->logging("GET", $msLink, $out);
            }

            unset($out);
            return $json;
        }

        /**
         * @param string $entity
         * @param array $data
         * @return mixed
         */
        public function post($entity, $data = array()){
            $msLink = $this->baseLink.$entity;

            $entityData = json_encode($data);

            $curl = $this->initCurl($msLink);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $entityData);
            $out = curl_exec($curl);
            curl_close($curl);

            $json = json_decode($out, JSON_UNESCAPED_UNICODE);

            if (isset($json['errors'])) {
                $this->logging("CREATE", $msLink, $out);
            }

            unset($out);
            return $json;
        }

        /**
         * @param string $entity
         * @param array $data
         * @return mixed
         */
        public function put($entity, $data = array()){
            $msLink = $this->baseLink.$entity;

            $entityData = json_encode($data);

            $curl = $this->initCurl($msLink);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $entityData);
            $out = curl_exec($curl);
            curl_close($curl);

            $json = json_decode($out, JSON_UNESCAPED_UNICODE);

            if (isset($json['errors'])) {
                $this->logging("UPDATE", $msLink, $out);
            }

            unset($out);
            return $json;
        }

        /**
         * @param $msLink
         * @return bool|mixed|string
         */
        public function getImage($msLink){
            $curl = $this->initCurl($msLink);
            curl_setopt($curl,CURLOPT_POST,0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
            $content=curl_exec($curl);
            $info = curl_getinfo($curl);

            curl_close($curl);
            $response = $info;

            if ($response['http_code'] == 301 || $response['http_code'] == 302)
            {
                $headers = get_headers($response['redirect_url']);
                foreach( $headers as $value )
                {
                    $out= file_get_contents( $response['redirect_url'] );
                    return($out);
                }
            }
            return $content;
        }

        /**
         * @param $msLink
         * @return resource
         */
        private function initCurl($msLink){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $msLink);
            curl_setopt($curl, CURLOPT_USERPWD, $this->msAuth);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));

            return $curl;
        }

        /**
         * @param $type
         * @param $msLink
         * @param $out
         */
        private function logging($type, $msLink, $out){
            if(filesize("ms_log.log") > 67108864){ //64MB
                $hand = fopen("ms_log.log", "w+");
            }else{
                $hand = fopen("ms_log.log", "a+");
            }
            fwrite($hand, date("d-m-Y H:i:s", time()) . " - MS_$type \n $msLink \n  $out \n");
            fclose($hand);
            return;
        }

    }

    class MSParameters
    {
        private $filter_string = "";
        private $order_string = "";

        public function addFilter($param, $value, $operator = "=" )
        {
            $this->filter_string = $this->filter_string . $param . $operator . $value . ';';
        }

        public function getFilter()
        {
            return 'filter=' . urlencode(trim($this->filter_string, ';'));
        }

        public function addOrder($param, $direction = "asc" )
        {
            $this->order_string = $this->order_string . $param . "," . $direction . ';';
        }

        public function getOrder()
        {
            return 'order=' . urlencode(trim($this->order_string, ';'));
        }

        public function getParameters(){
            return 'filter=' . urlencode(trim($this->filter_string, ';')).'&order=' . urlencode(trim($this->order_string, ';'));
        }
    }

    class Zvonobot{
        private $baseLink = "https://lk.zvonobot.ru/apiCalls/";
        private $apiKey = "";

        /**
         * Zvonobot constructor.
         * @param $apiKey
         */
        public function __construct($apiKey)
        {
            $this->apiKey = $apiKey;
        }

        public function create($data){
            $data["apiKey"] = $this->apiKey;
            $curl = $this->initCurl($this->baseLink."create",$data);
            $out = curl_exec($curl);
            curl_close($curl);
            return json_decode($out,JSON_UNESCAPED_UNICODE);
        }

        public function get($data){
            $data["apiKey"] = $this->apiKey;
            $curl = $this->initCurl($this->baseLink."get",$data);
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $out = curl_exec($curl);
            curl_close($curl);
            return json_decode($out,JSON_UNESCAPED_UNICODE);
        }

        /**
         * @param $link
         * @param $data
         * @return resource
         */
        private function initCurl($link,$data){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            return $curl;
        }
    }
}