/**
 * Avada
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the avada.io license that is
 * available through the world-wide-web at this URL:
 * https://www.avada.io/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Avada
 * @package     Avada_Proofo
 * @copyright   Copyright (c) Avada (https://www.avada.io/)
 * @license     https://www.avada.io/LICENSE.txt
 */
define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    "use strict";

    $.widget('proofo.sync', {
        options: {
            ajaxUrl: '',
            sync: '#proofo_webhook_sync_statistic_orders',
        },
        _create: function () {
            var self = this;

            $(this.options.sync).click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            var storeId = $("#store_switcher").val()
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    "form_key": this.options.formKey,
                    "store": storeId
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.status ? $t('Success') : $t('Error'),
                        content: result.content
                    });
                }
            });
        }
    });

    return $.proofo.sync;
});
