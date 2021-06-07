<?php

namespace DbCon;

use Exception;
use Medoo\Medoo;
use Monolog\Logger;
use PDOStatement;
use stdClass;

//require_once 'vendor/autoload.php';
//require_once 'env.php';

class TgDatabase
{
    private $connection;
    private $logger;

    public function __construct(array $db_credentials, Logger $logger)
    {
        $this->logger = $logger;
        $cred = [
            'database_type' => 'mysql',
            'database_name' => $db_credentials['database'],
            'server' => $db_credentials['host'],
//                'port' => $db_credentials['port'],
            'username' => $db_credentials['user'],
            'password' => $db_credentials['password'],
            'logging' => false
        ];
        if (!empty($db_credentials['port'])) $cred['port'] = $db_credentials['port'];
        $this->connection = new Medoo($cred);
    }

    public function createContext($app_id, $context)
    {
        $this->connection->update('app_info', ['cntx' => $context], ['id' => $app_id]);
    }

    public function getContext($context, $account_id)
    {
        return $this->connection->get('app_info', '*', ['cntx' => $context, 'accid' => $account_id]);
    }


    public function getLog()
    {
        return $this->connection->log();
    }

    public function getError()
    {
        return $this->connection->error();
    }

    public function getAppInfo($account_id)
    {
        return $this->connection->get('app_info', '*', ['accid' => $account_id]);
    }

    public function getLastInsertionId()
    {
//        return $this->last_insertion_id;
        return $this->connection->id();
    }

    public function createUser($chat_id, $app_info_id = null)
    {
        $this->logger->debug('Run function ' . __FUNCTION__, [
            'ch_id' => $chat_id,
            'app_info_id' => $app_info_id
        ]);
        if (!$this->connection->has('users', ['chat_id' => $chat_id, 'app_info_id' => $app_info_id])) {
            $columns = [
                'id' => uniqid('', true),
                'chat_id' => $chat_id,
                'state' => 'MENU'
            ];
            if ($app_info_id)
                $columns['app_info_id'] = $app_info_id;
            $this->insertOrUpdate('users', $columns, ['chat_id' => $chat_id, 'app_info_id' => $app_info_id]); // не обновится, ибо уже связан с другой таблицей...
//        $this->connection->insert('users', $columns);
//        $this->connection->query("ON DUPLICATE KEY UPDATE state='MENU'");

            if ($this->connection->error())
                $this->logger->error('Create user failed', [
                    'dbError' => $this->connection->error(),
                    'dbLog' => $this->connection->log()
                ]);
        }
    }

    public function updateUser(array $fields, $chat_id, $app_info_id)
    {
        $this->connection->update("users", $fields, [
            "app_info_id" => $app_info_id,
            "chat_id" => $chat_id
        ]);
        if ($this->connection->error()[2])
            $this->logger->error('Update user failed', [
                'dbError' => $this->connection->error(),
                'log' => $this->connection->log()
            ]);
    }

    function getUser($chat_id, $app_info_id)
    {
        return $this->connection->get("users", "*", ['chat_id' => $chat_id, 'app_info_id' => $app_info_id]);
    }

    public function getStateUser($chat_id, $app_info_id)
    {
        $state = $this->connection->get("users", "state", [
            'app_info_id' => $app_info_id,
            'chat_id' => $chat_id
        ]);
        if ($this->connection->error()[2])
            $this->logger->error('Get state from user failed', [
                'dbError' => $this->connection->error(),
                'log' => $this->connection->log()
            ]);

        return $state;
    }

    public function insertOrUpdate($table, $data, $where)
    {
        $this->logger->debug("Run " . __FUNCTION__, ['table' => $table, 'data' => $data, 'where' => $where]);
        $exist = $this->connection->get($table, 'id', $where);
        if (!empty($exist)) {
            $this->connection->update($table, $data, $where);
//            return $this->connection->update($table, $data, $where);
//            $this->logger->debug('', ['Db_log' => $this->connection->log()]);
            return $exist;
        } else {
            $this->connection->insert($table, $data);
//            return $this->connection->insert($table, $data);
//            $this->logger->debug('', ['Db_log' => $this->connection->log()]);
            return $this->connection->id();
        }
    }

    public function updateAppSettings(array $data, array $where)
    {
        $settings = $this->connection->get("app_settings", "id", $where);
//        var_dump($where);
        if (!empty($settings)) {
            $this->connection->update("app_settings", $data, $where);
        } else {
            $this->connection->insert('app_settings', $data);
        }
    }


