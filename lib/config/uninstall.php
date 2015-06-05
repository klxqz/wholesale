<?php

$model = new waModel();

try {
    $model->query("SELECT `min_product_count` FROM `shop_product` WHERE 0");
    $model->exec("ALTER TABLE `shop_product` DROP `min_product_count`");
} catch (waDbException $e) {
    
}

try {
    $model->query("SELECT `min_sum` FROM `shop_category` WHERE 0");
    $model->exec("ALTER TABLE `shop_category` DROP `min_sum`");
} catch (waDbException $e) {
    
}

try {
    $model->query("SELECT `min_product_count` FROM `shop_category` WHERE 0");
    $model->exec("ALTER TABLE `shop_category` DROP `min_product_count`");
} catch (waDbException $e) {
    
}
