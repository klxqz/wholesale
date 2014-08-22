<?php

/**
 * @author Коробов Николай wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'name' => 'Минимальная сумма заказа',
    'description' => 'Ограничение минимальной суммы заказа',
    'img' => 'img/wholesale.png',
    'vendor' => '985310',
    'version' => '1.0.1',
    'rights' => false,
    'frontend' => true,
    'handlers' => array(
        'frontend_cart' => 'frontendCart',
        'frontend_checkout' => 'frontendCheckout'
    )
);
