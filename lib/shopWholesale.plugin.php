<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'wholesale');
    public static $default_settings = array(
        'status' => 1,
        'min_order_sum' => 0,
        'min_order_sum_message' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа меньше минимальной. Минимальная сумма заказа %s',
        'min_order_products' => 0,
        'min_order_products_message' => 'Вы не можете оформить заказ т.к. количество товаров в Вашей корзине меньше минимального. Минимальное количество товаров %s шт.',
        'product_count_setting' => 1,
        'min_product_count_message' => 'Вы не можете оформить заказ т.к. количество товара "%s" в Вашей корзине меньше минимального. Минимальное количество товара %s шт.',
        'default_output' => 1,
        'template' => '',
        'change_tpl' => 0
    );

    public function backendProductEdit($product) {
        $html = '<div class="field">
                    <div class="name">Минимальное количество товара для заказа</div>
                    <div class="value no-shift">
                        <input type="text" name="product[min_product_count]" value="' . $product->min_product_count . '" class="bold numerical small">
                    </div>
                </div>';
        return array('basics' => $html);
    }

    public function frontendCheckout($param) {
        $domain_settings = shopWholesale::getDomainSettings();
        if ($this->getSettings('status') && $domain_settings['status']) {
            $cart = new shopCart();
            $result = self::checkOrder();
            if (!$result['result'] && $param['step'] != 'success') {
                $cart_url = wa()->getRouteUrl('shop/frontend/cart');
                wa()->getResponse()->redirect($cart_url);
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

        if (!$domain_settings['status']) {
            return false;
        }

        if (!$domain_settings['change_tpl']) {
            $template_path = wa()->getAppPath('plugins/wholesale/templates/FrontendCart.html', 'shop');
        } else {
            $route_hash = shopWholesale::getRouteHash();
            $template_path = wa()->getCachePath('plugins/wholesale/' . $route_hash . '.html');
            if (!file_exists($tpl_path)) {
                file_put_contents($tpl_path, $domain_settings['template']);
            }
        }

        $data = self::checkOrder();

        $view = wa()->getView();
        $view->assign('wholesale', $data);
        return $view->fetch($template_path);
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
        } else {
            $return['result'] = 1;
            $return['message'] = '';
        }
        return $return;
    }

}
