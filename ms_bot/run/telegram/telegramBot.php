<?php

/**
 * Телеграмм экомерс бот с инлайн кнопками.
 * Работает через колбеки с телеграмма.
 * Каждый шаг в виде статуса пользователя.
 *
 * Два метода работы: опрос и хуки.
 *
 * webhook регистрируется с get параметром msacc_id=your_id
 *
 *
 */

use DbCon\TgDatabase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (@$GLOBALS['rootPath']) {
    require_once $GLOBALS['rootPath'] . '/run/vendor/autoload.php';
    require_once $GLOBALS['rootPath'] . '/run/env.php';
    require_once $GLOBALS['rootPath'] . '/run/telegram/global_constants.php';
    require_once $GLOBALS['rootPath'] . '/run/telegram/telegramBotTools.php';
    require_once $GLOBALS['rootPath'] . '/run/functions.php';

} else {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../env.php';
    require_once __DIR__ . '/../functions.php';

    require_once __DIR__ . '/global_constants.php';
    require_once __DIR__ . '/telegramBotTools.php';

}


class MyTgBot
{
    private $db;
    private $telegram;
    private $logger;
    private $menu;
    private $chat_id;
    private $app_info_id;
    private $message;
    private $user_reply;
    private $prev_reply;
    private $callback_query;
    private $user_name_from_callback;
    private $user_cart = [];
    private $categories = [];
    private $menu_list = [];
//    private $products = [];
//    private array $categories = [];

    public function __construct(
        Telegram $telegram,
        TgDatabase $db,
        $app_info_id,
        $logger
    )
    {
        $this->telegram = $telegram;
        $this->db = $db;
        $this->app_info_id = $app_info_id;
        $this->logger = $logger;

        $this->menu = new TelegramMenu(
            $this->logger,
            $this->telegram,
            $this->db,
            $this->chat_id,
            $this->user_cart,
            $this->app_info_id,
            @$GLOBALS['settings']['products_per_page']
        );
    }


    public function getMessageId()
    {
        return $this->telegram->MessageID();
    }

    /**
     * Delete last message
     */
    function deleteLastMessage()
    {
        $this->telegram->deleteMessage(['chat_id' => $this->chat_id, 'message_id' => $this->getMessageId()]);
    }


    function mergeKeyboard($keyboard1, $keyboard2)
    {
        // add goBack btn. Need rework //
        try {
            $_keyboard = array_merge(json_decode($keyboard1, true)['inline_keyboard'], json_decode($keyboard2, true)['inline_keyboard']);
            $_keyboard = json_encode(['inline_keyboard' => $_keyboard]);
            return $_keyboard;
        } catch (Exception $exception) {
            $this->logger->debug('Merge goBack btn failed', ['trace' => $exception->getTrace()]);
        }
        return false;
    }

    function sendMenu(): string
    {
        global $settings;
        $this->deleteLastMessage();

        $text = 'Выберите меню нажав на кнопку ниже';
        // нужно протестировать лучше совмещение
        if (!empty($this->menu_list)) {
            if (!empty($settings['main_menu_banner']))
                $this->telegram->sendPhoto([
                    'chat_id' => $this->chat_id,
                    'photo' => $settings['main_menu_banner'],
                    'caption' => $text,
                    'reply_markup' => $this->menu->createMenu([], $this->menu_list)
                ]);
            else
                $this->telegram->sendMessage(
                    [
                        'chat_id' => $this->chat_id,
                        'text' => $text,
                        'reply_markup' => $this->menu->createMenu([], $this->menu_list)
                    ]
                );
            return 'MENU';
        } else {
            if (!empty($settings['main_menu_banner']))
                $this->telegram->sendPhoto([
                    'chat_id' => $this->chat_id,
                    'photo' => $settings['main_menu_banner'],
                    'caption' => $text,
                    'reply_markup' => $this->menu->createMenu($this->categories)
                ]);
            else
                $this->telegram->sendMessage(
                    [
                        'chat_id' => $this->chat_id,
                        'text' => $text,
                        'reply_markup' => $this->menu->createMenu($this->categories)
                    ]
                );
            return 'MENU';
        }
    }

