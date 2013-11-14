<?php

return array(
    'status' => array(
        'title' => 'Статус',
        'description' => '',
        'value' => '1',
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            '0' => 'Выключен',
            '1' => 'Включен',

        )
    ),
    
    'min_order_sum' => array(
        'title' => 'Сумма минимального заказа',
        'description' => 'Сумма указывается в основной валюте. Клиент не может оформить заказ на сумму меньшую, чем указанная',
        'value' => '0',
        'control_type' => waHtmlControl::INPUT,
    ),
    
    'text' => array(
        'title' => 'Текст сообщения',
        'description' => '',
        'value' => 'Вы не можете оформить заказ т.к. сумма Вашего заказа меньше суммы минимального заказа. Сумма минимального заказа %s',
        'control_type' => waHtmlControl::INPUT,
    ),
);