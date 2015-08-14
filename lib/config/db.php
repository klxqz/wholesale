<?php
return array(
    'shop_wholesale' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'product_id' => array('int', 11, 'null' => 0),
        'sku_id' => array('int', 11, 'null' => 0),
        'min_sku_count' => array('int', 11, 'null' => 0),
        'multiplicity' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('id'),
            'product_id' => 'product_id',
            'sku_id' => 'sku_id',
        ),
    ),
);
