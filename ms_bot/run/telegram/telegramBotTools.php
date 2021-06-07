<?php


use DbCon\TgDatabase;

class TelegramMenu
{
    private $logger;
    private $db;
    private $telegram;
    private $chat_id;
    private $app_info_id;
    private $addButtonText;
    private $menuButtonText;
    private $orderingButtonText;
    private $searchButtonText;
    private $goBackText;
    private $userCart;
    private $products;
    private $productsOnPage;

    /**
     * TelegramMenu constructor.
     * @param $logger -  used monolog logger
     * @param Telegram $telegram telegram lib
     * @param TgDatabase $db
     * @param $chat_id - telegram chat_id with user
     * @param array $user_cart
     * @param $app_info_id - id app from launch
     * @param int $productsOnPage
     */
    public function __construct($logger, $telegram, $db, $chat_id, array $user_cart, $app_info_id, int $productsOnPage = 10)
    {
        $this->logger = $logger->withName('telegram_menu');
        $this->telegram = $telegram;
        $this->chat_id = $chat_id;
        $this->app_info_id = $app_info_id;
        $this->productsOnPage = $productsOnPage;
        $this->userCart = $user_cart;
        $this->db = $db;
        $this->addButtonText = "{$GLOBALS['CHECK_MARK']} Добавить в корзину";
        $this->menuButtonText = "{$GLOBALS['LEFTWARDS_ARROW']} В меню";
        $this->orderingButtonText = "{$GLOBALS['CREDIT_CARD']} Оформить заказ";
        $this->searchButtonText = "{$GLOBALS['LOUPE']} Поиск";
        $this->goBackText = "{$GLOBALS['LEFTWARDS_ARROW_UP']} Назад";
    }

    /**
     * @param int $productsOnPage
     */
    public function setProductsOnPage(int $productsOnPage): void
    {
        $this->productsOnPage = $productsOnPage;
    }

    /**
     * @param mixed $chat_id
     */
    public function setChatId($chat_id)
    {
        $this->chat_id = $chat_id;
    }

    public function setProducts($products)
    {
        $this->products = $products;
    }

    public function setDb(TgDatabase $db)
    {
        $this->db = $db;
    }

    public function setCart($cart)
    {
        $this->userCart = $cart;
    }

    function getAddButton($product_id)
    {
        $this->logger->info('Run function ' . __FUNCTION__, ['product_id' => $product_id]);
        return [
            $this->telegram->buildInlineKeyboardButton(
                $this->addButtonText,
                $url = '',
                $callback_data = "add_{$product_id}"
            )
        ];
    }

    function getGoBackButton($callback): array
    {
        $this->logger->info('Run function ' . __FUNCTION__, ['$callback' => $callback]);
        return [
            $this->telegram->buildInlineKeyboardButton(
                $this->goBackText,
                $url = '',
                $callback_data = $callback
            )
        ];
    }

    function getMenuButton(): array
    {
        return [
            $this->telegram->buildInlineKeyboardButton(
                $this->menuButtonText,
                $url = '',
                $callback_data = 'menu_btn'
            )
        ];
    }

    function getOrderingButton()
    {
        return [
            $this->telegram->buildInlineKeyboardButton(
                $this->orderingButtonText,
                $url = '',
                $callback_data = 'ordering_btn'
            )
        ];
    }

    function getSearchButton()
    {
        return [
            $this->telegram->buildInlineKeyboardButton(
                $this->searchButtonText,
                $url = '',
                $callback_data = 'search_btn'
            )
        ];
    }

    function getKeyboardMarkup()
    {
        return [
            $this->telegram->buildKeyboardButton(
                'Меню',
                $url = '',
                $callback_data = 'menu_btn'
            ),
            $this->telegram->buildKeyboardButton(
                'Корзина',
                $url = '',
                $callback_data = 'cart_btn'
            )
        ];
    }

