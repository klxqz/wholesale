<?php

$plugin_id = array('shop', 'wholesale');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');


$model = new waModel();
try {
    $sql = 'SELECT `wholesale_min_product_count` FROM `shop_product` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = 'ALTER TABLE `shop_product` ADD `wholesale_min_product_count` INT NOT NULL AFTER `id`';
    $model->query($sql);
}

try {
    $sql = 'SELECT `wholesale_multiplicity` FROM `shop_product` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = 'ALTER TABLE `shop_product` ADD `wholesale_multiplicity` INT NOT NULL AFTER `id`';
    $model->query($sql);
}

try {
    $sql = 'SELECT `wholesale_min_sum` FROM `shop_category` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = 'ALTER TABLE `shop_category` ADD `wholesale_min_sum` DECIMAL( 15, 4 ) NOT NULL AFTER `id`';
    $model->query($sql);
}

try {
    $sql = 'SELECT `wholesale_min_product_count` FROM `shop_category` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = 'ALTER TABLE `shop_category` ADD `wholesale_min_product_count` INT NOT NULL AFTER `id`';
    $model->query($sql);
}
