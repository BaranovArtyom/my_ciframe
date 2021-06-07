<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ShopBot for Marketplace of MoySklad">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>ShopBot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.5.5/dist/css/uikit.min.css"/>

</head>
<body class="uk-animation-fade uk-animation-fast">
<div class="uk-section uk-section-xsmall">
    <div style="padding-left: 31px;">
        <section class="body">
            <div class="container">
                <ul uk-tab uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
                    <li>
                        <a href="">Заказы
                            <?php if (!empty($settings['tg_bot_name'])) echo $settings['tg_bot_name'] ?>
                            <?php if (!empty($count_orders) and $count_orders > 0) echo "<span class='uk-badge'>$count_orders</span>" ?>
                        </a>
                    </li>
                    <li>
                        <a href="">Выгруженные товары
                            <?php if (!empty($count_products) and $count_products > 0) echo "<span class='uk-badge'>$count_products</span>" ?>
                        </a>
                    </li>
                    <li <?php if (!$isAdmin) { ?>
                        class="uk-disabled"
                    <?php } ?>
                            class="uk-active">
                        <a>Настройки</a>
                    </li>
                </ul>
                <ul class="uk-switcher uk-margin">
                    <li>
                        <ul class="uk-list uk-list-striped">
                            <?php if (!empty($orders)) foreach ($orders as $order) {
                                $order_name = empty($order['ms_name']) ? $order['id'] : $order['ms_name']; ?>
                                <li>
                                <?php if (!empty($order['ms_id'])) { ?>
                                    <a href="https://online.moysklad.ru/app/#customerorder/edit?id=<?php echo "{$order['ms_id']}"; ?>"
                                       target="_blank" uk-tooltip="Редактировать заказ">
                                        <?php echo "Заказ в МоемСкладе под номером TG-{$order_name}"; ?>
                                    </a>
                                    <span class="uk-label uk-align-right"><?php echo $order['updated_at']; ?></span>
                                <?php } else { ?>
                                    <?php echo "Заказ в МоемСкладе под номером TG-{$order_name}"; ?>
                                    <span class="uk-label uk-align-right"><?php echo $order['updated_at']; ?></span>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <p>Заказов пока нет</p>
                            <?php } ?>
                        </ul>
                    </li>
                    <li>
                        <ul class="uk-list uk-list-striped" id="product_list">
                            <?php if (!empty($products)) { ?>
                                <?php foreach ($products as $v) { ?>
                                    <li>
                                        <?php echo $v['name']; ?>
                                        <span class="uk-label uk-align-right">
                                    <?php echo $v['article'] ?></span>
                                    </li>
                                <?php }
                            } ?>
                            <ul class="uk-pagination uk-margin">
                                <li><a href="#" id="pag_prev" page="1"><span class="uk-margin-small-right"
                                                                             uk-pagination-previous></span> Previous</a>
                                </li>
                                <li class="uk-margin-auto-left"><a href="#" id="pag_next" page="2">Next <span
                                                class="uk-margin-small-left" uk-pagination-next></span></a></li>
                            </ul>
                        </ul>

                    </li>
                    <li>
                        <div id="alert_div">
                            <?php if (!empty($GLOBALS['alert'])) {
                                foreach ($GLOBALS['alert'] as $alert) { ?>
                                    <div class="uk-alert-<? echo $alert->action ?> uk-text-center" uk-alert>
                                        <a class="uk-alert-close" uk-close></a>
                                        <p><? echo $alert->message ?></p>
                                    </div>
                                <?php }
                                $GLOBALS['alert'] = null;
                            } ?>
                        </div>

                        <?php if ($isAdmin) { ?>
                            <div uk-grid>
                                <div class="uk-width-auto">
                                    <ul class="uk-tab-left" uk-tab="connect: #settings">
                                        <li class="uk-active"><a href="#">Основные</a></li>
                                        <li><a href="#">Товары</a></li>
                                        <li><a href="#">Design</a></li>
                                        <li><a href="#">FAQ</a></li>
                                    </ul>
                                </div>
                                <div class="uk-width-expand">
                                    <ul id="settings" class="uk-switcher">
                                        <li>

                                            <div class="uk-margin">
                                                <h3>Создать меню:</h3>
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        <a href="#c_menu_form" uk-toggle
                                                           class="uk-button uk-button-default">
                                                            Добавить меню
                                                        </a>
                                                    </div>
                                                    <form method="post"
                                                          action="index.php?task=updateSettings&action=c_menu&contextKey=<?php echo $contextKey ?>&accid=<?php echo $accountId ?>"
                                                          id="c_menu_form" uk-modal>
                                                        <!--                                                                  action="index.php?task=updateSettings&action=c_menu&contextKey=-->
                                                        <?php //echo $contextKey ?><!--"-->
                                                        <!--                                                                  method="post">-->
                                                        <div class="uk-modal-dialog">
                                                            <button class="uk-modal-close-default" type="button"
                                                                    uk-close></button>
                                                            <div class="uk-modal-header">
                                                                <h2 class="uk-modal-title">Добавить новое меню</h2>
                                                            </div>

                                                            <div class="uk-modal-body">
                                                                <div class="uk-margin">
                                                                    <div class="uk-inline">
                                                                                <span class="uk-form-icon"
                                                                                      uk-icon="icon: tag"></span>
                                                                        <input type="text" size="100" required
                                                                               id="c_menu_name"
                                                                               name="c_menu_name"
                                                                               class="uk-input"
                                                                               placeholder="Введите название"
                                                                               value=""/>
                                                                    </div>
                                                                    <p></p>
                                                                    <div class="uk-inline">
                                                                                <span class="uk-form-icon"
                                                                                      uk-icon="icon: comment"></span>
                                                                        <input type="text" size="100" required
                                                                               id="c_menu_message"
                                                                               name="c_menu_message"
                                                                               class="uk-input"
                                                                               placeholder="Сообщение"
                                                                               value=""/>
                                                                    </div>
                                                                    <p></p>
                                                                    <select class="uk-select"
                                                                            id="add_row_c_menu">
                                                                        <option value="text">Добавить текст</option>
                                                                        <option value="cat">Привязать категорию</option>
                                                                        <option value="url">Добавить ссылку</option>
                                                                    </select>
                                                                    <div id="row_c_menu">
                                                                        <p></p>
                                                                        <input type="text" size="100"
                                                                               id="c_menu_url" name="c_menu_url"
                                                                               class="uk-input uk-hidden"
                                                                               placeholder="Введите ссылку"
                                                                               value/>
                                                                        <textarea id="c_menu_text"
                                                                                  class="uk-textarea"
                                                                                  rows="5"
                                                                                  name="c_menu_text"
                                                                                  placeholder="Текст или"></textarea>
                                                                        <select id="c_menu_cat"
                                                                                class="uk-select uk-hidden"
                                                                                name="c_menu_cat">
                                                                            <option disabled selected value>
                                                                                -- Выбирите категорию --
                                                                            </option>
                                                                            <?php if (!empty($categories))
                                                                                foreach ($categories as $category) {
                                                                                    ?>
                                                                                    <option value="<?php echo $category['id'] ?>">
                                                                                        <?php echo $category['name'] ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <p></p>
                                                                    <div id="c_menu_pos">
                                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                                            Родительский пункт меню*
                                                                        </div>
                                                                        <select id="c_menu_field"
                                                                                class="uk-select"
                                                                                name="c_menu_field">
                                                                            <option disabled selected value>
                                                                                -- Выбирите пункт меню --
                                                                            </option>
                                                                            <?php if (!empty($menu_list))
                                                                                foreach ($menu_list as $menu) {
                                                                                    ?>
                                                                                    <option value="<?php echo $menu['id'] ?>">
                                                                                        <?php echo $menu['name'] ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                        </select>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="uk-text-small uk-text-light uk-text-muted">*
                                                                необязательно
                                                            </div>
                                                            <div class="uk-modal-footer uk-text-right">
                                                                <button class="uk-button uk-button-default uk-modal-close"
                                                                        type="button">Cancel
                                                                </button>

                                                                <input type="hidden" name="accountId"
                                                                       value="<?php echo $accountId ?>"/>
                                                                <input type="submit"
                                                                       class="uk-button uk-button-primary"
                                                                       value="Сохранить">
                                                                <div id="spinner" class="uk-hidden" uk-spinner
                                                                     style="padding-left: 1em;"></div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        *
                                                    </div>
                                                </div>

                                                <div id="menu_list">
                                                    <?php if (!empty($menu_list_dom)) echo $menu_list_dom; ?>
                                                </div>
                                            </div>

                                            <form method="post"
                                                  action="index.php?task=updateSettings&contextKey=<?php echo $contextKey ?>&action=main&accid=<?php echo $accountId ?>">
                                                <h5>Настройки телеграмм бота</h5>

                                                <div class="uk-margin">
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: user"></span>
                                                        <input type="text" size="50" id="botName" name="botName"
                                                               class="uk-input"
                                                               placeholder="Введите имя телеграмм бота"
                                                               value="<?php if (!empty($settings['tg_bot_name'])) echo $settings['tg_bot_name'] ?>"
                                                               uk-tooltip="Имя телеграмм бота"/>
                                                    </div>
                                                </div>
                                                <div class="uk-margin">
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: lock"></span>
                                                        <input type="text" size="50" id="botToken" name="botToken"
                                                               class="uk-input"
                                                               placeholder="Введите токен телеграмм бота"
                                                               value="<?php if (!empty($settings['tg_bot_token'])) echo $settings['tg_bot_token'] ?>"
                                                               uk-tooltip="Токен телеграмм бота"/>
                                                    </div>
                                                </div>
                                                <?php if ($tg_webhook_is_set == 0) { ?>
                                                    <div class="uk-margin">
                                                        <a href="index.php?task=activateBot&contextKey=<?php echo $contextKey ?>&accid=<?php echo $accountId ?>"
                                                           uk-tooltip="Активировать бота">
                                                            Активировать бота
                                                        </a>
                                                    </div>
                                                <?php } else { ?>

                                                    <div class="uk-margin">
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            Установить валюту.
                                                        </div>
                                                        <!-- <p>Тип цены товаров в телеграмм:</p>-->
                                                        <?php if (!empty($currency->rows)) { ?>
                                                            <div class="uk-inline">
                                                                <select class="uk-select" name="product_currency">
                                                                    <?php foreach ($currency->rows as $cur) { ?>
                                                                        <option <? if ($cur->id == @$settings['currency_id']) {
                                                                            echo 'selected="selected"';
                                                                        } elseif (empty($settings['currency_id']) and $cur->isoCode == 'RUB') {
                                                                            echo 'selected="selected"';
                                                                        } ?>
                                                                                value="<?php echo $cur->id ?>">
                                                                            <?php echo $cur->fullName ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        <?php } ?>
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            * При создании нового заказа будет использоваться эта
                                                            валюта. Убедитесь, что цена на товар указана в выбранной
                                                            валюте.
                                                            <!--                                                            Чтобы изменения вступили в силу, потребуется запустить-->
                                                            <!--                                                            синхронизацию товаров.-->
                                                        </div>
                                                    </div>

                                                    <div class="uk-margin">
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            Установить организацию.
                                                        </div>
                                                        <!-- <p>Тип цены товаров в телеграмм:</p>-->
                                                        <?php if (!empty($organizations->rows)) { ?>
                                                            <div class="uk-inline">
                                                                <select class="uk-select" name="organization">
                                                                    <?php foreach ($organizations->rows as $org) { ?>
                                                                        <option <? if ($org->id == @$settings['org_id']) {
                                                                            echo 'selected="selected"';
                                                                        } ?>
                                                                                value="<?php echo $org->id ?>">
                                                                            <?php echo $org->name ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        <?php } ?>
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            * Это значение будет учитываться при формировании заказа
                                                            <!--                                                            Чтобы изменения вступили в силу, потребуется запустить-->
                                                            <!--                                                            синхронизацию товаров.-->
                                                        </div>
                                                    </div>
                                                    <div class="uk-margin">
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            Установить склад.
                                                        </div>
                                                        <!-- <p>Тип цены товаров в телеграмм:</p>-->
                                                        <?php if (!empty($stores->rows)) { ?>
                                                            <div class="uk-inline">
                                                                <select class="uk-select" name="store">
                                                                    <?php foreach ($stores->rows as $store) { ?>
                                                                        <option
                                                                            <? if ($store->id == @$settings['store_id']) {
                                                                                echo 'selected="selected"';
                                                                            } ?>
                                                                                value="<?php echo $store->id ?>">
                                                                            <?php echo $store->name ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        <?php } ?>
                                                        <div class="uk-text-small uk-text-light uk-text-muted">
                                                            * Это значение будет учитываться при формировании заказа
                                                            <!--                                                            Чтобы изменения вступили в силу, потребуется запустить-->
                                                            <!--                                                            синхронизацию товаров.-->
                                                        </div>
                                                    </div>


                                                    <?php if (!empty($categories)) { ?>
                                                        <p>Категории на основе которых создать меню:</p>
                                                        <div class="uk-margin">
                                                            <a href="sync_products.php?accid=<?php echo $accountId ?>&sync_cat=true&sync_products=true&contextKey=<?php echo $contextKey ?>"
                                                               id="sync">Обновить товары и категории</a>
                                                            <div class='uk-text-small uk-text-light uk-text-muted'>При
                                                                большом количестве товаров в базе (>5000) и первой
                                                                синхронизации (идет обработка изображений), обновление
                                                                может занять время.<br>
                                                                Если синхронизация падает с ошибкой, нажмите
                                                                синхронизацию еще раз. Продолжиться с того места, где
                                                                была закончена. Работаем над исправлением.
                                                                Дождитесь пожалуйста. <br>Над автоматическим обновлением
                                                                цен и остатков уже работаем.
                                                            </div>
                                                        </div>
                                                        <?php echo $category_list_dom ?>
                                                    <?php } else { ?>
                                                        <div class="uk-margin">
                                                            <!--                                                            <a href="sync_products.php?accid=-->
                                                            <?php //echo $accountId ?><!--&sync_cat=true&sync_products=true">Синхронизировать-->
                                                            <a href="sync_products.php?accid=<?php echo $accountId ?>&sync_cat=true&sync_products=true&contextKey=<?php echo $contextKey ?>"
                                                               id="sync">Синхронизировать категории</a>
                                                        </div>
                                                    <?php }
                                                } ?>
                                                <input type="hidden" id="accountId" name="accountId"
                                                       value="<?php echo $accountId ?>"/>

                                                <a href="#" id="setDefault" context="<?= $contextKey ?>"
                                                   class="uk-button uk-button-default">
                                                    Сбросить настройки
                                                </a>

                                                <input type="submit" class="uk-button uk-button-primary"
                                                       value="Сохранить">
                                            </form>
                                        </li>
                                        <li>
                                            <form method="post"
                                                  action="index.php?task=updateSettings&action=products&contextKey=<?php echo $contextKey ?>&accid=<?php echo $accountId ?>">
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        <label>
                                                            <input class='uk-checkbox' type='checkbox' name='stock'
                                                                   value='positiveOnly'
                                                                <?php if (@$settings['quantityMode'] == 'positiveOnly') echo 'checked'; ?>
                                                            >
                                                            Публиковать товары только с положительным остатком.
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Количество товаров на странице:
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" name="products_per_page"
                                                               class="uk-input"
                                                               placeholder="Введите количество товаров"
                                                               uk-tooltip="Тип цены товаров"
                                                               value="<?php if (!empty($settings['products_per_page'])) echo $settings['products_per_page'] ?>"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Тип цены товаров в телеграмм.
                                                    </div>
                                                    <!-- <p>Тип цены товаров в телеграмм:</p>-->
                                                    <?php if (!empty($prices)) { ?>
                                                        <div class="uk-inline">
                                                            <!--                                                            --><?php //echo $settings['product_price_type']?>
                                                            <select class="uk-select" name="product_price_type">
                                                                <?php foreach ($prices as $price) { ?>
                                                                    <option <? if (!empty($settings['product_price_type']) and $price->name == $settings['product_price_type']) echo 'selected="selected"' ?>
                                                                            value="<?php echo $price->name ?>">
                                                                        <?php echo $price->name ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="uk-inline">
                                                            <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                            <input type="text" size="70" name="product_price_type"
                                                                   class="uk-input"
                                                                   placeholder="Введите название типа цены"
                                                                   uk-tooltip="Тип цены товаров"
                                                                   value="<?php if (!empty($settings['product_price_type'])) echo $settings['product_price_type'] ?>"
                                                            />
                                                        </div>
                                                    <?php } ?>
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        * Чтобы изменения вступили в силу, потребуется запустить
                                                        синхронизацию товаров.
                                                    </div>
                                                </div>

                                                <!--                                                <p>Изображение по умолчанию:</p>-->
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Изображение по умолчанию:
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" size="70" name="product_image"
                                                               class="uk-input"
                                                               placeholder="Введите ссылку на изображение"
                                                               uk-tooltip="Изображение по умолчанию"
                                                               value="<?php if (!empty($settings['default_product_image'])) echo $settings['default_product_image'] ?>"
                                                        />
                                                    </div>
                                                </div>
                                                <!--                                                <p>Выгружать товары по фильтру:</p>-->
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Отбирать товары из МойСклад по фильтру:
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" size="70" name="product_filter"
                                                               class="uk-input"
                                                               placeholder="Добавить фильтр имя_фильтра=значение_фильтра"
                                                               uk-tooltip="Выгружать товары по фильтру."
                                                               value="<?php if (!empty($settings['product_filter'])) echo $settings['product_filter'] ?>"
                                                        />
                                                    </div>
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        * (Название дополнительного поля товара) = (Значение поля.
                                                        Если поле "Флажок" и нужно выгружать отмеченые - значение
                                                        установить 1)
                                                        <br>Например: Отображать в телеграм=1
                                                    </div>
                                                </div>
                                                <input type="hidden" name="accountId" value="<?php echo $accountId ?>"/>
                                                <input type="submit" class="uk-button uk-button-primary">
                                            </form>
                                        </li>
                                        <li>
                                            <form method="post"
                                                  action="index.php?task=updateSettings&action=design&contextKey=<?php echo $contextKey ?>&accid=<?php echo $accountId ?>">

                                                <div class="uk-text-small uk-text-light uk-text-muted">
                                                    Обратите внимание! Принимается только прямая ссылка на изображение,
                                                    связано с ограничениями телеграм, так как мы не скачиваем само
                                                    изображение.
                                                    Пример: <!-- noindex -->
                                                    https://i2.rozetka.ua/promotions/1790/1790296.jpg<!--/ noindex -->
                                                </div>

                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Ссылка на баннер в меню:
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" name="main_menu_banner"
                                                               size="70"
                                                               class="uk-input"
                                                               placeholder="Баннер на главной"
                                                               uk-tooltip="Баннер на главной"
                                                               value="<?php if (!empty($settings['main_menu_banner'])) echo $settings['main_menu_banner'] ?>"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Ссылка на баннер в корзине.
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" name="cart_banner"
                                                               size="70"
                                                               class="uk-input"
                                                               placeholder="Баннер в корзине"
                                                               uk-tooltip="Баннер в корзине"
                                                               value="<?php if (!empty($settings['cart_banner'])) echo $settings['cart_banner'] ?>"
                                                        />
                                                    </div>
                                                </div>

                                                <!--                                                <p>Изображение по умолчанию:</p>-->
                                                <div class="uk-margin">
                                                    <div class="uk-text-small uk-text-light uk-text-muted">
                                                        Ссылка на баннер после завершения заказа:
                                                    </div>
                                                    <div class="uk-inline">
                                                        <span class="uk-form-icon" uk-icon="icon: list"></span>
                                                        <input type="text" size="70" name="confirm_order_banner"
                                                               class="uk-input"
                                                               placeholder="Баннер подтверждение заказа"
                                                               uk-tooltip="Баннер подтверждение заказа"
                                                               value="<?php if (!empty($settings['confirm_order_banner'])) echo $settings['confirm_order_banner'] ?>"
                                                        />
                                                    </div>
                                                </div>
                                                <input type="hidden" name="accountId" value="<?php echo $accountId ?>"/>
                                                <input type="submit" class="uk-button uk-button-primary">
                                            </form>
                                        </li>
                                        <li>
                                            <h5>Как пользоваться приложением</h5>
                                            <div>
                                                <ul uk-accordion>
                                                    <li class="uk-open">

                                                        <iframe width="560" height="315"
                                                                src="https://www.youtube.com/embed/u4QpxqIM6g4"
                                                                frameborder="0"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                allowfullscreen></iframe>
                                                        <p></p>
                                                        <p></p>
                                                        <a class="uk-accordion-title" href="#"><span
                                                                    class="uk-text-bolder">Шаг 1.</span> Создать и
                                                            добавить
                                                            телеграм бота</a>
                                                        <div class="uk-accordion-content">
                                                            <p class="uk-text-light"><a href="https://t.me/botfather">BotFather</a>
                                                                -
                                                                единственный бот, который правит ими всеми. Это поможет
                                                                вам создать новых ботов и изменить настройки
                                                                существующих.</p>
                                                            <p class="uk-text-light"> Используйте команду
                                                                <code>/newbot</code>, чтобы создать
                                                                нового бота.
                                                                BotFather запросит у вас имя и имя пользователя, а затем
                                                                сгенерирует токен авторизации для вашего нового бота.
                                                            </p>
                                                            <p class="uk-text-light">
                                                                Имя вашего бота отображается в контактных данных и в
                                                                других местах.
                                                            </p>
                                                            <p class="uk-text-light">
                                                                Имя пользователя - это короткое имя, которое будет
                                                                использоваться в упоминаниях и ссылках на t.me. Имена
                                                                пользователей состоят из 5-32 символов и нечувствительны
                                                                к регистру, но могут включать только латинские символы,
                                                                числа и символы подчеркивания. Имя пользователя вашего
                                                                бота должно заканчиваться на «бот», например tetris_bot
                                                                или TetrisBot.
                                                            </p>
                                                            <p class="uk-text-light">
                                                                Токен представляет собой строку типа
                                                                <code>110201543:
                                                                    AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw</code>
                                                                , которая требуется
                                                                для авторизации бота и отправки запросов в API бота.
                                                                Держите свой токен в безопасности и храните его, он
                                                                может быть использован кем угодно для управления вашим
                                                                ботом.</p>
                                                            <p class="uk-text-light">Скопируйте токен и вставьте в
                                                                настройках приложения
                                                                МойСклад</p>
                                                            <p class="uk-text-light">После сохранения токена появится
                                                                кнопка активации бота. Нажмите ее, чтобы мы могли
                                                                обрабатывать
                                                                сообщения с бота.
                                                            </p>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <a class="uk-accordion-title" href="#"><span
                                                                    class="uk-text-bolder">Шаг 2.</span>
                                                            Синхронизировать категории и товары</a>
                                                        <div class="uk-accordion-content">
                                                            <p class="uk-text-light">По умолчанию, в телеграм боте для
                                                                каждого пункта меню
                                                                выводится по 8 товаров.
                                                                Изменить это значение можно во вкладке товары, в
                                                                настройках этого приложения.
                                                                Также вы там можете изменить или добавть <span
                                                                        class="uk-text-bolder">тип цены</span>,
                                                                изображение по умолчанию(если у товара нет изображения,
                                                                будет использоваться это), добавить правило, по которому
                                                                будут отбираться товары из МойСклад</p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            Настройки доступны только администратору аккаунта
                            <?php
                        }
                        ?>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</div>

<!-- UIkit JS -->
<script src="https://cdn.jsdelivr.net/npm/uikit@3.5.5/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.5.5/dist/js/uikit-icons.min.js"></script>
<script src="app.min.js?<?= filemtime('app.min.js') ?>"></script>
</body>
</html>