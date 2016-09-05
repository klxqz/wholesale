<?php

class shopWholesale {
    /*
      public static function getRouteHash() {
      $domain = wa()->getRouting()->getDomain(null, true);
      $route = wa()->getRouting()->getRoute();
      return md5($domain . '/' . $route['url']);
      }
     */
    /*
      public static function getDomainsSettings() {

      $cache = new waSerializeCache('shopWholesalePlugin');

      if ($cache && $cache->isCached()) {
      $domains_settings = $cache->get();
      } else {
      $app_settings_model = new waAppSettingsModel();
      $routing = wa()->getRouting();
      $domains_routes = $routing->getByApp('shop');
      $app_settings_model->get(shopWholesalePlugin::$plugin_id, 'domains_settings');
      $domains_settings = json_decode($app_settings_model->get(shopWholesalePlugin::$plugin_id, 'domains_settings'), true);

      if (empty($domains_settings)) {
      $domains_settings = array();
      }

      foreach ($domains_routes as $domain => $routes) {
      foreach ($routes as $route) {
      $domain_route = md5($domain . '/' . $route['url']);
      if (empty($domains_settings[$domain_route])) {
      $domains_settings[$domain_route] = shopWholesalePlugin::$default_settings;
      }
      foreach (shopWholesalePlugin::$default_settings as $key => $value) {
      if (!isset($domains_settings[$domain_route][$key])) {
      $domains_settings[$domain_route][$key] = $value;
      }
      }

      foreach (shopWholesalePlugin::$default_settings['templates'] as $tpl_name => $tpl) {
      $domains_settings[$domain_route]['templates'][$tpl_name] = $tpl;

      $tpl_full_path = $tpl['tpl_path'] . $domain_route . '_' . $tpl['tpl_name'] . '.' . $tpl['tpl_ext'];
      $domains_settings[$domain_route]['templates'][$tpl_name]['tpl_full_path'] = $tpl_full_path;
      $template_path = wa()->getDataPath($tpl_full_path, $tpl['public'], 'shop', true);


      if (file_exists($template_path)) {
      $domains_settings[$domain_route]['templates'][$tpl_name]['template_path'] = $template_path;
      $domains_settings[$domain_route]['templates'][$tpl_name]['template'] = file_get_contents($template_path);
      $domains_settings[$domain_route]['templates'][$tpl_name]['change_tpl'] = 1;
      } else {
      $domains_settings[$domain_route]['templates'][$tpl_name]['tpl_full_path'] = $tpl['tpl_path'] . $tpl['tpl_name'] . '.' . $tpl['tpl_ext'];
      $template_path = wa()->getAppPath($tpl['tpl_path'] . $tpl['tpl_name'] . '.' . $tpl['tpl_ext'], 'shop');
      $domains_settings[$domain_route]['templates'][$tpl_name]['template_path'] = $template_path;
      $domains_settings[$domain_route]['templates'][$tpl_name]['template'] = file_get_contents($template_path);
      $domains_settings[$domain_route]['templates'][$tpl_name]['change_tpl'] = 0;
      }
      }
      }

      if ($domains_settings && $cache) {
      $cache->set($domains_settings);
      }
      }
      }

      return $domains_settings;
      }
     * 
     */
    /*
      public static function saveDomainsSettings($domains_settings) {


      $app_settings_model = new waAppSettingsModel();
      $routing = wa()->getRouting();
      $domains_routes = $routing->getByApp('shop');

      foreach ($domains_routes as $domain => $routes) {
      foreach ($routes as $route) {
      $domain_route = md5($domain . '/' . $route['url']);

      foreach (shopWholesalePlugin::$default_settings['templates'] as $id => $template) {
      $tpl_full_path = $template['tpl_path'] . $domain_route . '_' . $template['tpl_name'] . '.' . $template['tpl_ext'];
      $template_path = wa()->getDataPath($tpl_full_path, $template['public'], 'shop', true);

      @unlink($template_path);
      if (empty($domains_settings[$domain_route]['templates'][$id]['reset_tpl'])) {
      $source_path = wa()->getAppPath($template['tpl_path'] . $template['tpl_name'] . '.' . $template['tpl_ext'], 'shop');
      $source_content = file_get_contents($source_path);


      if (!isset($domains_settings[$domain_route]['templates'][$id]['template'])) {
      continue;
      }

      $post_template = $domains_settings[$domain_route]['templates'][$id]['template'];

      if ($source_content != $post_template) {
      $f = fopen($template_path, 'w');
      if (!$f) {
      throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
      }
      fwrite($f, $post_template);
      fclose($f);
      }
      }
      }
      unset($domains_settings[$domain_route]['templates']);
      }
      }

      $app_settings_model->set(shopWholesalePlugin::$plugin_id, 'domains_settings', json_encode($domains_settings));
      $cache = new waSerializeCache('shopWholesalePlugin');
      if ($cache && $cache->isCached()) {
      $cache->delete();
      }
      }
     */
    /*
      public static function getDomainSettings() {
      $domains_settings = self::getDomainsSettings();
      $hash = self::getRouteHash();

      $domain_settings = array();
      if (!empty($domains_settings[$hash])) {
      $domain_settings = $domains_settings[$hash];
      } else {
      $domain_settings = shopWholesalePlugin::$default_settings;
      foreach ($domain_settings['templates'] as $tpl_name => $tpl) {
      $domain_settings['templates'][$tpl_name] = $tpl;

      $domain_settings['templates'][$tpl_name]['tpl_full_path'] = $tpl['tpl_path'] . $tpl['tpl_name'] . '.' . $tpl['tpl_ext'];
      $template_path = wa()->getAppPath($tpl['tpl_path'] . $tpl['tpl_name'] . '.' . $tpl['tpl_ext'], 'shop');
      $domain_settings['templates'][$tpl_name]['template_path'] = $template_path;
      $domain_settings['templates'][$tpl_name]['template'] = file_get_contents($template_path);
      $domain_settings['templates'][$tpl_name]['change_tpl'] = 0;
      }
      }

      return $domain_settings;
      }
     */

