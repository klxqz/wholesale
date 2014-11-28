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

            foreach ($settings as $name => $value) {
                $app_settings_model->set(shopWholesalePlugin::$plugin_id, $name, $value);
            }

            $template_path = wa()->getAppPath('plugins/wholesale/templates/FrontendCart.html', 'shop');
            $template = file_get_contents($template_path);

            foreach ($domains_settings as &$domain_settings) {
                $domain_settings['change_tpl'] = 0;
                if (!empty($domain_settings['reset_tpl'])) {
                    $domain_settings['template'] = '';
                } elseif ($domain_settings['template'] != $template) {
                    $domain_settings['change_tpl'] = 1;
                }
            }
            unset($domain_settings);

            $app_settings_model->set(shopWholesalePlugin::$plugin_id, 'domains_settings', json_encode($domains_settings));

            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