    function createCartKeyboard(bool $order_btn = true, $prev_reply = null)
    {
        $button_list[] = $this->getRemoveButtons();
        $button_list[] = $this->getMenuButton();
        if ($order_btn)
            $button_list[] = $this->getOrderingButton();
        if ($prev_reply) $button_list[] = $this->getGoBackButton($prev_reply);


        $this->logger->info(
            'Run function ' . __FUNCTION__,
            ['button_list' => $button_list]
        );
        return $this->telegram->buildInlineKeyBoard($button_list);
    }

    function createProductDescriptionKeyboard($product_id, $prev_reply = null)
    {
        $button_list[] = $this->getAddButton($product_id);
        $button_list[] = $this->getCartButton();
        $button_list[] = $this->getMenuButton();
        if ($prev_reply) $button_list[] = $this->getGoBackButton($prev_reply);
        return $this->telegram->buildInlineKeyBoard($button_list);
    }

    function createProductButtons($page_number, $category_id = null, $product_name = '', $app_info_id = '')
    {
        $this->logger->debug('Run function ' . __FUNCTION__, ['page_number' => $page_number, 'category_id' => $category_id, 'product_name' => $product_name]);
        $last_element_index = $this->productsOnPage * $page_number;
        $first_element_index = $last_element_index - $this->productsOnPage;

        if ($category_id) {
            $products_for_current_page = $this->db->getProductsFromCategory($category_id, $this->productsOnPage, $first_element_index);
        } elseif ($product_name) {
//            $products_for_current_page = $this->db->getProductFromDb(['name' => $product_name, 'to_telegram' => 1]);
            $products_for_current_page = $this->db->searchProducts($product_name, $app_info_id);
            $this->logger->debug('Run function ' . __FUNCTION__, ['products_for_current_page' => $products_for_current_page, 'log' => $this->db->getLog()]);

        } else {
            $products_for_current_page = $this->db->getAllproducts($this->productsOnPage, $first_element_index);
        }

        $this->logger->debug('Create product btns', [
            'products_for_current_page' => $products_for_current_page
        ]);
        $button_list = [];

        $user_id = $this->db->getCart($this->app_info_id, $this->chat_id);
        if (!empty($user_id)) {
            $user_id = $user_id['id'];
            $products_in_cart = $this->db->getProductsInCart($user_id);
            foreach ($products_for_current_page as $product) {
                if ($products_in_cart and in_array($product['id'], $products_in_cart))
                    $text = "{$GLOBALS['PACKAGE']} {$product['name']} ({$GLOBALS['CHECK_MARK']} Уже в корзине)";
                else
                    $text = "{$GLOBALS['PACKAGE']} {$product["name"]}";

                $button_list[] = [
                    $this->telegram->buildInlineKeyboardButton(
                        $text,
                        $url = '',
                        $callback_data = "id_{$product['id']}"
                    )
                ];
            }
        }
        return $button_list;
    }

    function getCartButton()
    {
        if (!empty($this->userCart['amount']))
            $text_on_button = "{$GLOBALS['SHOPPING_CART']} Корзина (Итог на сумму: {$this->userCart['amount']})";
        else
            $text_on_button = "{$GLOBALS['SHOPPING_CART']} Корзина";
        return [
            $this->telegram->buildInlineKeyboardButton(
                $text_on_button,
                $url = '',
                $callback_data = "cart_"
            )
        ];
    }

    function createSelectionButtons($quantity_of_products, $product_id)
    {
        $button_list = [
            [$this->telegram->buildInlineKeyboardButton(
                $GLOBALS['MINUS'],
                $url = '',
                $callback_data = "minus_{$quantity_of_products}_{$product_id}"
            ),
                $this->telegram->buildInlineKeyboardButton(
                    "{$quantity_of_products} шт.",
                    $url = '',
//                    $callback_data = 'number_of_products_btn'
                    $callback_data = "number_{$quantity_of_products}_{$product_id}"
                ),
                $this->telegram->buildInlineKeyboardButton(
                    $GLOBALS['PLUS'],
                    $url = '',
                    $callback_data = "plus_{$quantity_of_products}_{$product_id}"
                ),
            ],
            [$this->telegram->buildInlineKeyboardButton(
                $GLOBALS['OK'],
                $url = '',
                $callback_data = "ok_{$quantity_of_products}_{$product_id}"
            )],
        ];
        $this->logger->info(
            'Run function ' . __FUNCTION__,
            ['quantity_of_products' => $quantity_of_products, 'product_id' => $product_id, 'button_list' => $button_list]
        );
        return $this->telegram->buildInlineKeyBoard($button_list);
    }

