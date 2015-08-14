<?php

class shopWholesalePluginBackendDeleteController extends waJsonController {

    public function execute() {
        $id = waRequest::post('id');
        $wholesale_model = new shopWholesalePluginModel();
        $wholesale_model->deleteById($id);
    }

}