    function createParentsCategory($cats, $parent_id, $app_info_id)
    {
        foreach ($cats as $cat) {
            if ($cat->id === $parent_id) {
                if (empty($cat->productFolder)) {
                    $this->logger->debug("Create category $cat->name", ['app_id' => $app_info_id, 'ms_id' => $cat->id]);
                    return $this->createCategory($cat, 0, $app_info_id);
                } else {
                    try {
                        $cat_id = $this->createParentsCategory(
                            $cats,
                            $this->get_id_from_href($cat->productFolder->meta->href),
                            $app_info_id
                        );
                    } catch (Exception $e) {
                        $this->logger->error('Не удалось создать родительскую категорию', ['trace' => $e->getTrace()]);
                    }
                    $this->logger->debug("Create category $cat->name", ['app_id' => $app_info_id, 'ms_id' => $cat->id]);
                    return $this->createCategory($cat, $cat_id, $app_info_id);
                }
            }
        }
        throw new Exception('Не удалось создать родительскую категорию');
    }

    public function get_id_from_href($href): string
    {
        $t = explode('/', $href);
        $id = explode('?', $t[count($t) - 1])[0];
        return $id;
    }

    public function createCategory(stdClass $category, $parent_id, $app_info_id, $custom = 0)
    {
        $exist_c = $this->connection->get('categories', '*', ['OR' => ['name' => $category->name, 'ms_id' => $category->id], 'app_info' => $app_info_id]);
        if (empty($exist_c)) {
            $cat_name = iconv(mb_detect_encoding($category->name, mb_detect_order(), true), "UTF-8", $category->name);
            $this->connection->insert('categories', [
                'name' => $cat_name,
                'parent_id' => $parent_id,
                'app_info' => $app_info_id,
                'ms_id' => $category->id,
                'ms_path' => $category->pathName,
                'custom' => $custom
            ]);
            return $this->connection->id();
        } else {
            if ($exist_c['name '] !== $category->name or $exist_c['parent_id'] !== $parent_id) {
                $this->connection->update('categories', [
                    'parent_id' => $parent_id,
                    'ms_id' => $category->id,
                    'ms_path' => $category->pathName,
                    'name' => $category->name,
//                'display' => $exist_c['display'],
                ], ['id' => $exist_c['id']]);
            }
            return $exist_c['id'];
        }
    }

    public function createCategoryOld(string $name, string $parent_name = '', $app_info = null, $ms_id = null)
    {
        $paren_id = 0;
//        echo "$name \n";
        if (!empty($name)) {
            if ($parent_name) {
                if (stripos($parent_name, '/')) {
                    $parent_name = explode('/', $parent_name);
                    $parent_name = end($parent_name);
                }
                $paren_id = $this->connection->get('categories', 'id', ['OR' => ['name' => $parent_name, 'ms_id' => $ms_id]]);
                if (empty($paren_id))
                    $paren_id = $this->createCategoryOld($parent_name, '', $app_info, $ms_id);
            }
            $this->connection->insert('categories', [
                    'name' => $name,
                    'parent_id' => $paren_id,
                    'app_info' => $app_info,
                    'ms_id' => $ms_id]
            );

//        $this->insertOrUpdate('categories', ['name' => $name], ['name' => $name]);
            if ($this->connection->error()[2]) {
                $this->logger->error("Create category $name ends with errors", ['err' => $this->connection->error()]);
            }
        }
        $cat_id = $this->connection->id();
        if (empty($cat_id))
            $cat_id = $this->connection->get('categories', 'id', ['OR' => ['name' => $name, 'ms_id' => $ms_id]]);
        return $cat_id;
    }

    public
    function updateCategory(array $data, $where)
    {
        $this->connection->update('categories', $data, $where);
        if ($this->connection->error()[2]) {
            $this->logger->error("Update category ends with errors", ['err' => $this->connection->error()]);
            return false;
        }
        return true;
    }

