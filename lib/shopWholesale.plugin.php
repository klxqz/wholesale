<?php

class shopWholesalePlugin extends shopPlugin {

    public function frontendCart() {
        if ($this->getSettings('status')) {
            $cart = new shopCart();
            $def_currency = wa('shop')->getConfig()->getCurrency(true);
            $cur_currency = wa('shop')->getConfig()->getCurrency(false);
            $total = $cart->total(true);
            $total = shop_currency($total, $cur_currency, $def_currency, false);
            $min_order_sum = $this->getSettings('min_order_sum');
            $min_order_sum = shop_currency($min_order_sum);
            if ($total < $this->getSettings('min_order_sum')) {
                $text = $this->getSettings('text');
                $text = sprintf($text, $min_order_sum);
                $view = wa()->getView();
                $view->assign('text', $text);
                $view->assign('color', $this->getSettings('color'));
                $view->assign('font_weight', $this->getSettings('font_weight'));
                $template_path = wa()->getAppPath('plugins/wholesale/templates/Wholesale.html', 'shop');
                $html = $view->fetch($template_path);
                return $html;
            }
        }
    }

}