    function getCartInfo($product_in_c)
    {
        $info = "";
        $amount = 0;
        $curr = '';
        foreach ($product_in_c as $item) {
            $product = $this->db->getProductFromDb('', '', $item['products'], '');
            $price = $this->db->getPrice($item['products']);
            if (empty($price))
                $price['value'] = 'Уточняем ';
            $sum = $item['quantity'] * (int)$price['value'];
            $amount += $sum;
            $curr = $price['currency'] ? $price['currency'] : '';
            if ($sum > 0)
                $info .= "{$product['name']} - {$item['quantity']}шт. на сумму {$sum} $curr" . PHP_EOL;
            else
                $info .= "{$product['name']} - {$item['quantity']}шт. на сумму - $curr" . PHP_EOL;
        }
        if ($amount > 0) {
            $info .= "\nИтого: {$amount} $curr";
        } else {
            $info .= "\n - - -";
        }
        return $info;
    }

    function handleDescription()
    {
        list($type_of_callback, $callback_value) = explode('_', $this->user_reply);
        if ($type_of_callback !== null && $type_of_callback != '') {
            switch ($type_of_callback) {
                case 'cmenu':
                    return $this->handleCustomMenu($callback_value);
                case 'menu':
                    return $this->sendMenu();
                case 'cart':
                    $cart = $this->db->getCart($this->app_info_id, $this->chat_id);
                    $product_in_c = $this->db->getProductsInCart($cart['id']);
                    $order_btn = empty($product_in_c) ? false : true;
                    $text = $this->getCartInfo($product_in_c);
                    $this->menu->setCart(['items' => $product_in_c]);
                    $photo = !empty($settings['cart_banner']) ? $settings['cart_banner'] : 'https://img.lovepik.com/element/40024/6975.png_860.png';
//                    $photo = 'https://blog.commlabindia.com/wp-content/uploads/2019/07/animated-gifs-corporate-training.gif';
                    $callback_data = str_replace('/', '', explode(':', $this->prev_reply)[1]);

                    $this->telegram->sendPhoto(
                        [
                            'chat_id' => $this->chat_id,
//                            'photo' => 'https://cs6.pikabu.ru/images/big_size_comm/2015-06_5/1435144652178654929.jpg',
                            'photo' => $photo,
                            'caption' => $text,
                            'reply_markup' => $this->menu->createCartKeyboard($order_btn, $callback_data),
                        ]
                    );
                    $this->deleteLastMessage();
                    return 'MENU';
                case 'add':
                    $number_of_product = 1;
                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => 'Выберите количество',
                            'reply_markup' => $this->menu->createSelectionButtons(
                                $number_of_product, $callback_value
                            )
                        ]
                    );
                    $this->deleteLastMessage();
                    return 'SELECTION_QUANTITY_OF_PRODUCTS';
            }
        }
        return 'MENU';
    }

    function selectionQuantityOfProducts()
    {
        list($type_of_callback, $quantity, $product_id) = explode('_', $this->user_reply);
        switch ($type_of_callback) {
            case "minus":
                if ($quantity <= 0) return 'SELECTION_QUANTITY_OF_PRODUCTS';
                $quantity -= 1;
                $this->telegram->editMessageReplyMarkup(
                    [
                        'chat_id' => $this->chat_id,
                        'message_id' => $this->getMessageId(),
                        'reply_markup' => $this->menu->createSelectionButtons($quantity, $product_id)
                    ]
                );
                return 'SELECTION_QUANTITY_OF_PRODUCTS';
                break;
            case "plus":
                $quantity += 1;
                $this->telegram->editMessageReplyMarkup(
                    [
                        'chat_id' => $this->chat_id,
                        'message_id' => $this->getMessageId(),
                        'reply_markup' => $this->menu->createSelectionButtons($quantity, $product_id)
                    ]
                );
                return 'SELECTION_QUANTITY_OF_PRODUCTS';

            case 'number':
                $this->telegram->sendMessage(
                    [
                        'chat_id' => $this->chat_id,
                        'text' => 'Выберите количество',
                        'reply_markup' => $this->menu->createSelectionButtons(
                            $quantity, $product_id
                        )
                    ]
                );
                $this->deleteLastMessage();
                return 'SELECTION_QUANTITY_OF_PRODUCTS';

            case "ok":
                if ($quantity > 0) {
                    // Todo добавить реализацию добавления товара в корзину
//                    add_product_to_cart();
                    $cart = $this->db->getCart($this->app_info_id, $this->chat_id);
                    if (empty($cart))
                        $this->logger->error('Cant get cart', ['c' => $cart, 'ch_id' => $this->chat_id, 'app_id' => $this->app_info_id]);
                    $cart_id = $cart['id'];
                    $this->db->addProductToCart($cart_id, $product_id, $quantity);
                    $text = 'Товар добавлен в корзину';
                    $this->telegram->answerCallbackQuery(
                        [
                            'callback_query_id' => $this->telegram->Callback_ID(),
                            'text' => $text,
                            'show_alert' => false
                        ]
                    );
                }
                $this->user_reply = "id_{$product_id}";
        }
        return $this->handleMenu();
    }

    function handleCart()
    {
        list($type_of_callback, $callback_value) = explode('_', $this->user_reply);
        if ($type_of_callback !== null && $type_of_callback != '') {
            switch ($type_of_callback) {
                case 'remove':
                    // Todo реализовать удаление товара в корзине.
//                    foreach ($this->user_cart['items'] as $key => $item) {
//                        if ($item['id'] == $callback_value)
//                            unset($this->user_cart['items'][$key]);
//                    }
                    $this->db->deleteProductInCart($callback_value, $this->user_cart['id']);

                    $text = 'Товар удален из корзины';
                    $ss = $this->telegram->Callback_Query();
                    $this->telegram->answerCallbackQuery(
                        [
                            'callback_query_id' => $this->telegram->Callback_ID(),
                            'text' => $text,
                            'show_alert' => false
                        ]
                    );
                    $this->user_reply = 'cart_';
                    return $this->handleMenu();

                case 'cmenu':
                    return $this->handleCustomMenu($callback_value);
                case 'menu':
                    return $this->sendMenu();

                case 'ordering':
//                    $my_last_message = $this->getMessageId();

                    $text = 'Напишите, пожалуйста, своё имя.';
                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => $text,
                        ]
                    );
                    $this->deleteLastMessage();
                    return 'WAITING_USERNAME';
                default:
                    return $this->handleMenu();
            }
        }
        return 'MENU';
    }

    function handleWaitingUsername()
    {
        global $settings;
        if (!is_null($this->message) && !is_null($this->chat_id)) {
            $this->logger->info('Run function ' . __FUNCTION__, [
                'chat_id' => $this->chat_id,
                'callback_query' => $this->callback_query,
                'message' => $this->message,
            ]);
            $this->db->updateUser(["name" => $this->message], $this->chat_id, $settings['app_info_id']);
            $this->telegram->sendMessage(
                [
                    "chat_id" => $this->chat_id,
                    "text" => 'Введите номер телефона',
//                    "reply_markup" => $this->telegram->buildKeyBoard([[$this->telegram->buildKeyboardButton(
//                        '📱 Отправить номер телефона',
//                        true
//                    )]])
//                    "text" => 'Напишите Ваш номер телефона по шаблону +796212312123.'
                ]
            );
        }
        list($type_of_callback, $callback_value) = explode('_', $this->user_reply);
        if ($type_of_callback !== null && $type_of_callback != '') {
            if ($type_of_callback == 'start' or $type_of_callback == 'menu' or $type_of_callback == 'Меню') {
                return $this->sendMenu();
            }
        }
        return 'WAITING_PHONE_NUMBER';
    }

    function validate_phone_number($phone): bool
    {
        // Allow +, - and . in phone number
        $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        // Remove "-" from number
        $phone_to_check = str_replace("-", "", $filtered_phone_number);
        // Check the lenght of number
        // This can be customized if you want phone number from a specific country
        if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
            return false;
        } else {
            return true;
        }
    }

    function handleWaitingPhone(): string
    {
        global $settings;
        if ((!is_null($this->message) && !is_null($this->chat_id))) {
            $this->logger->info('Run function ' . __FUNCTION__, [
                'chat_id' => $this->chat_id,
                'callback_query' => $this->callback_query,
                'message' => $this->message,
            ]);
            // нужно проверять длину, потом уже регуляркой
            $this->message = trim($this->message);
            if ($this->validate_phone_number($this->message) === false) {
//            $match = preg_match('/\+[0-9]{2}+[0-9]{12}/m', $this->message);
//            if (($this->message == '+796212312123')
//                or $match === false
//            ) {
//            if ($this->message == '+796212312123') {

//                $this->deleteLastMessage();
                $this->telegram->sendMessage(
                    [
                        "chat_id" => $this->chat_id,
                        "text" => "Некорректный номер телефона, попробуйте ввести еще раз номер в международном формате"
                    ]
                );
                return 'WAITING_PHONE_NUMBER';
            }

            if (empty($this->message)) {

//                $this->deleteLastMessage();
                $this->telegram->sendMessage(
                    [
                        "chat_id" => $this->chat_id,
                        "text" => 'Введите номер телефона еще раз, наши гномы не смогли его разобрать =('
                    ]
                );
                return 'WAITING_PHONE_NUMBER';
            }

            $this->db->updateUser(["phone" => $this->message], $this->chat_id, $settings['app_info_id']);
//            $this->db->update('users', [
//                "phone" => $this->message,
//            ], [
//                "chat_id" => $this->chat_id
//            ]);
//            $username = $this->db->get('users', 'name', [
//                'chat_id' => $this->chat_id
//            ]);
            $username = $this->db->getUser($this->chat_id, $settings['app_info_id'])['name'];
            $text = "Проверьте введенные Вами данные.\n" .
                "Ваше имя: {$username}\n" .
                "Ваш номер телефона: {$this->message}";
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
                'reply_markup' => $this->menu->createConfirmKeyboard()
            ]);
        }
        return 'HANDLE_CONFIRM_PERSONAL_DATA';
    }

    function handleConfirmPersonalData()
    {
        list($type_of_callback, $callback_value) = explode('_', $this->user_reply);
        $this->logger->debug('Run function ' . __FUNCTION__, ['user_reply' => $this->user_reply, 'type' => $type_of_callback]);

        if ($type_of_callback !== null && $type_of_callback != '') {
            switch ($type_of_callback) {
                case 'yes':
                    // Todo create order
                    global $ms_acc_id;
                    global $settings;
                    $user = $this->db->getUser($this->chat_id, $settings['app_info_id']);
                    $cart = $this->db->getCart($settings['app_info_id'], $this->chat_id, $user['id']);
                    $pr_in_c = $this->db->getProductsInCart($cart['id']);
                    $photo = !empty($settings['confirm_order_banner']) ? $settings['confirm_order_banner'] : 'https://ria.dn.ua/web/uploads/image/b6cf10762829b276035ee00d0b7694f2.png';
                    if (!empty($pr_in_c)) {
                        $pr = [];
                        foreach ($pr_in_c as $item) {
                            $pr[] = $this->db->getProductFromDb('', '', $item['products'], '');  // $item['products'] is product_id
                            $this->db->deleteProductInCart('', '', $item['id']);
                        }
                        $order_id = $this->db->createOrder($pr_in_c, $user['id'], $pr, $settings['app_info_id']);
                        $this->db->updateCart($cart['id'], ['completed' => true]);

                        try {
                            $sync = file_get_contents("https://i.spey.ru/saas/shopbot_prod/sync_products.php?accid=$ms_acc_id&new_order=$order_id");
                            $this->logger->debug('Sync order', ['response' => $sync]);
                            if (!empty($order_id) and !empty($sync->id))
                                $this->db->updateOrder($order_id, ['ms_id' => (string)$sync->id]);
                        } catch (Exception $e) {
                            $this->logger->error($e->getMessage(), ['trace' => $e->getTrace()]);
                        }

                        $text = 'Спасибо за заказ. Наш менеджер свяжется с вами в течение 15 минут.';
                        $this->telegram->sendPhoto(
                            [
                                'chat_id' => $this->chat_id,
                                'photo' => $photo,
                                'caption' => $text,
                                'reply_markup' => $this->telegram->buildInlineKeyBoard([$this->menu->getMenuButton()])
                            ]
                        );
                        $this->deleteLastMessage();

                    } else {
                        $text = 'Не могу найти товары в корзине. Наши менеджеры вам перезвонят в течение 15 минут';
                        $this->telegram->sendPhoto(
                            [
                                'chat_id' => $this->chat_id,
//                                'photo' => 'https://ria.dn.ua/web/uploads/image/b6cf10762829b276035ee00d0b7694f2.png',
                                'photo' => $photo,
                                'caption' => $text,
                            ]
                        );
                        $this->deleteLastMessage();
                    }
                    return 'MENU';
                case 'no':
                    $this->deleteLastMessage();

                    $text = 'Напишите, пожалуйста, своё имя.';
                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => $text,
                        ]
                    );
                    return 'WAITING_USERNAME';
            }
        }
        return 'MENU';
    }

    function handleSearch()
    {
        global $settings;
        if (!is_null($this->message) && !is_null($this->chat_id)) {
            $this->logger->info('Run function ' . __FUNCTION__, [
                'chat_id' => $this->chat_id,
                'callback_query' => $this->callback_query,
                'message' => $this->message,
            ]);
            $mess = htmlspecialchars($this->message);
            $text = "Товары по вашему запросу $mess \n";
            $this->menu->setProductsOnPage($settings['products_per_page']);
            $btn = $this->menu->createSearchMenu($mess, $settings['app_info_id']);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
                'reply_markup' => $btn
            ]);
            return 'MENU';
        } else {
            $this->deleteLastMessage();
            $this->handleMenu();
            return 'MENU';
        }
    }

    /**
     * @param $cmenu_id - custom menu id
     * @return string - state Home
     */
    function handleCustomMenu($cmenu_id): string
    {
        $_menu = $this->db->getCustomMenu([], $cmenu_id);
        if (!empty($_menu)) {
            $text = '';
            if (!empty($_menu['message']))
                $text = "***{$_menu['message']}*** \n\n";
            if (@$_menu['text'])
                $text .= $_menu['text'];

            if ($cmenu_id > 0)
                $_child = $this->db->getCustomMenu(['parent_id' => $cmenu_id]);
            $btns = [];
            if (!empty($_menu['category_id'])) {
                $_count = $this->db->countProducts($_menu['category_id'], $this->app_info_id);
                if (!empty($_child)) {
                    $this->logger->debug('', ['$_child' => $_child]);
                    $btns = $this->menu->createProductsMenu($_menu['category_id'], 1, $_count, $_child);
                } else {
                    $btns = $this->menu->createProductsMenu($_menu['category_id'], 1, $_count);
                }
            } elseif (!empty($_child)) {
                $btns = $this->menu->createMenu([], $_child);
            } else {
                $btns = $this->telegram->buildInlineKeyBoard([$this->menu->getMenuButton()]);
            }

            // add goBack btn. Need rework //
            try {
                $callback_data = str_replace('/', '', explode(':', $this->prev_reply)[1]);
                $back_btn = $this->telegram->buildInlineKeyBoard([$this->menu->getGoBackButton($callback_data . '_')]);
                $_keyboard = $this->mergeKeyboard($btns, $back_btn);
            } catch (Exception $exception) {
                $this->logger->debug('Merge goBack btn failed', ['trace' => $exception->getTrace()]);
            }

            $options = [
                'chat_id' => $this->chat_id,
                'text' => $text,
                'parse_mode' => 'markdown',
                'disable_web_page_preview' => 'false',
                'reply_markup' => empty($_keyboard) ? $btns : $_keyboard
            ];
            $this->telegram->sendMessage($options);
            $this->deleteLastMessage();
        } else {
            return $this->sendMenu();
        }
        return "MENU";
    }

    function handleMenu()
    {
        global $settings;
        $this->logger->info('Run function ' . __FUNCTION__, [
            'GET' => $_GET,
            'chat_id' => $this->chat_id,
            'callback_query' => $this->callback_query,
            'message' => $this->message,
            'user_reply' => $this->user_reply
        ]);
//        $this->deleteLastMessage();
        if (!is_null($this->message) && !is_null($this->chat_id)) {
            return $this->sendMenu();
        }
        $this->menu->setDb($this->db);

        list($type_of_callback, $callback_value) = explode('_', $this->user_reply);
        if ($type_of_callback !== null && $type_of_callback != '') {
            foreach ($this->categories as $category) {
                if ($type_of_callback == str_replace(' ', '', $this->menu->translit($category['name']))) {

                    $count = $this->db->countProducts($category['id'], $settings['app_info_id']);
                    if ($count > 0)
                        $text = 'Выберите товар';
                    else
                        $text = 'Товары в этой категории закончились. Оставьте свои контакты мы сообщим вам когда они будут в наличии';
//                    $this->menu->setCart($this->db->getCart($this->chat_id));
                    $this->deleteLastMessage();

                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => $text,
                            'reply_markup' => $this->menu->createProductsMenu($category['id'], 1, $count)
                        ]
                    );
                    return 'MENU';
                }
            }
            switch ($type_of_callback) {
                case'cmenu':
                    $this->logger->debug('cmenu callback', ['$this->user_reply' => $this->user_reply]);
                    $this->deleteLastMessage();

                    return $this->handleCustomMenu($callback_value);
                case 'page':
                    $this->logger->debug('Page handle', ['callback' => $callback_value]);
                    list($page_num, $cat_id) = explode('-', $callback_value);
                    if (!empty($cat_id))
                        $count = $this->db->countProducts($cat_id);
                    else $count = 0;
                    $this->telegram->editMessageReplyMarkup(
                        [
                            'chat_id' => $this->chat_id,
                            'message_id' => $this->getMessageId(),
                            'reply_markup' => $this->menu->createProductsMenu($cat_id, $page_num, $count)
                        ]
                    );
                    return 'MENU';
                case 'search':
                    $this->deleteLastMessage();

                    $text = 'Введите имя продукта.';
                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => $text,
                        ]
                    );
                    return 'SEARCH';
                case 'id':
                    $this->deleteLastMessage();

                    // Todo get products
                    $product = $this->db->getProductFromDb('', '', $callback_value);