    function getRemoveButtons()
    {
        $remove_buttons = [];
        foreach ($this->userCart['items'] as $item) {
            $product = $this->db->getProductFromDb('', '', $item['products'], '');
            $remove_buttons[] = $this->telegram->buildInlineKeyboardButton(
                "{$GLOBALS['CROSS_MARK']} Удалить {$product['name']}",
                $url = '',
                $callback_data = "remove_{$product['id']}"
            );
        }

        return $remove_buttons;
    }

    function createConfirmKeyboard()
    {
        $button_list[] = [$this->telegram->buildInlineKeyboardButton(
            'Всё верно',
            $url = '',
            $callback_data = 'yes_'
        )];
        $button_list[] = [$this->telegram->buildInlineKeyboardButton(
            'Данные некорректны',
            $url = '',
            $callback_data = 'no_'
        )];
        return $this->telegram->buildInlineKeyBoard($button_list);
//        return $this->telegram->buildInlineKeyBoard($button_list);
    }

    public function createPagination(int $number_of_products, int $page_num, $category_id = 0)
    {
        $this->logger->info(
            'Run function ' . __FUNCTION__,
            ['number_of_products' => $number_of_products, 'page_num' => $page_num]
        );

        if ($number_of_products < ($this->productsOnPage + 1)) return null;

        $number_of_pages = ceil($number_of_products / $this->productsOnPage);
        if (3 < $page_num and $page_num < $number_of_pages - 2) {
            $first_button = $this->telegram->buildInlineKeyboardButton(
                "← 1",
                $url = '',
                $callback_data = "page_1-$category_id"
            );
            $new_p_num = $page_num - 1;
            $second_button = $this->telegram->buildInlineKeyboardButton(
                "← {$new_p_num}",
                $url = '',
                $callback_data = "page_{$new_p_num}-$category_id"
            );
            $third_button = $this->telegram->buildInlineKeyboardButton(
                "⋅{$page_num}⋅",
                $url = '',
                $callback_data = "page_{$page_num}-$category_id"
            );
            $new_p_num = $page_num + 1;
            $fourth_button = $this->telegram->buildInlineKeyboardButton(
                " {$new_p_num} →",
                $url = '',
                $callback_data = "page_{$new_p_num}-$category_id"
            );
            $fifth_button = $this->telegram->buildInlineKeyboardButton(
                " {$number_of_pages} →",
                $url = '',
                $callback_data = "page_{$number_of_pages}-$category_id"
            );
            return [
                $first_button, $second_button, $third_button, $fourth_button, $fifth_button
            ];
        }
        $products_for_five_pages = $this->productsOnPage * 5;
        if ($number_of_products < $products_for_five_pages) {
            $button_list = [];

            for ($i = 1; $i <= $number_of_pages; $i++) {
                if ($page_num == $i) {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        "⋅{$i}⋅",
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                } else {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        $i,
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                }
            }
            return $button_list;
        }
        $first_three_pages = 3;
        $number_of_first_button = 1;
        $number_of_fourth_button = 4;
        if ($page_num <= $first_three_pages) {
            $button_list = [];

            for ($i = $number_of_first_button; $i < $number_of_fourth_button; $i++) {
                if ($page_num == $i) {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        "⋅{$i}⋅",
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                } else {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        $i,
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                }
            }
            $button_list[] = $this->telegram->buildInlineKeyboardButton(
                '4 →',
                $url = '',
                $callback_data = "page_4-$category_id"
            );
            $button_list[] = $this->telegram->buildInlineKeyboardButton(
                $number_of_pages,
                $url = '',
                $callback_data = "page_{$number_of_pages}-$category_id"
            );
            return $button_list;
        }
        $number_of_fifth_button = $number_of_pages;
        $number_of_third_button = $number_of_pages - 2;
        if ($page_num >= $number_of_pages - 2) {
            $button_list[] = $this->telegram->buildInlineKeyboardButton(
                '← 1',
                $url = '',
                $callback_data = "page_1-$category_id"
            );
            $button_list[] = $this->telegram->buildInlineKeyboardButton(
                "← " . ($number_of_pages - 3),
                $url = '',
                $callback_data = "page_" . ($number_of_pages - 3) . "-$category_id"
            );

            for ($i = $number_of_third_button; $i <= $number_of_fifth_button; $i++) {
                if ($page_num == $i) {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        "⋅{$i}⋅",
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                } else {
                    $button_list[] = $this->telegram->buildInlineKeyboardButton(
                        $i,
                        $url = '',
                        $callback_data = "page_{$i}-$category_id"
                    );
                }
            }
            return $button_list;
        }
    }

