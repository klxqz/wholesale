<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'wholesale');
    public static $default_settings = array(
        'status' => 1,
        'redirect' => 0,
        'min_order_sum' => 0,
        'min_order_sum_message' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа меньше минимальной. Минимальная сумма заказа %s',
        'min_order_products' => 0,
        'min_order_products_message' => 'Вы не можете оформить заказ т.к. количество товаров в Вашей корзине меньше минимального. Минимальное количество товаров %s шт.',
        'min_order_sum_category_message' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа для категории "%s" меньше минимальной. Минимальная сумма заказа %s',
        'min_order_count_category_message' => 'Вы не можете оформить заказ т.к. количество товаров для категории "%s" в Вашей корзине меньше минимального. Минимальное количество товаров %s шт.',
        'product_count_setting' => 1,
        'min_product_count_message' => 'Вы не можете оформить заказ т.к. количество товара "%s" в Вашей корзине меньше минимального. Минимальное количество товара %s шт.',
        'default_output' => 1,
        'plugins' => array(),
        'shipping_message' => 'Вы не можете воспользоваться выбранным способом доставки т.к. сумма Вашего заказа меньше минимальной. Минимальная сумма заказа для данного способа доставки %s Попробуйте выбрать другой способ доставки или увеличить сумму заказа.',
        'templates' => array(
            'cart' => array(
                'name' => 'Шаблон для страницы корзины',
                'tpl_path' => 'plugins/wholesale/templates/',
                'tpl_name' => 'FrontendCart',
                'tpl_ext' => 'html',
                'public' => false
            ),
            'shipping' => array(
                'name' => 'Шаблон для страницы доставки',
                'tpl_path' => 'plugins/wholesale/templates/',
                'tpl_name' => 'Shipping',
                'tpl_ext' => 'html',
                'public' => false
            )
        )
    );

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $html = '<div class="field">
                        <div class="name">Минимальное количество товара для заказа</div>
                        <div class="value no-shift">
                            <input type="text" name="product[min_product_count]" value="' . $product->min_product_count . '" class="bold numerical small">
                        </div>
                    </div>';
            return array('basics' => $html);
        }
    }

    public function backendCategoryDialog($category) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('category', $category);
            $template_path = wa()->getAppPath('plugins/wholesale/templates/CategoryField.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public function checkShipping($shipping_id) {
        $return = array();
        $domain_settings = shopWholesale::getDomainSettings();
        $plugins = $domain_settings['plugins'];

        $cart = new shopCart();
        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $cur_currency = wa('shop')->getConfig()->getCurrency(false);
        $total = $cart->total(true);
        $total = shop_currency($total, $cur_currency, $def_currency, false);

        if (!empty($plugins[$shipping_id]) && $total < $plugins[$shipping_id]) {
            $message = sprintf($domain_settings['shipping_message'], shop_currency($plugins[$shipping_id]));
            $return = array('result' => 0, 'message' => $message);
        } else {
            $return = array('result' => 1, 'message' => '');
        }

        return $return;
    }

    public function frontendCheckout($param) {
        $domain_settings = shopWholesale::getDomainSettings();
        if (!$this->getSettings('status') || !$domain_settings['status']) {
            return false;
        }

        $cart = new shopCart();
        $result = self::checkOrder();
        if (!$result['result'] && $param['step'] != 'success' && $domain_settings['redirect']) {
            $cart_url = wa()->getRouteUrl('shop/frontend/cart');
            wa()->getResponse()->redirect($cart_url);
        }


        $data = wa()->getStorage()->get('shop/checkout');
        $plugins = $domain_settings['plugins'];
        $templates = $domain_settings['templates'];

        if (!empty($data['shipping']['id'])) {
            $shipping_id = $data['shipping']['id'];
            if (!empty($plugins[$shipping_id])) {
                $cart = new shopCart();
                $def_currency = wa('shop')->getConfig()->getCurrency(true);
                $cur_currency = wa('shop')->getConfig()->getCurrency(false);
                $total = $cart->total(true);
                $total = shop_currency($total, $cur_currency, $def_currency, false);

                if ($total < $plugins[$shipping_id]) {
                    $steps = array_keys(wa()->getConfig()->getCheckoutSettings());
                    $current_step_key = array_search($param['step'], $steps);
                    $shipping_step_key = array_search('shipping', $steps);


                    if ($param['step'] == 'shipping') {
                        $message = sprintf($domain_settings['shipping_message'], shop_currency($plugins[$shipping_id]));

                        $view = wa()->getView();
                        $view->assign('wholesale', array('result' => 0, 'message' => $message));
                        return $view->fetch($templates['shipping']['template_path']);
                    } elseif ($current_step_key > $shipping_step_key && $domain_settings['redirect']) {
                        $shipping_url = wa()->getRouteUrl('shop/frontend/checkout', array('step' => 'shipping'));
                        wa()->getResponse()->redirect($shipping_url);
                    }
                }
            }
        }
    }

    public function frontendCart() {
        $domain_settings = shopWholesale::getDomainSettings();
        if ($domain_settings['default_output']) {
            return self::display();
        }
    }

    public static function display() {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(self::$plugin_id, 'status')) {
            return false;
        }

        $domain_settings = shopWholesale::getDomainSettings();
        $templates = $domain_settings['templates'];

        if (!$domain_settings['status']) {
            return false;
        }

        $data = self::checkOrder();

        $view = wa()->getView();
        $view->assign('wholesale', $data);
        return $view->fetch($templates['cart']['template_path']);
    }

    public static function checkMinProductCount(&$product_name = null, &$min_product_count = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product' && $item['quantity'] < $item['product']['min_product_count']) {
                $product_name = $item['product']['name'];
                $min_product_count = $item['product']['min_product_count'];
                return false;
            }
        }
        return true;
    }

    public static function getCategoryMinSum($category_id, &$category_name) {

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        if ($category['min_sum'] > 0) {
            $category_name = $category['name'];
            return $category['min_sum'];
        } elseif ($category['parent_id']) {
            return self::getCategoryMinSum($category['parent_id'], $category_name);
        }
        return 0;
    }

    protected static function inCategory($category, $product) {

        if ($product['category_id'] == $category['id']) {
            return true;
        } elseif ($category['parent_id']) {
            return inCategory($category, $product);
        }
        return false;
    }

    protected static function getCategoryProductsSum($category) {
        $category_model = new shopCategoryModel();
        $sum = 0;
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            $category = $category_model->getById($item['product']['category_id']);
            if ($item['type'] == 'product' && self::inCategory($category, $item['product'])) {
                $sum += $item['price'] * $item['quantity'];
            }
        }
        return $sum;
    }

    public static function checkMinCategorySum(&$category_name = null, &$min_category_sum = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $category_min_sum = self::getCategoryMinSum($item['product']['category_id'], $category_name);
                $category_sum = self::getCategoryProductsSum($item['product']['category_id']);
                if ($category_sum < $category_min_sum) {
                    $min_category_sum = $category_min_sum;
                    return false;
                }
            }
        }

        return true;
    }

    public static function getCategoryMinCount($category_id, &$category_name) {

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        if ($category['min_product_count'] > 0) {
            $category_name = $category['name'];
            return $category['min_product_count'];
        } elseif ($category['parent_id']) {
            return self::getCategoryMinCount($category['parent_id'], $category_name);
        }
        return 0;
    }

    protected static function getCategoryProductsCount($category) {
        $category_model = new shopCategoryModel();
        $count = 0;
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            $category = $category_model->getById($item['product']['category_id']);
            if ($item['type'] == 'product' && self::inCategory($category, $item['product'])) {
                $count += $item['quantity'];
            }
        }
        return $count;
    }

    public static function checkMinCategoryCount(&$category_name = null, &$min_category_count = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $category_min_count = self::getCategoryMinCount($item['product']['category_id'], $category_name);
                $category_count = self::getCategoryProductsCount($item['product']['category_id']);
                if ($category_count < $category_min_count) {
                    $min_category_count = $category_min_count;
                    return false;
                }
            }
        }
        return true;
    }

    public static function checkOrder() {
        $return = array();
        $domain_settings = shopWholesale::getDomainSettings();

        $cart = new shopCart();
        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $cur_currency = wa('shop')->getConfig()->getCurrency(false);

        $total = $cart->total(true);
        $total = shop_currency($total, $cur_currency, $def_currency, false);
        $min_order_sum = $domain_settings['min_order_sum'];
        $min_order_sum_format = shop_currency($min_order_sum);

        if ($total < $min_order_sum) {
            $return['result'] = 0;
            $return['message'] = sprintf($domain_settings['min_order_sum_message'], $min_order_sum_format);
        } elseif ($cart->count() < $domain_settings['min_order_products']) {
            $return['result'] = 0;
            $return['message'] = sprintf($domain_settings['min_order_products_message'], $domain_settings['min_order_products']);
        } elseif ($domain_settings['product_count_setting'] && !self::checkMinProductCount($product_name, $min_product_count)) {
            $return['result'] = 0;
            $return['message'] = sprintf($domain_settings['min_product_count_message'], $product_name, $min_product_count);
        } elseif (!self::checkMinCategorySum($category_name, $min_category_sum)) {
            $return['result'] = 0;
            $return['message'] = sprintf($domain_settings['min_order_sum_category_message'], $category_name, shop_currency($min_category_sum));
        } elseif (!self::checkMinCategoryCount($category_name, $min_category_count)) {
            $return['result'] = 0;
            $return['message'] = sprintf($domain_settings['min_order_count_category_message'], $category_name, $min_category_count);
        } else {
            $return['result'] = 1;
            $return['message'] = '';
        }
        return $return;
    }

}