//                    $productInfo = "Это описание товара {$product['name']}";

                    $price = $this->db->getPrice($callback_value);
                    if (empty($price['value']))
                        $price['value'] = 'Уточняется ';
                    $curr = $price['currency'] ? $price['currency'] : '';
                    if (!empty($product['description'])) {
                        $productInfo = $product['name'] . "\n\n" . $product['description'] . "\n\n" . "Цена: {$price['value']} $curr";
                    } else {
                        $productInfo = $product['name'] . "\n\n" . "Цена: {$price['value']} $curr";
                    }
//                    $productInfo = "Это описание товара {$product['name']}";
//                    $photo = !empty($product['image']) ? $product['image'] : 'https://cs10.pikabu.ru/post_img/big/2019/05/09/9/155741134919140942.jpg';
                    $photo = !empty($product['image']) ? $product['image'] : 'https://img.lovepik.com/element/40017/9806.png_860.png';
                    $callback_data = str_replace('/', '', explode(':', $this->prev_reply)[1]);
                    $btns = $this->menu->createProductDescriptionKeyboard($callback_value, $callback_data);
                    $this->telegram->sendPhoto(
                        [
                            'chat_id' => $this->chat_id,
                            'photo' => $photo,
//                            'photo' => 'https://cs10.pikabu.ru/post_img/big/2019/05/09/9/155741134919140942.jpg',
                            'caption' => $productInfo,
                            'reply_markup' => empty($_keyboard) ? $btns : $_keyboard
                        ]
                    );
                    return 'DESCRIPTION';
                case 'cart':
                    $this->deleteLastMessage();

