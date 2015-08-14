(function($) {
    $.product_wholesale = {
        /**
         * {Number}
         */
        service_id: 0,
        /**
         * {Number}
         */
        product_id: 0,
        /**
         * {Jquery object}
         */
        form: null,
        /**
         * Keep track changing of form
         * {String}
         */
        form_serialized_data: '',
        /**
         * {Jquery object}
         */
        container: null,
        button_color: null,
        /**
         * {Object}
         */
        options: {},
        init: function(options) {
            this.options = options;
            if (options.container) {
                if (typeof options.container === 'object') {
                    this.container = options.container;
                } else {
                    this.container = $(options.container);
                }
            }
            if (options.counter) {
                if (typeof options.counter === 'object') {
                    this.counter = options.counter;
                } else {
                    this.counter = $(options.counter);
                }
            }

            this.service_id = parseInt(this.options.service_id, 10) || 0;
            this.product_id = parseInt(this.options.product_id, 10) || 0;
            this.form = $('#s-product-save');

            if (this.product_id) {

                // maintain intearaction with $.product object
                $.product.editTabWholesaleBlur = function() {
                    var that = $.product_wholesale;

                    if (that.form_serialized_data != that.form.serialize()) {
                        $.product_wholesale.save();
                    }
                };

                $.product.editTabWholesaleSave = function() {
                    $.product_wholesale.save();
                };

                var that = this;
                var button = $('#s-product-save-button');

                // some extra initializing
                that.container.addClass('ajax');
                that.form_serialized_data = that.form.serialize();
                that.counter.text(that.options.count);
                this.initButtons();
            }

        },
        initButtons: function() {
            var that = this;
            $('.add-row').click(function() {
                $('#wholesale-tmpl').tmpl(that.options).appendTo('#wholesale-table tbody');
                return false;
            });

            $('#wholesale-table').on('click', '.delete-row', function() {
                var url = '?plugin=wholesale&action=delete';
                var id = $(this).closest('tr').find('input[name="wholesale_id[]"]').val();
                var self = $(this);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(data, textStatus) {
                        self.closest('tr').remove();
                    }
                });

                return false;
            });

        },
        save: function() {

            var form = $.product_wholesale.form;
            var that = this;
            $.product.refresh('submit');

            var url = '?plugin=wholesale&action=save';
            return $.shop.jsonPost(
                    url,
                    form.serialize(),
                    function(r) {

                        for (var i in r.data.items) {
                            var item = r.data.items[i];
                            $('input[name="wholesale_id[]"]:eq(' + i + ')').val(item.id);
                        }

                        $.product.refresh();
                        $('#s-product-save-button').removeClass('yellow green').addClass('green');
                        that.form_serialized_data = form.serialize();
                        $.products.dispatch();
                    }
            );
        },
    };
})(jQuery);