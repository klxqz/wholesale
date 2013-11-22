<?php

/**
 * @author Коробов Николай wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePlugin extends shopPlugin {

    public function frontendCart() {
        if ($this->getSettings('status')) {
            $cart = new shopCart();

            $check = $this->checkOrder($min_order_sum, $min_order_sum_format);
            $text = $this->getSettings('text');
            $message = sprintf($text, $min_order_sum_format);

            $view = wa()->getView();
            $view->assign('message', $message);
            $view->assign('check', (int) $check);
            $view->assign('color', $this->getSettings('color'));
            $view->assign('font_weight', $this->getSettings('font_weight'));
            $template_path = wa()->getAppPath('plugins/wholesale/templates/FrontendCart.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public function checkOrder(&$min_order_sum = null, &$min_order_sum_format = null) {
        $cart = new shopCart();
        $def_currency = wa('shop')->getConfig()->getCurrency(true);
        $cur_currency = wa('shop')->getConfig()->getCurrency(false);

        $total = $cart->total(true);
        $total = shop_currency($total, $cur_currency, $def_currency, false);
        $min_order_sum = $this->getSettings('min_order_sum');
        $min_order_sum_format = shop_currency($min_order_sum);
        $min_order_sum = shop_currency($min_order_sum, null, null, false);

        if ($total < $this->getSettings('min_order_sum')) {
            return false;
        }
        return true;
    }

}
