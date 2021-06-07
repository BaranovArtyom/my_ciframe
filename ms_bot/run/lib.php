<?php
//ini_set('display_errors', '1');
require_once 'jwt.lib.php';
require_once 'vendor/autoload.php';
require_once 'env.php';

use \Firebase\JWT\JWT;
use Medoo\Medoo;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use DbCon\TgDatabase;


//
//  Config
//

class AppConfig
{

    var $appId = 'APP-ID';
    var $appUid = 'APP-UID';
    var $secretKey = 'SECRET-KEY';

    var $appBaseUrl = 'APP-BASE-URL';

    var $moyskladVendorApiEndpointUrl = 'https://marketplace.sandbox.moysklad.ru/api/vendor/1.0';
    var $moyskladJsonApiEndpointUrl = 'https://marketplace.sandbox.moysklad.ru/api/remap/1.2';

    public function __construct(array $cfg)
    {
        foreach ($cfg as $k => $v) {
            $this->$k = $v;
        }
    }
}

$cfg = new AppConfig(require('config.php'));

function cfg(): AppConfig
{
    return $GLOBALS['cfg'];
}

//
//  Vendor API 1.0
//

class VendorApi
{

    function context(string $contextKey)
    {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $appId, string $accountId, string $status)
    {
        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            "{\"status\": \"$status\"}");
    }

    private function request(string $method, $path, $body = null)
    {
        return makeHttpRequest(
            $method,
            cfg()->moyskladVendorApiEndpointUrl . $path,
            buildJWT(),
            $body);
    }

}

function makeHttpRequest(string $method, string $url, string $bearerToken, $body = null)
{
    loginfo("APP => MOYSKLAD", "Send: $method $url\n$body");

    $opts = $body
        ? ['http' =>
            [
                'method' => $method,
                'header' => ['Authorization: Bearer ' . $bearerToken, "Content-type: application/json"],
                'content' => $body
            ]
        ]
        : ['http' =>
            [
                'method' => $method,
                'header' => 'Authorization: Bearer ' . $bearerToken
            ]
        ];
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    return json_decode($result);
}

$vendorApi = new VendorApi();

function vendorApi(): VendorApi
{
    return $GLOBALS['vendorApi'];
}

function buildJWT()
{
    $token = array(
        "sub" => cfg()->appUid,
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token, cfg()->secretKey);
}


//
//  JSON API 1.2
//

class JsonApi
{

    private $accessToken;

    function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    function stores()
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . '/entity/store',
            $this->accessToken);
    }

    function products(array $filter = null)
    {
        if (!$filter) {
            return makeHttpRequest(
                'GET',
                cfg()->moyskladJsonApiEndpointUrl . '/entity/product',
                $this->accessToken);
        } else {
            return makeHttpRequest(
                'GET',
                cfg()->moyskladJsonApiEndpointUrl . '/entity/product?filter=' . $filter['name'] . '=' . $filter['value'],
                $this->accessToken);
        }
    }

    function prices()
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . '/context/companysettings/pricetype',
            $this->accessToken);
    }

    function currency()
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . '/entity/currency/',
            $this->accessToken);
    }

    function organizations()
    {
        return makeHttpRequest(
            'GET',
            cfg()->moyskladJsonApiEndpointUrl . '/entity/organization/',
            $this->accessToken);
    }
}

function jsonApi(): JsonApi
{
    if (!array_key_exists('jsonApi', $GLOBALS)) {
        $GLOBALS['jsonApi'] = new JsonApi(AppInstance::get()->accessToken);
    }
    return $GLOBALS['jsonApi'];
}

//
//  Logging
//

function loginfo($name, $msg)
{
    @mkdir('logs');
    @mkdir('logs/' . date('Y-m-d'));
    file_put_contents('run/'.'logs/' . date('Y-m-d') . '/log.txt', date(DATE_W3C) . ' [' . $name . '] ' . $msg . "\n", FILE_APPEND);
}

//
//  AppInstance state
//

$currentAppInstance = null;

class AppInstance
{

    const UNKNOWN = 0;
    const SETTINGS_REQUIRED = 1;
    const ACTIVATED = 100;

    var $appId;
    var $accountId;
    var $infoMessage;
    var $store;

    private $db;

    var $accessToken;

    var $status = AppInstance::UNKNOWN;

