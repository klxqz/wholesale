<?php

class shopWholesalePluginFrontendController extends waJsonController {

    public function execute() {
        $plugin = wa()->getPlugin('wholesale');
        $result = $plugin->checkOrder();
        $check = $plugin->checkOrder();
        $this->response['check'] = $check;
    }
}
