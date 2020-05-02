/*
 * @author MagePixel Team
 * @copyright Copyright © 2019 MagePixel. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        sampleShippingProviderShippingRatesValidator,
        sampleShippingProviderShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('shippingperproduct', sampleShippingProviderShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('shippingperproduct', sampleShippingProviderShippingRatesValidationRules);
        return Component;
    }
);