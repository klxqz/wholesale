<?php

class shopWholesalePluginBackendAction extends waViewAction {

    public function execute() {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);

        $product_model = new shopProductModel();
        $product = $product_model->getById($id);
        if (!$product) {
            throw new waException(_w("Unknown product"));
        }
        $p = new shopProduct($product);
        $this->view->assign('product', $p);

        $wholesale_model = new shopWholesalePluginModel();
        $items = $wholesale_model->where('product_id = ' . (int) $id)
                ->order('sku_id ASC')
                ->fetchAll();
        $this->view->assign('items', $items);
    }

}
