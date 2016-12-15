(function ($) {
    $.wholesale = {
        cart: {
            options: {},
            init: function (options) {
                this.options = options;
                if (!this.checkSettings()) {
                    return false;
                }

                if (!$('#wholesale-cart').length) {
                    if (this.options.is_onestep) {
                        $('.onestep-cart .onestep-cart-form').after('<div id="wholesale-cart" class="hidden" style="display:none;"></div>');
                    } else {
                        $(this.options.checkout_selector).after('<div id="wholesale-cart" class="hidden" style="display:none;"></div>');
                    }
                }
                this.options.wholesale_selector = '#wholesale-cart';
                this.initUpdateCart();
                this.initCartTotalChange();

            },
            checkSettings: function () {
                /*Проверка наличия плагина заказ на одной странице*/
                if ($('.onestep-cart').length) {
                    this.options.is_onestep = true;
                    this.options.cart_total_selector = '.onestep-cart .cart-total';

                } else {
                    this.options.is_onestep = false;
                    if (!$(this.options.cart_total_selector).length) {
                        console.log('Указан неверный селектор "' + this.options.cart_total_selector + '"');
                        return false;
                    }
                    if (!$(this.options.checkout_selector).length) {
                        console.log('Указан неверный селектор "' + this.options.checkout_selector + '"');
                        return false;
                    }
                }
                return true;
            },
            initCartTotalChange: function () {
                var $cart_total = $(this.options.cart_total_selector);
                var total = '';
                setInterval(function () {
                    if (total != $cart_total.html()) {
                        total = $cart_total.html();
                        $(document).trigger('updateCart');
                    }
                }, 500);
            },
            disableCheckout: function (loading, message) {
                if ($('#wholesale-cart-loading').length) {
                    $('#wholesale-cart-loading').remove();
                }
                if (message === undefined) {
                    message = '';
                }
                if (loading === undefined) {
                    loading = false;
                }
                if (this.options.is_onestep) {
                    $('.onestep-cart .checkout').hide();
                    $(this.options.wholesale_selector).text(message);
                    if (message) {
                        $(this.options.wholesale_selector).removeClass('hidden').addClass('active').show();
                    } else {
                        $(this.options.wholesale_selector).removeClass('active').addClass('hidden').hide();
                    }
                    if (loading && !$('#wholesale-cart-loading').length) {
                        $('<span id="wholesale-cart-loading"><i class="icon16 loading"></i> Пожалуйста, подождите...</span>').insertBefore('.onestep-cart .checkout');
                    }
                } else {
                    $(this.options.checkout_selector).attr('disabled', true);
                    $(this.options.wholesale_selector).text(message);
                    if (message) {
                        $(this.options.wholesale_selector).removeClass('hidden').addClass('active').show();
                    } else {
                        $(this.options.wholesale_selector).removeClass('active').addClass('hidden').hide();
                    }
                    if (loading && !$('#wholesale-cart-loading').length) {
                        $('<span id="wholesale-cart-loading"><i class="icon16 loading"></i> Пожалуйста, подождите...</span>').insertBefore(this.options.checkout_selector);
                    }
                }
            },
            enableCheckout: function () {
                if ($('#wholesale-cart-loading').length) {
                    $('#wholesale-cart-loading').remove();
                }
                $(this.options.wholesale_selector).text('');
                $(this.options.wholesale_selector).removeClass('active').addClass('hidden').hide();
                if (this.options.is_onestep) {
                    $('.onestep-cart .checkout').show();
                } else {
                    $(this.options.checkout_selector).removeAttr('disabled');
                }
            },
            checkCart: function () {
                var wholesale = this;
                this.disableCheckout(true);
                $.ajax({
                    type: 'POST',
                    url: wholesale.options.url,
                    dataType: 'json',
                    success: function (data, textStatus, jqXHR) {
                        if (data.data.check.result) {
                            wholesale.enableCheckout();
                        } else {
                            wholesale.disableCheckout(false, data.data.check.message);
                        }
                    },
                    error: function (jqXHR, errorText) {
                        wholesale.enableCheckout();
                    }
                });
            },
            initUpdateCart: function () {
                var wholesale = this;
                $(document).on('updateCart', function () {
                    wholesale.checkCart();
                });
            }
        },
        product: {
            options: {},
            init: function (options) {
                this.options = options;
                if (!this.checkSettings()) {
                    return false;
                }
                this.options.input_quantity = $(this.options.product_cart_form_selector).find('input[name=quantity]');
                this.options.old_quantity_val = this.options.input_quantity.val();
                this.options.busy = false;
                this.options.first = true;
                this.initProductQuantityChange();
                this.initSkuChange();
                this.initUpdateProductQuantity();
            },
            checkSettings: function () {
                if (!$(this.options.product_cart_form_selector).length) {
                    console.log('Указан неверный селектор "' + this.options.product_cart_form_selector + '"');
                    return false;
                }
                if (!$(this.options.product_add2cart_selector).length) {
                    console.log('Указан неверный селектор "' + this.options.product_add2cart_selector + '"');
                    return false;
                }
                if (!$(this.options.product_cart_form_selector).find('input[name=quantity]').length) {
                    console.log('Не удалось найти "' + this.options.product_cart_form_selector + ' input[name=quantity]"');
                    return false;
                }
                return true;
            },
            initSkuChange: function () {
                $(this.options.product_cart_form_selector).find('[name=sku_id]').change(function () {
                    $(document).trigger('updateProductQuantity');
                });
                $(this.options.product_cart_form_selector).find('[name*="features"]').change(function () {
                    $(document).trigger('updateProductQuantity');
                });

            },
            initProductQuantityChange: function () {
                var $input_quantity = this.options.input_quantity;
                var quantity = '';
                setInterval(function () {
                    if (quantity != $input_quantity.val()) {
                        quantity = $input_quantity.val();
                        $(document).trigger('updateProductQuantity');
                    }
                }, 500);
            },
            checkProduct: function () {
                var wholesale = this;
                if (!wholesale.options.busy && !$(this.options.product_add2cart_selector).is(':disabled')) {
                    wholesale.options.busy = true;
                    var $form = $(this.options.product_cart_form_selector);
                    var $add2cart_button = $(this.options.product_add2cart_selector);
                    var $input_quantity = this.options.input_quantity;

                    $add2cart_button.attr('disabled', true);
                    var loading = $('<i class="icon16 loading"></i>').insertBefore($add2cart_button);
                    $.ajax({
                        type: 'POST',
                        url: wholesale.options.url,
                        data: $form.serialize() + '&old_quantity=' + wholesale.options.old_quantity_val,
                        dataType: 'json',
                        success: function (data, textStatus, jqXHR) {
                            wholesale.options.busy = false;
                            loading.remove();
                            if (data.data.check.result) {
                                $add2cart_button.removeAttr('disabled');
                            } else {
                                $input_quantity.val(data.data.check.quantity);
                                $add2cart_button.removeAttr('disabled');
                                if (wholesale.options.product_message && !wholesale.options.first) {
                                    alert(data.data.check.message);
                                }
                            }
                            wholesale.options.old_quantity_val = $input_quantity.val();
                            wholesale.options.first = false;
                        },
                        error: function (jqXHR, errorText) {
                            wholesale.options.busy = false;
                            loading.remove();
                            $add2cart_button.removeAttr('disabled');
                            //console.log(jqXHR.responseText);
                        }
                    });
                }
            },
            initUpdateProductQuantity: function () {
                var wholesale = this;
                $(document).on('updateProductQuantity', function () {
                    wholesale.checkProduct();
                });
            }
        },
        shipping: {
            disabled: false,
            message: '',
            options: {},
            init: function (options) {
                this.options = options;
                if (!this.checkSettings()) {
                    return false;
                }
                if (!$('#wholesale-shipping').length) {
                    if (this.options.is_onestep) {
                        $('.checkout-content[data-step-id=shipping]').after('<div id="wholesale-shipping" class="hidden" style="display:none;"></div>');
                    } else {
                        $(this.options.shipping_submit_selector).after('<div id="wholesale-shipping" class="hidden" style="display:none;"></div>');
                    }
                }
                this.options.wholesale_shipping = '#wholesale-shipping';
                this.initChangeShipping();
                this.initOnestepFormSubmit();
                $('input[name=shipping_id]:checked').change();
            },
            checkSettings: function () {
                /*Проверка наличия плагина заказ на одной странице*/
                if ($('.onestep-cart').length) {
                    this.options.is_onestep = true;
                } else {
                    this.options.is_onestep = false;
                    if (!$(this.options.shipping_submit_selector).length) {
                        console.log('Указан неверный селектор "' + this.options.shipping_submit_selector + '"');
                        return false;
                    }
                }
                return true;
            },
            initOnestepFormSubmit: function () {
                var self = this;
                $('form.checkout-form').submit(function () {
                    if (self.disabled) {
                        if (self.message) {
                            alert(self.message)
                        }
                        return false;
                    }
                });
            },
            disableCheckout: function (loading, message) {
                if ($('#wholesale-shipping-loading').length) {
                    $('#wholesale-shipping-loading').remove();
                }
                if (message === undefined) {
                    message = '';
                }
                if (loading === undefined) {
                    loading = false;
                }
                if (this.options.is_onestep) {
                    this.disabled = true;
                    this.message = message;
                    $(this.options.wholesale_shipping).text(message);
                    if (message) {
                        $(this.options.wholesale_shipping).removeClass('hidden').addClass('active').show();
                    } else {
                        $(this.options.wholesale_shipping).removeClass('active').addClass('hidden').hide();
                    }
                } else {
                    $(this.options.shipping_submit_selector).attr('disabled', true);
                    $(this.options.wholesale_shipping).text(message);
                    if (message) {
                        $(this.options.wholesale_shipping).removeClass('hidden').addClass('active').show();
                    } else {
                        $(this.options.wholesale_shipping).removeClass('active').addClass('hidden').hide();
                    }
                    if (loading && !$('#wholesale-shipping-loading').length) {
                        $('<span id="wholesale-shipping-loading"><i class="icon16 loading"></i> Пожалуйста, подождите...</span>').insertBefore(this.options.shipping_submit_selector);
                    }
                }
            },
            enableCheckout: function () {
                if ($('#wholesale-shipping-loading').length) {
                    $('#wholesale-shipping-loading').remove();
                }
                if (this.options.is_onestep) {
                    this.disabled = false;
                    this.message = '';
                    $(this.options.wholesale_shipping).text('');
                    $(this.options.wholesale_shipping).removeClass('active').addClass('hidden').hide();
                    $(this.options.shipping_submit_selector).removeAttr('disabled');
                } else {
                    $(this.options.wholesale_shipping).text('');
                    $(this.options.wholesale_shipping).removeClass('active').addClass('hidden').hide();
                    $(this.options.shipping_submit_selector).removeAttr('disabled');
                }
            },
            initChangeShipping: function () {
                var wholesale = this;
                $(document).off('change', 'input[name=shipping_id]').on('change', 'input[name=shipping_id]', function () {
                    var shipping_id = $(this).val();
                    wholesale.disableCheckout(true);
                    $.ajax({
                        type: 'POST',
                        url: wholesale.options.url,
                        dataType: 'json',
                        data: {
                            shipping_id: shipping_id
                        },
                        success: function (data, textStatus, jqXHR) {
                            if (data.data.check.result) {
                                wholesale.enableCheckout();
                            } else {
                                wholesale.disableCheckout(false, data.data.check.message);
                            }
                        },
                        error: function (jqXHR, errorText) {
                        }
                    });
                });
            }
        }
    };
})(jQuery);