<?php

/**
 * @author Коробов Николай wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePluginFrontendWholesaleController extends waJsonController {

    public function execute() {
        $plugin = wa()->getPlugin('wholesale');
        $check = $plugin->checkOrder();
        $this->response['check'] = $check;
    }

}