    function createSearchMenu($name, $app_info_id)
    {
        $this->logger->info(
            'Run function ' . __FUNCTION__,
            ['name' => $name]
        );
        $btn_list = $this->createProductButtons(1, '', $name, $app_info_id);
        $btn_list[] = $this->getMenuButton();
        return $this->telegram->buildInlineKeyBoard($btn_list);

    }

    function createProductsMenu($category_id, $page_num = 1, $count = 0, $ch = [])
    {
        $this->logger->info(
            'Run function ' . __FUNCTION__,
            ['page_num' => $page_num, 'category_id' => $category_id]
        );
//        $count = $this->db->countProducts($category_id);
        if ($count > 0)
            $menu = $this->createProductButtons($page_num, $category_id);
//        $pagination = $this->createPagination(count($this->products), $page_num);
        $pagination = $this->createPagination($count, $page_num, $category_id);
        if (!is_null($pagination)) {
            $menu[] = $pagination;
        }

        // todo rework
        if (!empty($ch)) {
            foreach ($ch as $item) {
                $this->logger->debug('', ['$item' => $item]);
                if ($item['url']) {
                    $menu[] = [
                        $this->telegram->buildInlineKeyboardButton(
                            "{$GLOBALS['FOLDER']} {$item['name']}",
                            $url = $item['url']
                        )
                    ];
                } else {
                    $menu[] = [
                        $this->telegram->buildInlineKeyboardButton(
                            "{$GLOBALS['FOLDER']} {$item['name']}",
                            $url = '',
                            $callback_data = 'cmenu_' . $item['id'])
                    ];
                }
            }
        }
        $menu[] = $this->getCartButton();
        $menu[] = $this->getMenuButton();
        return $this->telegram->buildInlineKeyBoard($menu);
    }

    function createMenu(array $categories = [], $custom_menu_list = [])
    {
        $option = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $option[] = [
                    $this->telegram->buildInlineKeyboardButton(
                        "{$category['name']}",
                        $url = '',
                        $callback_data = str_replace(' ', '', $this->translit($category['name'])) . '_'),
                ];
            }
            $option[] = $this->getSearchButton();
        }
        if (!empty($custom_menu_list)) {
            foreach ($custom_menu_list as $item) {
                if ($item['url']) {
                    $option[] = [
                        $this->telegram->buildInlineKeyboardButton(
                            "{$item['name']}",
                            $url = $item['url']
//                            $url = 'https://www.google.com/'
                        )
                    ];
                } else {
                    $option[] = [
                        $this->telegram->buildInlineKeyboardButton(
                            "{$item['name']}",
                            $url = '',
                            $callback_data = 'cmenu_' . $item['id'])
                    ];
                }
            }
            $option[] = $this->getSearchButton();
            if ($custom_menu_list[0]['parent_id'] != 0) {
                $option[] = $this->getMenuButton();
            }
        }
//        $option[] = $this->getKeyboardMarkup();
        return $this->telegram->buildInlineKeyBoard($option);
    }

    /**
     * @param string $s
     * @return string
     */
    function translit(string $s)
    {
        $s = (string)$s;
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr($s, ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => '']);
        return $s;
    }

}

