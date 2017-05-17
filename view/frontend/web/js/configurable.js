define([
    'jquery',
    'mage/template',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, mageTemplate) {
    $.widget('attlaz_base.configurable', $.mage.configurable, {

        _changeProductImage: function () {

            this._super();


            console.log(this.simpleProduct);


            $('.child_stock').hide();
            $('.child_price').hide();
            if (this.simpleProduct !== undefined) {
                $('.child_stock_' + this.simpleProduct).show();
                $('.child_price_' + this.simpleProduct).show();
            }

            // AttlazBase.updateRequests();
            //
        },

        _reloadPrice: function () {

        },
        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector);
            //priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

            // if (priceBoxOptions && priceBoxOptions.optionTemplate) {
            //     options.optionTemplate = priceBoxOptions.optionTemplate;
            // }
            //
            // if (priceBoxOptions && priceBoxOptions.priceFormat) {
            //     options.priceFormat = priceBoxOptions.priceFormat;
            // }
            options.optionTemplate = mageTemplate(options.optionTemplate);
            options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

        }
    });

    return $.attlaz_base.configurable;
});