    /**
     * Проверка текущего заказ на соответсвие минимальным требованиям.
     * Возвращает result = TRUE - если условия минимального заказа выполняются, FALSE - если условия не выполняются.
     * message - сообщение об ошибке.
     * 
     * @return array('result' => boolean, 'message' => string)
     */
    public static function checkOrder() {
        $return = array(
            'result' => true,
            'message' => '',
        );

        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return $return;
        }

        $cart = new shopCart();
        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $cur_currency = wa('shop')->getConfig()->getCurrency(false);

        $total = $cart->total(true);
        $total = shop_currency($total, $cur_currency, $def_currency, false);
        $min_order_sum = $route_settings['min_order_sum'];
        $min_order_sum_format = shop_currency($min_order_sum);

        if ($total < $min_order_sum) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_order_sum_message'], $min_order_sum_format);
        } elseif ($cart->count() < $route_settings['min_order_products']) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_order_products_message'], $route_settings['min_order_products']);
        } elseif ($route_settings['product_count_setting'] && !self::checkMinProductsCartCount($product_name, $min_product_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_product_count_message'], $product_name, $min_product_count);
        } elseif ($route_settings['product_multiplicity_setting'] && !self::checkMultiplicityProductsCartCount($product_name, $multiplicity_product_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['multiplicity_product_message'], $product_name, $multiplicity_product_count);
        } elseif ($route_settings['category_sum_setting'] && !self::checkMinCategorySum($category_name, $min_category_sum)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_order_sum_category_message'], $category_name, shop_currency($min_category_sum));
        } elseif ($route_settings['category_count_setting'] && !self::checkMinCategoryCount($category_name, $min_category_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_order_count_category_message'], $category_name, $min_category_count);
        } elseif ($route_settings['sku_count_setting'] && !self::checkMinSkusCartCount($product_name, $min_sku_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_product_count_message'], $product_name, $min_sku_count);
        } elseif ($route_settings['sku_multiplicity_setting'] && !self::checkMultiplicitySkusCartCount($product_name, $multiplicity_sku_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['multiplicity_product_message'], $product_name, $multiplicity_sku_count);
        }
        return $return;
    }

    /**
     * Проверка минимальной суммы заказа для способа доставки с $shipping_id
     * Возвращает result = TRUE - если условия минимальной суммы выполняются, FALSE - если условия не выполняются.
     * message - сообщение об ошибке.
     * 
     * @param int $shipping_id
     * @return array('result' => boolean, 'message' => string)
     */
    public static function checkShipping($shipping_id) {
        $return = array(
            'result' => true,
            'message' => '',
        );

        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return $return;
        }

        $plugins = $route_settings['plugins'];

        $cart = new shopCart();
        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $cur_currency = wa('shop')->getConfig()->getCurrency(false);
        $total = $cart->total(true);
        $total = shop_currency($total, $cur_currency, $def_currency, false);

        if (!empty($plugins[$shipping_id]) && $total < $plugins[$shipping_id]) {
            $message = sprintf($route_settings['shipping_message'], shop_currency($plugins[$shipping_id]));
            $return = array('result' => 0, 'message' => $message);
        }

        return $return;
    }

    /**
     * 
     */
    public static function checkProduct($product_id, $sku_id = null, $quantity = null, $old_quantity = null) {
        if (!$quantity) {
            $quantity = 1;
        }
        $return = array(
            'result' => true,
            'message' => '',
            'quantity' => $quantity,
        );
        $route_hash = null;
        if (shopWholesaleHelper::getRouteSettings(null, 'status')) {
            $route_hash = null;
            $route_settings = shopWholesaleHelper::getRouteSettings();
        } elseif (shopWholesaleHelper::getRouteSettings(0, 'status')) {
            $route_hash = 0;
            $route_settings = shopWholesaleHelper::getRouteSettings(0);
        } else {
            return $return;
        }

        $product_model = new shopProductModel();
        $product = $product_model->getById($product_id);
        if ($sku_id) {
            $sku_model = new shopProductSkusModel();
            $sku = $sku_model->getById($sku_id);
        }

        if ($route_settings['product_count_setting'] && !self::checkMinProductCount($product, $quantity, $product_name, $min_product_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_product_count_message'], $product_name, $min_product_count);
            $return['quantity'] = $min_product_count;
        } elseif ($route_settings['product_multiplicity_setting'] && !self::checkMultiplicityProductCount($product, $quantity, $product_name, $multiplicity_product_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['multiplicity_product_message'], $product_name, $multiplicity_product_count);
            if ($old_quantity < $quantity) {
                $k = ceil($quantity / $multiplicity_product_count);
                $set_quantity = $k * $multiplicity_product_count;
            } else {
                $k = floor($quantity / $multiplicity_product_count);
                $set_quantity = $k * $multiplicity_product_count;
            }
            if ($set_quantity == 0) {
                $set_quantity = $multiplicity_product_count;
            }
            $return['quantity'] = $set_quantity;
        } elseif ($route_settings['sku_count_setting'] && !empty($sku) && !self::checkMinSkuCount($sku, $quantity, $product_name, $min_sku_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['min_product_count_message'], $product_name, $min_sku_count);
            $return['quantity'] = $min_sku_count;
        } elseif ($route_settings['sku_multiplicity_setting'] && !self::checkMultiplicitySkuCount($sku, $quantity, $product_name, $multiplicity_sku_count)) {
            $return['result'] = false;
            $return['message'] = sprintf($route_settings['multiplicity_product_message'], $product_name, $multiplicity_sku_count);
            if ($old_quantity < $quantity) {
                $k = ceil($quantity / $multiplicity_sku_count);
                $set_quantity = $k * $multiplicity_sku_count;
            } else {
                $k = floor($quantity / $multiplicity_sku_count);
                $set_quantity = $k * $multiplicity_sku_count;
            }
            if ($set_quantity == 0) {
                $set_quantity = $multiplicity_sku_count;
            }
            $return['quantity'] = $set_quantity;
        }

        return $return;
    }

    /**
     * Проверка наличия в корзине минимального количества товара для категории
     * Возвращает TRUE - если условия минимального количества выполняются, FALSE - если условия не выполняются.
     * 
     * @param string $category_name - в эту переменную записывается имя категории, для которой условия минимального количества не выполняются.
     * @param int $min_category_count - в эту переменную записывается минимальное количество товара для категории.
     * @return boolean
     */
    public static function checkMinCategoryCount(&$category_name = null, &$min_category_count = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $product = new shopProduct($item['product']['id']);
                foreach ($product->categories as $category) {
                    $category_min_count = self::getCategoryMinCount($category['id'], $category_name);
                    $category_count = self::getCategoryProductsCount($category['id']);
                    if ($category_count < $category_min_count) {
                        $min_category_count = $category_min_count;
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Возвращает количество товара добавленного в корзину для заданной категории и ее подкатегорий
     * @param array $category_id
     * @return int
     */
    protected static function getCategoryProductsCount($category_id) {
        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        $count = 0;
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product' && self::inCategory($category, $item['product'])) {
                $count += $item['quantity'];
            }
        }
        return $count;
    }

    /**
     * Возвращает минимальное количество товаров для заказа для указанной категории. 
     * Текущая категория может наслетовать количество минимального товара от родительской категории, поэтому функция вызывается рекурсивно.
     * 
     * @param int $category_id - идентификатор категории, для которой возвращается минимальное количество товаров для заказа. 
     * @param string $category_name - имя категории, для которой установлено ограничение минимального количества товаров.
     * @return int
     */
    public static function getCategoryMinCount($category_id, &$category_name) {

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        if ($category['wholesale_min_product_count'] > 0) {
            $category_name = $category['name'];
            return $category['wholesale_min_product_count'];
        } elseif ($category['parent_id']) {
            return self::getCategoryMinCount($category['parent_id'], $category_name);
        }
        return 0;
    }

    /**
     * Проверка минимальную сумму товаров для категории. 
     * Возвращает TRUE - если условия минимальной суммы выполняются, FALSE - если условия не выполняются.
     *  
     * @param string $category_name - в эту переменную записывается имя категории, для которой условия минимальной суммы не выполняются.
     * @param type $min_category_sum - в эту переменную записывается минимальная сумма заказа для категории.
     * @return boolean
     */
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

    /**
     * Возвращает сумму товаров добавленных в корзину для заданной категории и ее подкатегорий. 
     * @param array $category
     * @return float
     */
    protected static function getCategoryProductsSum($category_id) {
        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        $sum = 0;
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product' && self::inCategory($category, $item['product'])) {
                $sum += $item['price'] * $item['quantity'];
            }
        }
        return $sum;
    }

    /**
     * Возвращает минимальную сумму товаров для заказа для указанной категории. 
     * Текущая категория может наслетовать минимальную сумму товара от родительской категории, поэтому функция вызывается рекурсивно.
     * 
     * @param int $category_id - идентификатор категории, для которой возвращается минимальное количество товаров для заказа. 
     * @param string $category_name - имя категории, для которой установлено ограничение минимального количества товаров.
     * @return int
     */
    public static function getCategoryMinSum($category_id, &$category_name) {
        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);
        if ($category['wholesale_min_sum'] > 0) {
            $category_name = $category['name'];
            return $category['wholesale_min_sum'];
        } elseif ($category['parent_id']) {
            return self::getCategoryMinSum($category['parent_id'], $category_name);
        }
        return 0;
    }

    /**
     * Проверка наличия товара в указанной категории или подкатегориях.
     * 
     * @param type $category
     * @param type $product
     * @return boolean
     */
    protected static function inCategory($category, $product) {

        if ($product['category_id'] == $category['id']) {
            return true;
        } elseif ($category['parent_id']) {
            $category_model = new shopCategoryModel();
            $category = $category_model->getById($category['parent_id']);
            return self::inCategory($category, $product);
        }
        return false;
    }

    /**
     * Проверка минимального количества заказанных продуктов.
     * Возвращает TRUE - если условие минимального количества для товара выполняется, FALSE - если условие не выполняется.
     * 
     * @param type $product_name - в эту переменную записывается имя товара, для которого условие минимального количества не выполняется.
     * @param type $min_product_count - в эту переменную записывается минимальное количество товара.
     * @return boolean
     */
    public static function checkMinProductsCartCount(&$product_name = null, &$min_product_count = null, &$item = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product' && !self::checkMinProductCount($item['product'], $item['quantity'], $product_name, $min_product_count)) {
                return false;
            }
        }
        unset($item);
        return true;
    }

    public static function checkMinProductCount($product, $quantity, &$product_name = null, &$min_product_count = null) {
        if ($quantity < $product['wholesale_min_product_count']) {
            $product_name = $product['name'];
            $min_product_count = $product['wholesale_min_product_count'];
            return false;
        }
        return true;
    }

    /**
     * Проверка кратности количества заказанных продуктов.
     * Возвращает TRUE - если условие кратности количества для товара выполняется, FALSE - если условие не выполняется.
     * 
     * @param type $product_name - в эту переменную записывается имя товара, для которого условие кратности количества не выполняется.
     * @param type $multiplicity_product_count - в эту переменную записывается кратность товара.
     */
    public static function checkMultiplicityProductsCartCount(&$product_name = null, &$multiplicity_product_count = null, &$item = null) {
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product' && !self::checkMultiplicityProductCount($item['product'], $item['quantity'], $product_name, $multiplicity_product_count)) {
                return false;
            }
        }
        unset($item);
        return true;
    }

    public static function checkMultiplicityProductCount($product, $quantity, &$product_name = null, &$multiplicity_product_count = null) {
        if ($product['wholesale_multiplicity'] > 0 && $quantity % $product['wholesale_multiplicity'] != 0) {
            $product_name = $product['name'];
            $multiplicity_product_count = $product['wholesale_multiplicity'];
            return false;
        }
        return true;
    }

    /**
     * Проверка минимального количества заказанных продуктов для артикулов.
     * Возвращает TRUE - если условие минимального количества для товара выполняется, FALSE - если условие не выполняется.
     * 
     * @param type $product_name - в эту переменную записывается имя товара, для которого условие минимального количества не выполняется.
     * @param type $min_sku_count - в эту переменную записывается минимальное количество товара для выбранного артикула.
     * @return boolean
     */
    public static function checkMinSkusCartCount(&$product_name = null, &$min_sku_count = null, &$item = null) {
        $sku_model = new shopProductSkusModel();
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $sku = $sku_model->getById($item['sku_id']);
                if (!empty($sku) && !self::checkMinSkuCount($sku, $item['quantity'], $product_name, $min_sku_count)) {
                    return false;
                }
            }
        }
        unset($item);
        return true;
    }

    public static function checkMinSkuCount($sku, $quantity, &$product_name = null, &$min_sku_count = null) {
        if (!empty($sku) && $quantity < $sku['wholesale_min_sku_count']) {
            $product_model = new shopProductModel();
            $product = $product_model->getById($sku['product_id']);

            $product_name = $product['name'];
            if (!empty($sku['name'])) {
                $product_name .= " (" . $sku['name'] . ")";
            }
            $min_sku_count = $sku['wholesale_min_sku_count'];
            return false;
        }
        return true;
    }

    /**
     * Проверка кратности количества заказанных продуктов для артикулов.
     * Возвращает TRUE - если условие кратности количества для товара выполняется, FALSE - если условие не выполняется.
     * 
     * @param type $product_name - в эту переменную записывается имя товара, для которого условие кратности количества не выполняется.
     * @param type $multiplicity_product_count - в эту переменную записывается кратность товара для артикула.
     */
    public static function checkMultiplicitySkusCartCount(&$product_name = null, &$multiplicity_sku_count = null, &$item = null) {
        $sku_model = new shopProductSkusModel();
        $cart = new shopCart();
        $items = $cart->items();
        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $sku = $sku_model->getById($item['sku_id']);
                if (!empty($sku) && !self::checkMultiplicitySkuCount($sku, $item['quantity'], $product_name, $multiplicity_sku_count)) {
                    return false;
                }
            }
        }
        unset($item);
        return true;
    }

    public static function checkMultiplicitySkuCount($sku, $quantity, &$product_name = null, &$multiplicity_sku_count = null) {
        if ($sku['wholesale_sku_multiplicity'] > 0 && $quantity % $sku['wholesale_sku_multiplicity'] != 0) {
            $product_model = new shopProductModel();
            $product = $product_model->getById($sku['product_id']);

            $product_name = $product['name'];
            if (!empty($sku['name'])) {
                $product_name .= " (" . $sku['name'] . ")";
            }
            $multiplicity_sku_count = $sku['wholesale_sku_multiplicity'];
            return false;
        }
        return true;
    }

}
