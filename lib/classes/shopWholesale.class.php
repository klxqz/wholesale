<?php

class shopWholesale {

    public static function getRouteHash() {
        $domain = wa()->getRouting()->getDomain(null, true);
        $route = wa()->getRouting()->getRoute();
        return md5($domain . '/' . $route['url']);
    }

    public static function getDomainsSettings() {
        $app_settings_model = new waAppSettingsModel();
        $routing = wa()->getRouting();
        $domains_routes = $routing->getByApp('shop');

        $template_path = wa()->getAppPath('plugins/wholesale/templates/FrontendCart.html', 'shop');
        $template = file_get_contents($template_path);

        $domains_settings = json_decode($app_settings_model->get(shopWholesalePlugin::$plugin_id, 'domains_settings'), true);

        foreach ($domains_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $domain_route = md5($domain . '/' . $route['url']);
                if (empty($domains_settings[$domain_route])) {
                    $domains_settings[$domain_route] = shopWholesalePlugin::$default_settings;
                }
                if ($domains_settings[$domain_route]['change_tpl'] == 0) {
                    $domains_settings[$domain_route]['template'] = $template;
                }
            }
        }

        return $domains_settings;
    }

    public static function getDomainSettings() {
        $domains_settings = self::getDomainsSettings();
        $hash = self::getRouteHash();
        return $domains_settings[$hash];
    }

}
