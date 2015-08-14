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
        'product_count_setting' => 1,
        'sku_count_setting' => 1,
        'min_product_count_message' => 'Вы не можете оформить заказ т.к. количество товара "%s" в Вашей корзине меньше минимального. Минимальное количество товара %s шт.',
        'product_multiplicity_setting' => 1,
        'sku_multiplicity_setting' => 1,
        'multiplicity_product_message' => 'Количество товара "%s" в Вашей корзине должно быть кратно %s шт.',
        'category_sum_setting' => 1,
        'min_order_sum_category_message' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа для категории "%s" меньше минимальной. Минимальная сумма заказа %s',
        'category_count_setting' => 1,
        'min_order_count_category_message' => 'Вы не можете оформить заказ т.к. количество товаров для категории "%s" в Вашей корзине меньше минимального. Минимальное количество товаров %s шт.',
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

    public function backendProduct($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('product', $product);
            $html = $view->fetch('plugins/wholesale/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $html = '<div class="field">
                        <div class="name">Минимальное количество товара для заказа</div>
                        <div class="value no-shift">
                            <input type="text" name="product[min_product_count]" value="' . $product->min_product_count . '" class="bold numerical small">
                        </div>
                    </div>
                    <div class="field">
                        <div class="name">Кратность заказываемого товара</div>
                        <div class="value no-shift">
                            <input type="text" name="product[multiplicity]" value="' . $product->multiplicity . '" class="bold numerical small">
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

    public function frontendCheckout($param) {
        $domain_settings = shopWholesale::getDomainSettings();
        if (!$this->getSettings('status') || !$domain_settings['status']) {
            return false;
        }

        $cart = new shopCart();
        $result = shopWholesale::checkOrder();
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
                $total = $cart->total(false);
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

        $data = shopWholesale::checkOrder();

        $view = wa()->getView();
        $view->assign('wholesale', $data);
        return $view->fetch($templates['cart']['template_path']);
    }

}
