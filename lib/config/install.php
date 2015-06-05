<?php

$plugin_id = array('shop', 'wholesale');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');


$model = new waModel();
try {
    $sql = 'SELECT `min_product_count` FROM `shop_product` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = 'ALTER TABLE `shop_product` ADD `min_product_count` INT NOT NULL AFTER `id`';
    $model->query($sql);
}
