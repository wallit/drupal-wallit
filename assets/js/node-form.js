(function ($) {
    Drupal.behaviors.imonezaFieldsetSummaries = {
        attach: function (context) {
            $('#edit-imoneza', context).drupalSetSummary(function (context) {
                return Drupal.t('Automatically managed');
            });
        }
    };
    
    function togglePricingGroupDisplay($pricingToggle, $pricingFormElement)
    {
        if ($pricingToggle.is(':checked')) {
            $pricingFormElement.show();
        }
        else {
            $pricingFormElement.hide();
        }
    }
    
    $(function() {
        var $pricingToggle = $('#edit-imoneza-override-pricing'),
            $pricingFormElement = $('.form-item-iMoneza-pricing-group-id');
       
        $pricingToggle.change(function(e) {
            togglePricingGroupDisplay($pricingToggle, $pricingFormElement);
        });
        
        togglePricingGroupDisplay($pricingToggle, $pricingFormElement);
    });
    
})(jQuery);