//                    $text = $this->getCartInfo();
                    $cart = $this->db->getCart($settings['app_info_id'], $this->chat_id);
                    $product_in_c = $this->db->getProductsInCart($cart['id']);
                    $order_btn = empty($product_in_c) ? false : true;
                    $text = $this->getCartInfo($product_in_c);
                    $this->menu->setCart(['items' => $product_in_c]);

                    $photo = !empty($settings['cart_banner']) ? $settings['cart_banner'] : 'https://img.lovepik.com/element/40017/9806.png_860.png';
//                    $photo = 'https://blog.commlabindia.com/wp-content/uploads/2019/07/animated-gifs-corporate-training.gif';
                    $callback_data = str_replace('/', '', explode(':', $this->prev_reply)[1]);

                    $this->telegram->sendPhoto(
                        [
                            'chat_id' => $this->chat_id,
//                            'photo' => 'https://cs6.pikabu.ru/images/big_size_comm/2015-06_5/1435144652178654929.jpg',
                            'photo' => $photo,
                            'caption' => $text,
                            'reply_markup' => $this->menu->createCartKeyboard($order_btn, $callback_data),
                        ]
                    );

                    return 'CART';
                case 'ordering':

                    $text = 'Напишите, пожалуйста, своё имя.';
                    $this->telegram->sendMessage(
                        [
                            'chat_id' => $this->chat_id,
                            'text' => $text,
                        ]
                    );
                    return 'WAITING_USERNAME';
                case 'remove':
                    // Todo реализовать удаление товара в корзине.