    public function unpublishRelatedGroup($category_id)
    {
        if ($category_id != 0) {
            $this->connection->delete('custom_menu', ['category_id' => $category_id]);
            $this->connection->update('categories', ['display' => false], ['id' => $category_id]);
            $this->connection->update('products', ['to_telegram' => false], ['category_id' => $category_id]);
            $childs = $this->connection->select('categories', 'id', ['parent_id' => $category_id]);
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $this->unpublishRelatedGroup($child);
                }
            }
        }
    }

    public
    function setAllCategoriesNotDisplay($app_info_id): bool
    {
        $this->connection->update('categories', ['display' => 0], ['app_info' => $app_info_id]);
        if ($this->connection->error()[2]) {
            $this->logger->error("setAllCategoriesNotDisplay ends with errors", ['err' => $this->connection->error()]);
            return false;
        }
        return true;
    }

    public function setAllProductsNotDisplay($app_info_id = null)
    {
        $where = [];
        if ($app_info_id) $where = ['app_info_id' => $app_info_id];
        $this->connection->update("products", ["to_telegram" => false], $where);
        if ($this->connection->error()[2])
            $this->logger->error("setAllCategoriesNotDisplay ends with errors", ['err' => $this->connection->error()]);
    }

    public
    function createPrice(stdClass $price, $product_id, $currency)
    {
        $_price = $this->connection->get('prices', '*', ['product_id' => $product_id]);

        if (!empty($_price)) {
            if (
                $_price['value'] != $price->value or
                $_price['price_type'] != (string)$price->priceType->name or
                $_price['currency'] != strtolower($currency->isoCode) or
                $_price['currency_id'] != (string)$currency->id
            ) {
                $this->connection->update('prices', [
                    'value' => $price->value / 100,
                    'price_type' => (string)$price->priceType->name,
                    'currency_id' => $currency->id,
                    'currency' => strtolower($currency->isoCode)
                ], ['id' => $_price['id']]);

                if ($this->connection->error()[2])
                    $this->logger->debug('Update price failed', ['err' => $this->connection->error()]);
            }
        } else {
            $this->connection->insert('prices', [
                'value' => $price->value / 100,
                'price_type' => (string)$price->priceType->name,
                'currency' => strtolower($currency->isoCode),
                'currency_id' => (string)$currency->id,
                'product_id' => $product_id
            ]);
            if ($this->connection->error()[2])
                $this->logger->debug('Create price failed', ['err' => $this->connection->error()]);
            return $this->getLastInsertionId();
        }
        return $_price;
    }

    public
    function getPrice($product_id)
    {
        return $this->connection->get('prices', '*', ['product_id' => $product_id]);
    }

    function createProduct(stdClass $product, string $default_image, $app_info_id = '', $category_id = '', $q = 'all')
    {
        $where = [
            "app_info_id" => $app_info_id,
            "ms_id" => $product->id
        ];

        if (empty($product->description)) $product->description = '';

        $db_product = $this->connection->get("products", "*", $where);

        if (empty($category_id)) {
            $crop_parent_name = explode('/', (string)$product->pathName);
            $parent_name = end($crop_parent_name);
//            $category_id = $this->connection->get('categories', 'id', ['name' => $parent_name]);
            $parent_name = iconv(mb_detect_encoding($parent_name, mb_detect_order(), true), "UTF-8", $parent_name);
            $category_id = $this->connection->get('categories', 'id', ['name' => $parent_name, 'app_info' => $app_info_id]);
        }
        if (empty($db_product)) {
            $data = [
                'ms_id' => $product->id,
                'article' => empty($product->article) ? null : $product->article,
                'ms_code' => @$product->code,
                'barcode' => empty($product->barcodes) ? null : $this->getBarcode($product->barcodes),
                'name' => iconv(mb_detect_encoding($product->name, mb_detect_order(), true), "UTF-8", $product->name),
                'description' => $product->description,
                'image' => $default_image,
                'app_info_id' => $app_info_id
            ];
//            if (!empty($product->to_telegram))
//            $data['to_telegram'] = $product->to_telegram;
            if ($q == 'all')
                $data['to_telegram'] = true;
            elseif ($q == 'positiveOnly')
                if (@$product->quantity > 0)
                    $data['to_telegram'] = true;

            $data['category_id'] = $category_id;
            $data['quantity'] = empty($product->quantity) ? 0 : (int)$product->quantity;
            $data['type'] = empty($product->meta->type) ? 'product' : $product->meta->type;
            $data['parent_ms_id'] = empty($product->product->meta->href) ?: $this->get_id_from_href($product->product->meta->href);

//        $this->insertOrUpdate();
            $this->connection->insert('products', $data);
            if ($this->connection->error()[2])
                $this->logger->error('Create product ends with errors', [
                    'err' => $this->connection->error(),
                    'product' => $product
                ]);
            $product_id = $this->connection->id();
        } else {
            $product_id = $db_product['id'];
            if (empty($category_id)) {
                throw new Exception("Create product failed. Cat id is empty, $product->id, $app_info_id");
                $crop_parent_name = explode('/', (string)$product->pathName);
                $parent_name = end($crop_parent_name);
                $parent_name = iconv(mb_detect_encoding($parent_name, mb_detect_order(), true), "UTF-8", $parent_name);

                $c_names = count($crop_parent_name);
                if ($c_names > 1) {
                    $cat_name_parent = $crop_parent_name[$c_names - 2];
                } else {
                    $cat_name_parent = '';
                }
                $category_id = $this->createCategory($parent_name, $cat_name_parent, $app_info_id);
            }

//                $this->connection->query("ON DUPLICATE KEY UPDATE category_id='$category_id', image='$default_image', description='$product->description'");
            if (strtotime($db_product['updated_at']) < strtotime(@$product->updated)) {
                $cols = ['ms_code' => @$product->code, 'article' => @$product->article, 'name' => $product->name,
                    'category_id' => $category_id, 'image' => $default_image, 'description' => $product->description,
                    'quantity' => empty($product->quantity) ? 0 : (int)$product->quantity,
                    'type' => empty($product->meta->type) ? 'product' : $product->meta->type,
                ];

                $cols['parent_ms_id'] = empty($product->product->meta->href) ?: $this->get_id_from_href($product->product->meta->href);

                if ($q == 'all')
                    $cols['to_telegram'] = true;
                elseif ($q == 'positiveOnly')
                    if (@$product->quantity > 0)
                        $cols['to_telegram'] = true;

                $this->connection->update('products', $cols, ['id' => $db_product['id']]);

                if ($this->connection->error()[2])
                    $this->logger->error('Create product ends with errors', [
                        'err' => $this->connection->error(),
                        'product' => $product
                    ]);
            } else {
                if ($db_product['to_telegram'] == '0') {
                    $this->connection->update('products', ['to_telegram' => 1], ['id' => $db_product['id']]);
                }
            }
        }

//        $product_id = $this->connection->id();
//        $this->createPrice($product->salePrices, $product_id);

        if (!empty($product_id))
            return $product_id;
        return [];
    }

    /**
     * @param string $product_id
     * @param array $column
     * @param array $where
     */
    public
    function updateProduct(string $product_id, array $column, $where = [])
    {
        if ($where)
            $this->connection->update('products', $column, $where);
        else
            $this->connection->update('products', $column, ['id' => $product_id]);
//        $this->logger->debug('Run ' . __FUNCTION__, ['db log' => $this->connection->log()]);
//        return $this->connection->update('products', $column, ['id' => $product_id]);
    }

    /**
     *
     * need rework. This params put in array $where, add param $fields - which field needed
     * @param string $name
     * @param string $article
     * @param null $id
     * @param string $app_info_id
     * @param string $ms_id
     * @return bool|mixed
     */
    //todo change function param
    function getProductFromDb($name = '', $article = '', $id = null, $app_info_id = '', $ms_id = '')
    {
        $where = [];
        if ($app_info_id)
            $where = ['app_info_id' => $app_info_id];

        if ($id)
            $where['id'] = $id;

        if ($name) {
            $name = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
            $where['name'] = $name;
        }
        if ($article) {
//            $where['article'] = $article;
//            ['ms_code' => $article];
            $where["OR"] = ["ms_code" => $article, "article" => $article];
        }

        if ($ms_id) {
            $where['ms_id'] = $ms_id;
        }

        $prd = $this->connection->get('products', '*', $where);
//        $log = $this->connection->log();
//        $log = end($log);
//        $this->logger->debug('getProductFromDb', ['log' => $log]);
        return $prd;
    }

    function searchProducts($name, $app_info_id, $to_telegram = true)
    {
        if (!empty($name))
            $name = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
        return $this->connection->select("products", "*", ["name[~]" => $name, 'to_telegram' => $to_telegram, 'app_info_id' => $app_info_id]);
    }

    function deleteProductInCart($product_id, $cart_id, $id = null)
    {
        if ($id)
            $this->connection->delete("products_in_cart", ["id" => $id]);
        else
            $this->connection->delete("products_in_cart", ["products" => $product_id, 'cart' => $cart_id]);
        $log = $this->connection->log();
        $log = end($log);
        $this->logger->debug('deleteProductInCart', ['log' => $log]);
    }


    /**
     * @param string $category_id
     * @param string $app_info_id
     * @return bool|int|mixed|string
     */
    function countProducts($category_id = '', $app_info_id = '')
    {
        $where = ["to_telegram" => true];
//        $where = [];
        if ($category_id)
            $where['category_id'] = $category_id;
        if ($app_info_id)
            $where['app_info_id'] = $app_info_id;
        return $this->connection->count("products", $where);
    }

    public function getCategoryChild($app_id, $category_id, $fields = [])
    {
        return $this->connection->select('categories', $fields ?? '*', ['app_info' => $app_id, 'parent_id' => $category_id]);
    }

    public
    function getAllCategories($app_info_id, $display = null, $custom = false)
    {
        $where = [
            "app_info" => $app_info_id,
            "ORDER" => ["name" => "ASC"]
        ];
        if ($display)
            $where['display'] = $display;
        if (!$custom)
            $where['custom'] = $custom;
        return $this->connection->select('categories', '*', $where);
    }

    public
    function getCategory($ms_id, $app_info_id, $display = 1, $name = '')
    {
        $where = [
            "app_info" => $app_info_id,
            "OR" => [
                "ms_id" => $ms_id,
                "name" => $name,
            ]
//            "parent_id" => 0,
//            "ORDER" => ["name" => "ASC"]
        ];
        if (!empty($display) or $display === 0) {
            $where['display'] = $display;
        }
//        $resp = $this->connection->get('categories', '*', ['name' => (string)$category_name]);
        return $this->connection->get("categories", '*', $where);
    }

    public
    function getAllProducts($app_info_id, int $limit = 1, int $offset = 0, bool $all = false, $to_telegram = false)
    {
//        $this->connection->update('products', ['app_info_id' => 1]);
        $where = [
            "to_telegram" => 1,
            "app_info_id" => $app_info_id,
            "ORDER" => ["name" => "DESC"]
        ];
        if ($all === false) $where['LIMIT'] = [$offset, $limit];
        if ($to_telegram) $where['to_telegram'] = true;

        $pr = $this->connection->select('products', '*', $where);
//        $this->logger->debug('Run' . __FUNCTION__, ['getAllProducts db log' => $this->connection->log()]);
        if ($this->connection->error()[2])
            $this->logger->error('Cant get products from db', ['db_error' => $this->connection->error()]);
//        return $this->connection->select('products', '*', $where);
        return $pr;
    }

    public
    function getProductsFromCategory($category_id, int $limit = 0, int $offset = 0)
    {
        $where = [
            "to_telegram" => 1,
            "category_id" => $category_id,
            "ORDER" => ["name" => "DESC"]];
        if ($limit > 0)
            $where['LIMIT'] = [$offset, $limit];
        $products = $this->connection->select('products', '*', $where);
        $this->logger->debug('getProductsFromCategory', ['log' => $this->connection->log()]);
        return $products;
    }

    public
    function getCart($app_info_id, $chat_id = null, $user_id = null, $completed = 0)
    {
        // todo need with join
        if (empty($user_id))
            $user_id = $this->connection->get("users", "id", ['chat_id' => $chat_id, "app_info_id" => $app_info_id, "ORDER" => ["updated_at" => "DESC"]]);
        //        $cart = $this->connection->get('cart', ['[>]users' => ["user" => "id"]], '*', ['users.chat_id' => $chat_id]);
//        $this->logger->debug('Query db', ['q' => $this->connection->log()]);
//        $this->logger->error('Err db', ['err' => $this->connection->error()[2]]);
//        $user_id = $this->connection->get('users', 'id', ['chat_id' => $chat_id]);
//        $cart = $this->connection->get('cart', '*', ['user' => $user_id, 'completed' => false]);
        return $this->connection->get("cart", "*", ['user' => $user_id]);
    }

    function updateCart($cart_id, array $data)
    {
        $this->connection->update("cart", $data, ['id' => $cart_id]);
    }

    public
    function getProductsInCart(string $cart_id = '', string $acc_id = '')
    {
        if (!empty($cart_id))
            return $this->connection->select('products_in_cart', '*', [
                "cart" => $cart_id
            ]);
        if (!empty($acc_id))
            // query is SELECT * FROM "products_in_cart" LEFT JOIN "cart" ON "products_in_cart"."cart" = "cart"."id" LEFT JOIN "users" ON "cart"."user" = "users"."id" LEFT JOIN "app_info" ON "users"."app_info_id" = "app_info"."id" WHERE "app_info"."accid" = :MeDoO_0_mEdOo
            return $this->connection->select("products_in_cart", [
                "[>]cart" => ["cart" => "id"],
                "[>]users" => ["cart.user" => "id"],
                "[>]app_info" => ["users.app_info_id" => "id"]
            ], '*', [
                "app_info.accid" => $acc_id
            ]);
        return [];
    }

    public
    function checkDbError($msg, $context)
    {
        if ($this->connection->error()[2])
            $this->logger->error($msg, $context);
    }

    public
    function createOrder($cart, $user_id, $items, $app_id)
    {
        $id = uniqid('', true);
        $columns = [
            'id' => $id,
            'user' => $user_id,
            'cart_info' => json_encode($cart),
            'items' => json_encode($items),
            'ms_name' => $this->connection->count('orders', ['app_info_id' => $app_id]) + 1,
            'app_info_id' => $app_id
        ];
//        $order = $this->connection->insert('orders', $columns)->fetchAll();
//        if (!$order)
//            return $this->connection->id();
//        return $order['id'];
        $this->connection->insert('orders', $columns);
        if (!empty($this->connection->error()[2])) {
            $this->logger->error('Create order ends with error: ' . $this->connection->error()[2], ['err' => $this->connection->error()]);
        }
        $this->logger->debug('Create order ' . $id, ['columns' => $columns]);
        return $id;
    }

    public
    function updateOrder(string $id, array $columns)
    {
        $this->connection->update('orders', $columns, ['id' => $id]);
    }

    public
    function getAppSettings(string $account_id)
    {
//        $set = $this->connection->query("SELECT * FROM app_settings LEFT JOIN app_info ON app_settings.app_info_id = app_info.id WHERE app_info.accid = '$account_id' LIMIT 1")->fetchAll()[0];
        //        print_r( $this->connection->log());
        $set = $this->connection->get("app_settings", ["[>]app_info" => ["app_info_id" => "id"]], '*', [
            "app_info.accid" => $account_id
        ]);
//        $app = $this->connection->get("app_info", "id", ["accid" => $account_id]);
//
//        $l = $this->connection->log();
//        $set = $this->connection->get("app_settings", "*", ["app_info_id" => $app]);
        return $set;
    }

    /**
     * @return Medoo
     */
    public
    function getConnection(): Medoo
    {
        return $this->connection;
    }

