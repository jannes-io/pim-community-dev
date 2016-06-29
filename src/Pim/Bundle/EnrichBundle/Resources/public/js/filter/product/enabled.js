/* global console */
'use strict';

//Should be reworked to be a boolean filter

define([
        'underscore',
        'oro/translator',
        'pim/filter/filter',
        'routing',
        'text!pim/template/filter/product/enabled',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'jquery.select2'
    ], function (_, __, BaseFilter, Routing, template) {
    return BaseFilter.extend({
        template: _.template(template),
        removable: false,
        events: {
            'change [name="filter-value"]': 'updateState'
        },
        renderInput: function () {
            if (undefined === this.getValue()) {
                this.setValue(null);
            }

            return this.template({
                labels: {
                    title: __('pim_enrich.export.product.filter.enabled.title'),
                    valueChoices: {
                        all: __('pim_enrich.export.product.filter.enabled.value.all'),
                        enabled: __('pim_enrich.export.product.filter.enabled.value.enabled'),
                        disabled: __('pim_enrich.export.product.filter.enabled.value.disabled')
                    }
                },
                value: this.getValue()
            });
        },
        postRender: function () {
            this.$('[name="filter-value"]').select2();
        },
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();

            if ('all' === value) {
                this.clearData();

                return;
            }

            this.setData({
                field: this.getField(),
                operator: '=',
                value: 'enabled' === value
            });
        }
    });
});