//                    foreach ($this->user_cart['items'] as $key => $item) {
//                        if ($item['id'] == $callback_value)
//                            unset($this->user_cart['items'][$key]);
//                    }
                    $cart_id = $this->user_cart['id'];
                    $this->db->deleteProductInCart($callback_value, $cart_id);

                    $text = 'Товар удален из корзины';
                    $ss = $this->telegram->Callback_Query();
                    $this->telegram->answerCallbackQuery(
                        [
                            'callback_query_id' => $this->telegram->Callback_ID(),
                            'text' => $text,
                            'show_alert' => false
                        ]
                    );
                    $this->user_reply = 'cart_';
                    return $this->handleMenu();

                case 'start':
                case 'menu':
                case 'MENU':
                case 'меню':
                default:
                    return $this->sendMenu();
            }
        }
        return $this->sendMenu();
    }

    function handleUserReply()
    {
        global $settings;

        $this->logger->info('Run function ' . __FUNCTION__, [
            'GET' => $_GET,
            'chat_id' => $this->chat_id,
            'callback_query' => $this->callback_query,
            'message' => $this->message,
        ]);
        if ($this->callback_query !== null && $this->callback_query != '') {
            $this->user_reply = $this->telegram->Callback_Data();
            $this->chat_id = $this->telegram->Callback_ChatID();
            $this->menu->setChatId($this->chat_id);
            if (!$this->user_name_from_callback) {
                $q = $this->callback_query['from'];
                $this->user_name_from_callback = $q['last_name'] . ' ' . $q['first_name'];
                $this->db->updateUser(['fromTgName' => $this->user_name_from_callback], $this->chat_id, $settings['app_info_id']);
            }
        } elseif (!is_null($this->message) && !is_null($this->chat_id)) {
            $this->user_reply = $this->message;
        } else return;
        if (($this->user_reply == '/start') or ($this->user_reply == 'start')) {
            $this->db->createUser($this->chat_id, $settings['app_info_id']);
            $this->db->createCart($this->chat_id, $settings['app_info_id']);
            $this->user_cart = $this->db->getCart($this->app_info_id, $this->chat_id);
            $user_state = 'MENU';
        } else {
            $this->logger->debug('get state', ['chat_id' => $this->chat_id]);
            $user = $this->db->getUser($this->chat_id, $settings['app_info_id']);
            $this->prev_reply = empty($user['prev_reply']) ? 'MENU' : $user['prev_reply'];
            $user_state = empty($user['state']) ? 'MENU:' : $user['state'];
            // todo remove. Lazy load
            $this->user_cart = $this->db->getCart($this->app_info_id, $this->chat_id);
        }

        if (empty($user_state) or $user_state === 'NULL') {
            list($prev_state, $reply) = explode(':', @$user['prev_reply']);
            if (empty($prev_state)) {
                $user_state = 'MENU';
            } else {
                $user_state = $prev_state;
            }
            $this->logger->debug("Prev reply", ['state' => $user_state, 're' => $reply]);
        }

        // Зачем каждый раз загружать список. Я вроде решал эту проблему...
        $this->categories = $this->db->getAllCategories($settings['app_info_id'], true);
        $this->menu_list = $this->db->getCustomMenu(['app_id' => $settings['app_info_id'], 'display' => 1, 'parent_id' => 0]);

        $states = [  // new \DS\MAP
            'MENU' => 'handleMenu',
            'SEARCH' => 'handleSearch',
            'DESCRIPTION' => 'handleDescription',
            'CART' => 'handleCart',
            'SELECTION_QUANTITY_OF_PRODUCTS' => 'selectionQuantityOfProducts',
            'WAITING_USERNAME' => 'handleWaitingUsername',
            'WAITING_PHONE_NUMBER' => 'handleWaitingPhone',
            'HANDLE_CONFIRM_PERSONAL_DATA' => 'handleConfirmPersonalData',
        ];
        $state_handler = $states[$user_state];

//        $command = $this->telegram->endpoint('setMyCommands', ['command' => 'start', 'description' => 'Вернуться в меню'], true);
//        $this->logger->info('Command tg', ['c' => $command]);

        try {
            $this->logger->info('Do function ' . __FUNCTION__, [
                'state_handler' => $state_handler,
                '$user_state' => $user_state,
            ]);
            if (empty($state_handler)) $state_handler = 'handleMenu';
            $next_state = $this->$state_handler();
            $this->logger->debug('Update user states & reply', ['prev_reply' => $this->prev_reply, 'current_reply' => "$user_state:{$this->user_reply}"]);
            $this->db->updateUser(['state' => $next_state, 'prev_reply' => "$user_state:{$this->user_reply}"], $this->chat_id, $settings['app_info_id']);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getUpdates()
    {
        if (file_exists("registered.trigger")) {
            $this->telegram->deleteWebhook();
            unlink('registered.trigger');
        }
        $upd = $this->telegram->getUpdates();
        $this->logger->info('Run function ' . __FUNCTION__, ['updates' => $upd]);

        for ($i = 0; $i < $this->telegram->UpdateCount(); $i++) {
            $this->telegram->serveUpdate($i);
            $this->chat_id = $this->telegram->ChatID();
            if (!empty(@$this->telegram->Callback_Query()))
                $this->callback_query = $this->telegram->Callback_Query();
            if ($this->telegram->getUpdateType() == 'contact') {
                $this->message = $this->telegram->getData()['message']['contact']['phone_number'];
            } elseif (!$this->callback_query)
                $this->message = $this->telegram->Text(); // Возвращает текст вне зависимости от типа.
            $this->handleUserReply();
        }
    }

    function run()
    {
        global $settings;
        global $ms_acc_id;
        if ($settings['tg_webhook'] == '0') {
            $page_url = "https://i.spey.ru/saas/shopbot_prod/run/telegram/telegramBot.php?msacc_id=$ms_acc_id";
            $this->logger->debug('Url hook', [
                '$page_url' => $page_url,
                '$_SERVER["REQUEST_URI"]' => @$_SERVER["REQUEST_URI"],
                '$_SERVER["SERVER_NAME"]' => @$_SERVER["SERVER_NAME"]
            ]);
            $result = $this->telegram->deleteWebhook();
            $result = $this->telegram->setWebhook($page_url);

            if ($result['ok'] == 'true') {
                $this->db->updateAppSettings(['tg_webhook' => '1'], ['app_info_id' => $settings['id']]);
                $new_alert[] = new AlertInfo('success', 'Webhook зарегистрирован');
            } else {
                $new_alert[] = new AlertInfo('danger', 'Что-то пошло не так. Попробуйте еще раз');
            }
            if (!empty($GLOBALS['alert']) and @$new_alert)
                $GLOBALS['alert'] = array_merge($GLOBALS['alert'], $new_alert);
            else
                $GLOBALS['alert'] = @$new_alert ? $new_alert : null;
        }
//        $commands[] = array_values([
//            'command' => 'start',
//            'description' => 'Открыть меню'
//        ]);
//        $commands = [
//            'command' => 'cart',
//            'description' => 'Открыть корзину'
//        ];
//        $reg_commands = $this->telegram->endpoint('setMyCommands', $commands);
//        $this->logger->debug('Register commands', $reg_commands);

        $this->chat_id = $this->telegram->ChatID();
        $this->menu->setChatId($this->telegram->ChatID());
        $this->callback_query = $this->telegram->Callback_Query();
        if ($this->telegram->getUpdateType() == 'contact')
            $this->message = $this->telegram->getData()['message']['contact']['phone_number'];
        elseif (!$this->telegram->Callback_Query())
            $this->message = $this->telegram->Text(); // Возвращает текст вне зависимости от типа.
        $this->handleUserReply();
    }

}

$logger = new Logger('telegram');
$dir = __DIR__ . '/logs/' . date('Y-m-d') . '/telegram.log';
$logger->pushHandler(new StreamHandler($dir, Logger::DEBUG));


if (@$DEBUG)
    $ms_acc_id = '445842ed-740c-11e6-7a69-971100000991';
else {
    $ms_acc_id = @$GLOBALS['accountId'] ? $GLOBALS['accountId'] : $_GET['msacc_id'];
}

$db = new TgDatabase($dbAuth, $logger);

//$db->createTables();
$settings = $db->getAppSettings($ms_acc_id);
if (empty($settings))
    exit();

//$tg = new Telegram($telegramToken);
$tg = new Telegram($settings['tg_bot_token']);

$bot = new MyTgBot(
    $telegram = $tg,
    $db,
    $settings['app_info_id'],
    $logger
);

if ($DEBUG)
    $bot->getUpdates();
else
    $bot->run();