//
//    public function updateAppSettings($columns, $where)
//    {
//        $this->insertOrUpdate('app_settings', $columns, $where);
//    }

    /**
     * @param string $app_id
     * @return array|false
     *
     * db query something like SELECT * FROM orders LEFT JOIN users on (users.id = orders.user) LEFT JOIN app_info ON (users.app_info_id = app_info.id) WHERE app_info.accid =
     */
//    public function getOrders(string $account_id)
    public
    function getOrders(string $app_id)
    {
//        return $this->connection->select("orders", [
//            "[>]users" => ["user" => "id"],
//            "[>]app_info" => ["users.app_info_id" => "id"]
//        ], '*', [
//            "app_info.accid" => $account_id
//        ]);
        return $this->connection->select("orders", "*", [
            "app_info_id" => $app_id,
            "completed" => true,
            "ORDER" => ["updated_at" => "DESC"]
        ]);
    }

    public
    function getMsToken(string $account_id)
    {
        $token = $this->connection->get("app_info", "access_token", ["accid" => $account_id]);
//        var_dump($this->connection->log());
//        var_dump($this->connection->error());
        return $token;
    }

    function createCart($chat_id, $app_info_id)
    {
        $user_id = $this->connection->get('users', 'id', ['chat_id' => $chat_id, 'app_info_id' => $app_info_id]);
        $this->insertOrUpdate('cart', ['id' => uniqid('', true), 'user' => $user_id], ['user' => $user_id]);
    }

    function addProductToCart($cart_id, $product_id, $quantity)
    {
        $pr = $this->connection->get("products_in_cart", 'id', ['cart' => $cart_id, 'products' => $product_id]);

        if ($pr)
            $this->connection->update('products_in_cart', ['quantity' => $quantity], ['id' => $pr]);
        else
            $this->connection->insert("products_in_cart", ['cart' => $cart_id, 'products' => $product_id, 'quantity' => $quantity]);
//        $this->insertOrUpdate('products_in_cart', ['cart' => $cart_id, 'products' => $product_id, 'quantity' => $quantity],
//            ['cart' => $cart_id, 'products' => $product_id]);
    }

    public function update($table, $columns, $where = [])
    {
        $this->connection->update($table, $columns, $where);
//        print_r($this->connection->log());
    }

    /**
     * @param array $barcodes
     * @param string $name default ean13
     * @return bool|string
     */
    function getBarcode(array $barcodes, string $name = 'ean13')
    {
        foreach ($barcodes as $bar) {
            if ($bar->$name) {
                return (string)$bar->$name;
            }
        }
        return false;
    }

    function createCustomMenu(array $columns, $select = 'all')
    {
        $_menu_id = $this->connection->get('custom_menu', 'id', ['name' => $columns['name'], 'app_id' => $columns['app_id']]);
        if (empty($_menu_id)) {
            $this->connection->insert('custom_menu', $columns);
            $_menu_id = $this->connection->id();
        }
        if (@$this->getError()[2]) {
            $this->logger->debug('Create custom menu failed', ['err' => $this->getError()]);
        }
        if (!empty($columns['category_id'])) {
            $this->updateCategory(['display' => true], ['id' => $columns['category_id']]);
            $where = ['category_id' => $columns['category_id']];
            if (!empty($select) and $select != 'all') {
                $where['quantity[>]'] = 0;
            }
            $this->updateProduct('', ['to_telegram' => true], $where);
            $this->logger->debug('Set display cat related with cmenu',
                ['cat' => $columns['category_id'], 'where' => $where, 'log' => $this->connection->log()]);
        }
        return $_menu_id;
    }

    function updateCustomMenu($columns, $where)
    {
        $this->connection->update('custom_menu', $columns, $where);
        if (@$this->getError()[2]) {
            $this->logger->debug('Update custom menu failed', ['err' => $this->getError(), '$where' => $where, '$columns' => $columns]);
        }
    }

    function getCustomMenu($where = [], $id = '')
    {
        if (empty($id)) {
            return $this->connection->select('custom_menu', '*', $where);
        } else {
            return $this->connection->get('custom_menu', '*', ['id' => $id]);
        }
    }

    function deleteCustomMenu($id)
    {
        $this->connection->delete('custom_menu', ['id' => $id]);
        if (@$this->getError()[2]) {
            $this->logger->debug('Delete custom menu failed', ['err' => $this->getError(), '$id' => $id]);
        }
    }

    function getTasks($where = [])
    {
        return $this->connection->select('tasks', '*', $where);
    }

    function postTask($data)
    {
        $this->connection->insert('tasks', $data);
        return $this->connection->id();
    }

    function deleteTask($id)
    {
        $this->connection->delete('tasks', ['id' => $id]);
    }

    /**
     * @param $parent_id - id parent product
     * @return false|string - path to parent category. Like 'parent_cat_name' or 'parent_cat_name_lvl1/parent_cat_name_lvl_2'
     */
    function buildPathNameForVariant($parent_id)
    {
        $this->logger->debug('Run function ' . __FUNCTION__, [
            '$parent_id' => $parent_id,
        ]);
        // looks like: SELECT * FROM "products" LEFT JOIN "categories" ON "products"."category_id" = "categories"."id" WHERE "products"."ms_id" = 10ce0846-74c0-11ea-0a80-05fd00027364 LIMIT 1
        $_p = $this->connection->get('products', ['[>]categories' => ['category_id' => 'id']], '*', ['products.ms_id' => $parent_id]);
        if (!empty($_p))
            return empty($_p['ms_path']) ? (string)$_p['name'] : "{$_p['ms_path']}/{$_p['name']}";
        else
            return false;
    }

