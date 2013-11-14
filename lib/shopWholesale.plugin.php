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
                $html = $view->fetch('plugins/wholesale/templates/wholesale.html');
                return $html;
            }
        }
    }

}