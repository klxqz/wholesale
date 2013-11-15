<?php

return array(
    'name' => 'Оптовый заказ',
    'description' => 'Ограничение на минимальной суммы заказа',
    'img' => 'img/wholesale.png',
    'vendor' => '985310',
    'version' => '1.0.0',
    'rights' => false,
    'frontend' => true,
    'handlers' => array(
        'frontend_cart' => 'frontendCart',
    )
);
