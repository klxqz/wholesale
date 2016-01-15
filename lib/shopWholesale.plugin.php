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
        'auto_add_product_count_setting' => 0,
        'sku_count_setting' => 1,
        'auto_add_sku_count_setting' => 0,
        'min_product_count_message' => 'Вы не можете оформить заказ т.к. количество товара "%s" в Вашей корзине меньше минимального. Минимальное количество товара %s шт.',
        'product_multiplicity_setting' => 1,
        'auto_add_product_multiplicity_setting' => 0,
        'sku_multiplicity_setting' => 1,
        'auto_add_sku_multiplicity_setting' => 0,
        'multiplicity_product_message' => 'Количество товара "%s" в Вашей корзине должно быть кратно %s шт.',
        'category_sum_setting' => 1,
        'min_order_sum_category_message' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа для категории "%s" меньше минимальной. Минимальная сумма заказа %s',
        'category_count_setting' => 1,
        'min_order_count_category_message' => 'Вы не можете оформить заказ т.к. количество товаров для категории "%s" в Вашей корзине меньше минимального. Минимальное количество товаров %s шт.',
        'default_output' => 1,
        'plugins' => array(),
        'shipping_message' => 'Вы не можете воспользоваться выбранным способом доставки т.к. сумма Вашего заказа меньше минимальной. Минимальная сумма заказа для данного способа доставки %s Попробуйте выбрать другой способ доставки или увеличить сумму заказа.',
        'frontend_product' => 1,
        'frontend_product_output' => 1,
        'product_cart_form_selector' => 'form#cart-form',
        'product_add2cart_selector' => 'input[type=submit]',
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
            ),
            'product' => array(
                'name' => 'Шаблон для страницы товара',
                'tpl_path' => 'plugins/wholesale/templates/',
                'tpl_name' => 'FrontendProduct',
                'tpl_ext' => 'html',
                'public' => false
            ),
        )
    );

    public function backendProductSkuSettings($params) {
        if ($this->getSettings('status')) {
            $sku = $params['sku'];
            $view = wa()->getView();
            $view->assign('sku', $sku);
            $html = $view->fetch('plugins/wholesale/templates/BackendProductSkuSettings.html');
            return $html;
        }
    }

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $html = '<div class="field">
                        <div class="name">Минимальное количество товара для заказа</div>
                        <div class="value no-shift">
                            <input type="text" name="product[wholesale_min_product_count]" value="' . $product->wholesale_min_product_count . '" class="bold numerical small">
                        </div>
                    </div>
                    <div class="field">
                        <div class="name">Кратность заказываемого товара</div>
                        <div class="value no-shift">
                            <input type="text" name="product[wholesale_multiplicity]" value="' . $product->wholesale_multiplicity . '" class="bold numerical small">
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

        if ($param['step'] == 'shipping') {
            $view = wa()->getView();
            $view->assign('wholesale', array('result' => 1, 'message' => ''));
            return $view->fetch($templates['shipping']['template_path']);
        }
    }

    private function setQuantity($item_id, $quantity) {
        $cart = new shopCart();
        $cart->setQuantity($item_id, $quantity);
        $url = wa()->getConfig()->getCurrentUrl();
        wa()->getResponse()->redirect($url);
    }

    public function frontendCart() {
        if ($this->getSettings('status')) {
            $domain_settings = shopWholesale::getDomainSettings();
            if (!$domain_settings['status']) {
                return false;
            }
            if ($domain_settings['product_count_setting'] && !shopWholesale::checkMinProductsCartCount($product_name, $min_product_count, $item) && $domain_settings['auto_add_product_count_setting']) {
                if ($item) {
                    $this->setQuantity($item['id'], $min_product_count);
                }
            }

            if ($domain_settings['sku_count_setting'] && !shopWholesale::checkMinSkusCartCount($product_name, $min_sku_count, $item) && $domain_settings['auto_add_sku_count_setting']) {
                if ($item) {
                    $this->setQuantity($item['id'], $min_sku_count);
                }
            }
            if ($domain_settings['product_multiplicity_setting'] && !shopWholesale::checkMultiplicityProductsCartCount($product_name, $multiplicity_product_count, $item) && $domain_settings['auto_add_product_multiplicity_setting']) {
                if ($item) {
                    $k = ceil($item['quantity'] / $multiplicity_product_count);
                    $quantity = $k * $multiplicity_product_count;
                    $this->setQuantity($item['id'], $quantity);
                }
            }
            if ($domain_settings['sku_multiplicity_setting'] && !shopWholesale::checkMultiplicitySkusCartCount($product_name, $multiplicity_sku_count, $item) && $domain_settings['auto_add_sku_multiplicity_setting']) {
                if ($item) {
                    $k = ceil($item['quantity'] / $multiplicity_sku_count);
                    $quantity = $k * $multiplicity_sku_count;
                    $this->setQuantity($item['id'], $quantity);
                }
            }


            if ($domain_settings['default_output']) {
                return self::display();
            }
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

    public function frontendProduct($product) {
        $domain_settings = shopWholesale::getDomainSettings();
        if (!empty($domain_settings['frontend_product_output'])) {
            return array('cart' => self::displayFrontendProduct());
        }
    }

    public static function displayFrontendProduct() {
        $app_settings_model = new waAppSettingsModel();
        if (!$app_settings_model->get(self::$plugin_id, 'status')) {
            return false;
        }
        $domain_settings = shopWholesale::getDomainSettings();
        $templates = $domain_settings['templates'];
        if (!$domain_settings['status']) {
            return false;
        }
        if (!$domain_settings['frontend_product']) {
            return false;
        }
        $view = wa()->getView();
        $view->assign('settings', $domain_settings);
        return $view->fetch($templates['product']['template_path']);
    }

}
