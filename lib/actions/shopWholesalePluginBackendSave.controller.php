<?php

class shopWholesalePluginBackendSaveController extends waJsonController {

    public function execute() {
        $product_id = waRequest::post('wholesale_product_id');
        $wholesale_id = waRequest::post('wholesale_id', array());
        $wholesale_sku = waRequest::post('wholesale_sku', array());
        $wholesale_min_sku_count = waRequest::post('wholesale_min_sku_count', array());
        $wholesale_multiplicity = waRequest::post('wholesale_multiplicity', array());


        $wholesale_model = new shopWholesalePluginModel();
        $items = array();
        foreach ($wholesale_id as $key => $id) {
            $item = array(
                'id' => $id,
                'product_id' => $product_id,
                'sku_id' => $wholesale_sku[$key],
                'min_sku_count' => $wholesale_min_sku_count[$key],
                'multiplicity' => $wholesale_multiplicity[$key],
            );
            if (empty($item['id'])) {
                $item['id'] = $wholesale_model->insert($item);
            } else {
                $wholesale_model->updateById($item['id'], $item);
            }
            $items[] = $item;
        }
        $this->response['items'] = $items;
    }

}
