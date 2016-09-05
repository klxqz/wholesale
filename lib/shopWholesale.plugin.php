<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePlugin extends shopPlugin {

    public static $templates = array(
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
    );

    public function saveSettings($settings = array()) {
        $route_hash = waRequest::post('route_hash');
        $route_settings = waRequest::post('route_settings');

        if ($routes = $this->getSettings('routes')) {
            $settings['routes'] = $routes;
        } else {
            $settings['routes'] = array();
        }
        $settings['routes'][$route_hash] = $route_settings;
        $settings['route_hash'] = $route_hash;
        parent::saveSettings($settings);


        $templates = waRequest::post('templates');
        foreach ($templates as $template_id => $template) {
            $s_template = self::$templates[$template_id];
            if (!empty($template['reset_tpl'])) {
                $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                @unlink($template_path);
            } else {
                $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                if (!file_exists($template_path)) {
                    $tpl_full_path = $s_template['tpl_path'] . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                    $template_path = wa()->getAppPath($tpl_full_path, 'shop');
                }
                $content = file_get_contents($template_path);
                if (!empty($template['template']) && $template['template'] != $content) {
                    $tpl_full_path = $s_template['tpl_path'] . $route_hash . '.' . $s_template['tpl_name'] . '.' . $s_template['tpl_ext'];
                    $template_path = wa()->getDataPath($tpl_full_path, $s_template['public'], 'shop', true);
                    $f = fopen($template_path, 'w');
                    if (!$f) {
                        throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                    }
                    fwrite($f, $template['template']);
                    fclose($f);
                }
            }
        }
    }

    public function backendProductSkuSettings($params) {
        if ($this->getSettings('status')) {
            $sku = $params['sku'];
            $view = wa()->getView();
            $view->assign('sku', $sku);
            $view->assign('sku_id', $params['sku_id']);
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
        $plugin = wa()->getPlugin('wholesale');
        if (!$plugin->getSettings('status')) {
            return false;
        }
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return false;
        }

        $cart = new shopCart();
        $result = shopWholesale::checkOrder();
        if (!$result['result'] && $param['step'] != 'success' && $route_settings['redirect']) {
            $cart_url = wa()->getRouteUrl('shop/frontend/cart');
            wa()->getResponse()->redirect($cart_url);
        }

        $data = wa()->getStorage()->get('shop/checkout');
        $plugins = $route_settings['plugins'];
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
                    if ($current_step_key > $shipping_step_key && $route_settings['redirect']) {
                        $shipping_url = wa()->getRouteUrl('shop/frontend/checkout', array('step' => 'shipping'));
                        wa()->getResponse()->redirect($shipping_url);
                    }
                }
            }
        }

        if ($param['step'] == 'shipping') {
            $view = wa()->getView();
            $shipping_template = shopWholesaleHelper::getRouteTemplates($route_hash, 'shipping');
            $view->assign('settings', $route_settings);
            return $view->fetch('string:' . $shipping_template['template']);
        }
    }

    private function setQuantity($item_id, $quantity) {
        $cart = new shopCart();
        $cart->setQuantity($item_id, $quantity);
        $url = wa()->getConfig()->getCurrentUrl();
        wa()->getResponse()->redirect($url);
    }

    public function frontendCart() {
        if (!$this->getSettings('status')) {
            return false;
        }
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return false;
        }

        if ($route_settings['product_count_setting'] && !shopWholesale::checkMinProductsCartCount($product_name, $min_product_count, $item) && $route_settings['auto_add_product_count_setting']) {
            if ($item) {
                $this->setQuantity($item['id'], $min_product_count);
            }
        }

        if ($route_settings['sku_count_setting'] && !shopWholesale::checkMinSkusCartCount($product_name, $min_sku_count, $item) && $route_settings['auto_add_sku_count_setting']) {
            if ($item) {
                $this->setQuantity($item['id'], $min_sku_count);
            }
        }
        if ($route_settings['product_multiplicity_setting'] && !shopWholesale::checkMultiplicityProductsCartCount($product_name, $multiplicity_product_count, $item) && $route_settings['auto_add_product_multiplicity_setting']) {
            if ($item) {
                $k = ceil($item['quantity'] / $multiplicity_product_count);
                $quantity = $k * $multiplicity_product_count;
                $this->setQuantity($item['id'], $quantity);
            }
        }
        if ($route_settings['sku_multiplicity_setting'] && !shopWholesale::checkMultiplicitySkusCartCount($product_name, $multiplicity_sku_count, $item) && $route_settings['auto_add_sku_multiplicity_setting']) {
            if ($item) {
                $k = ceil($item['quantity'] / $multiplicity_sku_count);
                $quantity = $k * $multiplicity_sku_count;
                $this->setQuantity($item['id'], $quantity);
            }
        }


        if ($route_settings['frontend_cart_output']) {
            return self::displayFrontendCart();
        }
    }

    public static function display() {
        return self::displayFrontendCart();
    }

    public static function displayFrontendCart() {
        $plugin = wa()->getPlugin('wholesale');
        if (!$plugin->getSettings('status')) {
            return false;
        }
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return false;
        }
        $template = shopWholesaleHelper::getRouteTemplates($route_hash, 'cart');
        $view = wa()->getView();
        $html = $view->fetch('string:' . $template['template']);
        return $html;
    }

    public function frontendProduct($product) {
        $plugin = wa()->getPlugin('wholesale');
        if (!$plugin->getSettings('status')) {
            return false;
        }
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return false;
        }

        if (!empty($route_settings['frontend_product_output'])) {
            return array('cart' => self::displayFrontendProduct());
        }
    }

    public static function displayFrontendProduct() {
        $plugin = wa()->getPlugin('wholesale');
        if (!$plugin->getSettings('status')) {
            return false;
        }
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return false;
        }

        if (empty($route_settings['frontend_product'])) {
            return false;
        }
        $template = shopWholesaleHelper::getRouteTemplates($route_hash, 'product');
        $view = wa()->getView();
        $view->assign('settings', $route_settings);
        return $view->fetch('string:' . $template['template']);
    }

}
