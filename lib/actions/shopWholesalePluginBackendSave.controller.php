<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePluginBackendSaveController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('shop_wholesale', array());
            $domains_settings = waRequest::post('domains_settings', array());
            $reset = waRequest::post('reset');
            foreach ($settings as $name => $value) {
                $app_settings_model->set(shopWholesalePlugin::$plugin_id, $name, $value);
            }
            if ($reset) {
                $domains_settings = array();
            }
            shopWholesale::saveDomainsSettings($domains_settings);


            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
