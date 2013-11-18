<?php

return array(
    'name' => 'Минимальная сумма заказа',
    'description' => 'Ограничение минимальной суммы заказа',
    'img' => 'img/wholesale.png',
    'vendor' => '985310',
    'version' => '1.0.0',
    'rights' => false,
    'frontend' => true,
    'handlers' => array(
        'frontend_cart' => 'frontendCart',
    )
);