    static function get(): AppInstance
    {
        $app = $GLOBALS['currentAppInstance'];
        if (!$app) {
            throw new InvalidArgumentException("There is no current app instance context");
        }
        return $app;
    }

    public function __construct($appId, $accountId, $db)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
        $this->db = $db;
    }

    function getStatusName()
    {
        switch ($this->status) {
            case self::SETTINGS_REQUIRED:
                return 'SettingsRequired';
            case self::ACTIVATED:
                return 'Activated';
        }
        return null;
    }

    function persist(array $data)
    {
        $col = [
            'accname' => $data['accountName'],
            'accid' => $data['accid'],
            'access_token' => $data['access_token'],
            'status' => "Activated",
        ];

        $app_info_id = $this->db->get('app_info', 'id', ['accid' => $data['accid']]);
        if (empty($app_info_id)) {
            $this->db->insert('app_info', $col);
            $app_info_id = $this->db->id();
            if (empty($app_info_id))
                $app_info_id = $this->db->get('app_info', 'id', ['accid' => $data['accid']]);
            if (!$this->db->has('app_settings', ['app_info_id' => $app_info_id]))
                $this->db->insert('app_settings', ['app_info_id' => $app_info_id]);
        } else {
            $this->db->update('app_info', ['access_token' => $data['access_token']], ['id' => $app_info_id]);
            if (!$this->db->has('app_settings', ['app_info_id' => $app_info_id]))
                $this->db->insert('app_settings', ['app_info_id' => $app_info_id]);
        }
        if ($this->db->error()[2])
            loginfo('dbError', json_encode($this->db->error()[2]));
    }

    function delete(array $acc_id)
    {
        $this->db->update('app_info', [
            'status' => 'Deleted'
        ], ['accid' => $acc_id]);

        // need for statistic, remove //
        $accountId = key_exists('accid', $acc_id) ? $acc_id['accid'] : $acc_id;
        $data = $this->db->select("app_info", [
            "[>]app_settings" => ["app_info.id" => "app_info_id"]
        ], "*",
            [
                "app_info.accid" => $accountId
            ]);
        $log_dir = __DIR__ . "/data/deleted/$accountId.json";
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
            file_put_contents($log_dir, json_encode($data));
        }
//        file_put_contents($log_dir . "$accountId.json", json_encode($data));
        loginfo('Delete', json_encode($accountId) . ' ' . $log_dir);

        // delete account info and all related rows
//        $this->db->delete('app_info', ['accid' => $accountId]);
        if ($this->db->error()[2])
            loginfo('dbError', json_encode($this->db->error()));
    }

    static function loadApp($accountId, $db): AppInstance
    {
        return self::load(cfg()->appId, $accountId, $db);
    }

    static function load($appId, $accountId, $db): AppInstance
    {
        $where = [
        ];
        if ($accountId) $where['accid'] = $accountId;

        $exist_app = $db->select('app_info', '*', $where);
        if ($exist_app) {
            if ($exist_app[0]['status'] == 'Deleted')
                $db->update('app_info', ['status' => 'Activated'], [
                    'accid' => $accountId
                ]);
            $app = new AppInstance($appId, $accountId, $db);
            $app->accessToken = $exist_app[0]['access_token'];
        } else {
            $app = new AppInstance($appId, $accountId, $db);
        }

        $GLOBALS['currentAppInstance'] = $app;
        return $app;
    }
}

function connectDb()
{
    $dbCred = [
        'database_type' => 'mariadb',
        'database_name' => $GLOBALS['dbAuth']['database'],
        'server' => $GLOBALS['dbAuth']['host'],
        'username' => $GLOBALS['dbAuth']['user'],
        'password' => $GLOBALS['dbAuth']['password'],
        'logging' => false
    ];
    return new Medoo($dbCred);
}

//class AppRepo implements \SplSubject
//{
//    private array $apps = [];
//
//    public function attach(SplObserver $observer)
//    {
//        // TODO: Implement attach() method.
//    }
//
//    public function detach(SplObserver $observer)
//    {
//        // TODO: Implement detach() method.
//    }
//
//    public function notify()
//    {
//        // TODO: Implement notify() method.
//    }
//}
//
//// on delete send notify for archive
//class App implements \SplObserver{
//
//    public function update(SplSubject $subject)
//    {
//        // TODO: Implement update() method.
//    }
//}


