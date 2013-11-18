<?php

class shopWholesalePluginFrontendWholesaleController extends waJsonController {

    public function execute() {
        $plugin = wa()->getPlugin('wholesale');
        $check = $plugin->checkOrder();
        $this->response['check'] = $check;
    }
}
