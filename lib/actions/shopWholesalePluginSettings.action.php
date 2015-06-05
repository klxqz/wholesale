<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePluginSettingsAction extends waViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopWholesalePlugin::$plugin_id);

        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains_settings = shopWholesale::getDomainsSettings();
        
        $model = new shopPluginModel();

        $currency = wa('shop')->getConfig()->getCurrency(true);
        $this->view->assign('instances', $model->listPlugins(shopPluginModel::TYPE_SHIPPING, array('all' => true,)));
        $this->view->assign('currency', $currency);
        $this->view->assign('domain_routes', $domain_routes);
        $this->view->assign('domain_settings', $domains_settings);
        $this->view->assign('settings', $settings);
    }

}