//    function countRequestsGet($app_id)
//    {
//        return $this->connection->get('requests', '*', ['app_id' => $app_id]);
//    }
//
//    function countRequestsCreateOrUpdate($app_id, $columns = [])
//    {
//        $r = $this->connection->get('requests', '*', ['app_id' => $app_id]);
//        if ($r) {
//            if ($columns) {
//                $this->connection->update('requests', $columns, ['id' => $r['id']]);
//            }
//            return $r;
//        } else {
//            $this->connection->insert('requests', ['app_id' => $app_id]);
//            return $this->connection->get('requests', '*', ['id' => $this->connection->id()]);
//        }
//    }


    function createTables()
    {
        $this->connection->create("app_info", [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "accname" => [
                "VARCHAR(60)",
                "DEFAULT NULL"
            ],
//            "appid" => [
//                "VARCHAR(40)",
//                "DEFAULT NULL"
//            ],
//            "appuid" => [
//                "VARCHAR(60)",
//                "DEFAULT NULL"
//            ],
            "cntx" => [
                "VARCHAR(60)",
                "DEFAULT NULL"
            ],
            "accid" => [
                "VARCHAR(40)",
                "NOT NULL",
                "UNIQUE"
            ],
            "access_token" => [
                "VARCHAR(120)",
                "DEFAULT NULL"
            ],
            "status" => [
                "VARCHAR(10)",
                "DEFAULT NULL"
            ],
            "created" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ]);

        // todo разбить на группы
        $this->connection->create("app_settings", [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "tg_webhook" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "tg_bot_token" => [
                "VARCHAR(60)",
                "DEFAULT NULL",
                "UNIQUE"
            ],
            "tg_bot_name" => [
                "VARCHAR(120)",
                "DEFAULT NULL"
            ],
            "menu_custom_name_field" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "menu_custom_filter_field" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "product_filter" => [
                "VARCHAR(100)",
                "DEFAULT NULL"
            ],
            "default_product_image" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "product_price_type" => [
                "VARCHAR(120)",
                "DEFAULT 'Цена продажи'"
            ],
            "products_per_page" => [
                'TINYINT',
                "DEFAULT 8"
            ],

            "main_menu_banner" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "cart_banner" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "confirm_order_banner" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],

            "app_info_id" => [
                'INT',
                "DEFAULT NULL"
            ],
            "org_id" => [
                'char(40)',
                "DEFAULT NULL"
            ],
            "store_id" => [
                'char(40)',
                "DEFAULT NULL"
            ],
        ]);

        $this->connection->query("ALTER TABLE `app_settings` ADD FOREIGN KEY (`app_info_id`) REFERENCES `app_info` (`id`);");

        if ($this->connection->error()[2])
            $this->logger->error('Create app_info ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create("users", [
            "id" => [
                "VARCHAR(30)",
                "NOT NULL",
                "PRIMARY KEY"
            ],
            "chat_id" => [
                'INT',
                "UNIQUE"
            ],
            "state" => [
                "VARCHAR(30)",
                "DEFAULT NULL"
            ],
            "name" => [
                "VARCHAR(100)",
                "DEFAULT NULL"
            ],
            "fromTgName" => [
                "VARCHAR(100)",
                "DEFAULT NULL"
            ],
            "phone" => [
                "varchar(15)",
                "DEFAULT NULL"
            ],
            "email" => [
                "VARCHAR(100)",
                "DEFAULT NULL"
            ],
            "app_info_id" => [
                "INT",
                "DEFAULT NULL"
            ],
            "created_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ]);

        $this->connection->query("ALTER TABLE `users` ADD FOREIGN KEY (`app_info_id`) REFERENCES `app_info` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create app_info ends with errors', ['err' => $this->connection->error()]);


        $this->connection->create("categories", [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "name" => [
                "VARCHAR(120)",
//                "UNIQUE"
            ],
            "parent_id" => [
                "INT",
                "DEFAULT 0"
            ],
            "display" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "app_info" => [
                "INT",
                "NOT NULL"
            ],
            "ms_id" => [
                "CHAR(40)",
                "NULL"
            ],
            "ms_path" => [
                "varchar(255)",
                "NULL"
            ],
            "custom" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ]
        ]);

        $this->connection->query("ALTER TABLE `categories` ADD FOREIGN KEY (`app_info`) REFERENCES `app_info` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);
        $this->connection->create("products", [
            "id" => [
                "INT",
                "NOT NULL",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "ms_id" => [
                "char(40)",
                "NOT NULL"
            ],
            "article" => [
                "varchar(80)",
                "DEFAULT NULL"
            ],
            "ms_code" => [
                "varchar(80)",
                "DEFAULT NULL"
            ],
            "name" => [
                "VARCHAR(80)",
                "NOT NULL",
//                "UNIQUE"
            ],
            "description" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "image" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
//            "price_id" => [
//                "INT",
//                "DEFAULT NULL"
//            ],
            "category_id" => [
                "INT",
                "DEFAULT NULL"
            ],
            "to_telegram" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "app_info_id" => [
                "INT",
                "NOT NULL"
            ],
            "quantity" => [
                "INT",
                "DEFAULT 0"
            ],
            "type" => [
                "varchar(20)",
                "DEFAULT 'product'"
            ],
            "created_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ]);
        $this->connection->query("ALTER TABLE `products` ADD FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");
        $this->connection->query("ALTER TABLE `products` ADD FOREIGN KEY (`app_info_id`) REFERENCES `app_info` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create("prices", [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "value" => [
                "FLOAT",
                "DEFAULT 0"
            ],
            "price_type" => [
                "varchar(120)",
//                "UNIQUE", Todo need a separate entity
                "NOT NULL"
            ],
            "currency" => [
                "varchar(4)",
                "NOT NULL",
                "DEFAULT RUB"
            ],
            "ms_id" => [
                "varchar(60)",
                "DEFAULT NULL"
            ],
            'product_id' => [
                "INT",
                "DEFAULT NULL",
                "UNIQUE"
            ]
        ]);
        $this->connection->query("ALTER TABLE `prices` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create("cart", [
            "id" => [
                "VARCHAR(30)",
                "NOT NULL",
                "PRIMARY KEY"
            ],
            "user" => [
                "VARCHAR(30)"
            ],
            "amount" => [
                "SMALLINT",
                "DEFAULT 0"
            ],
            "currency" => [
                "varchar(3)",
                "DEFAULT 'RUB'"
            ],
            "completed" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "created_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ],
        ]);
        $this->connection->query("ALTER TABLE `cart` ADD FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create("products_in_cart", [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            "quantity" => [
                "TINYINT",
                "DEFAULT NULL"
            ],
            "products" => [
                "INT",
                "NOT NULL",
                "UNIQUE"
            ],
            "cart" => [
                "VARCHAR(30)"
            ]
        ]);
        $this->connection->query("ALTER TABLE `products_in_cart` ADD FOREIGN KEY (`products`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");
        $this->connection->query("ALTER TABLE `products_in_cart` ADD FOREIGN KEY (`cart`) REFERENCES `cart` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create("orders", [
            "id" => [
                "VARCHAR(30)",
                "NOT NULL",
                "PRIMARY KEY"
            ],
            "app_info_id" => [
                "INT",
                "DEFAULT NULL"
            ],
            "user" => [
                "VARCHAR(30)",
                "NOT NULL"
            ],
            "cart_info" => [
                "TEXT",
                "DEFAULT NULL"
            ],
            "items" => [
                "TEXT",
                "DEFAULT NULL"
            ],
            "completed" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "ms_id" => [
                "VARCHAR(80)",
                "DEFAULT NULL"
            ],
            "created_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ]);
        $this->connection->query("ALTER TABLE `orders` ADD FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
        $this->connection->query("ALTER TABLE `orders` ADD FOREIGN KEY (`cart`) REFERENCES `cart` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION");
        $this->connection->query("ALTER TABLE `orders` ADD FOREIGN KEY (`app_info_id`) REFERENCES `app_info` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);

        $this->connection->create('custom_menu', [
            "id" => [
                "INT",
                "AUTO_INCREMENT",
                "PRIMARY KEY"
            ],
            'app_id' => [
                "INT",
                "DEFAULT NULL"
            ],
            "display" => [
                "BOOLEAN",
                "NOT NULL",
                "DEFAULT FALSE"
            ],
            "parent_id" => [
                "INT",
                "DEFAULT 0"
            ],
            "parent_name" => [
                "VARCHAR(250)",
                "DEFAULT NULL"
            ],
            "name" => [
                "VARCHAR(250)",
                "DEFAULT NULL"
            ],
            "message" => [
                "TEXT",
                "DEFAULT NULL"
            ],
            "category_id" => [
                "INT",
                "DEFAULT NULL"
            ],
            "url" => [
                "VARCHAR(255)",
                "DEFAULT NULL"
            ],
            "text" => [
                "TEXT",
                "DEFAULT NULL"
            ],
            "created_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            "updated_at" => [
                "DATETIME",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ]);

        $this->connection->query("ALTER TABLE `custom_menu` ADD FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION");
        $this->connection->query("ALTER TABLE `custom_menu` ADD FOREIGN KEY (`app_id`) REFERENCES `app_info_id` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");

        if ($this->connection->error()[2])
            $this->logger->error('Create tables ends with errors', ['err' => $this->connection->error()]);
    }
}

//
//$db = new TgDatabase($dbAuth);
//$db->createTables();
//
//echo 'done';


