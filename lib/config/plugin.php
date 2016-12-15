<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'name' => 'Минимальный заказ',
    'description' => 'Ограничение минимального заказа',
    'img' => 'img/wholesale.png',
    'vendor' => '985310',
    'version' => '3.6.1',
    'rights' => false,
    'frontend' => true,
    'shop_settings' => true,
    'handlers' => array(
        'frontend_cart' => 'frontendCart',
        'frontend_checkout' => 'frontendCheckout',
        'backend_product_edit' => 'backendProductEdit',
        'backend_category_dialog' => 'backendCategoryDialog',
        'backend_product_sku_settings' => 'backendProductSkuSettings',
        'frontend_product' => 'frontendProduct',
        'category_save' => 'categorySave',
    )
);
