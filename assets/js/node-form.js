(function ($) {
    Drupal.behaviors.imonezaFieldsetSummaries = {
        attach: function (context) {
            $('#edit-imoneza', context).drupalSetSummary(function (context) {
                if ($('#imoneza-is-dynamically-created-resources').val() == 1) {
                    return Drupal.t('Automatically managed');
                }
                else {
                    return Drupal.t('Manually managed');
                }
            });
        }
    };

    /**
     * Handle pricing toggle display
     * @param $pricingToggle
     * @param $pricingFormElement
     */
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
        // pricing display
        var $pricingToggle = $('#edit-imoneza-override-pricing'),
            $pricingFormElement = $('.form-item-iMoneza-pricing-group-id');
        $pricingToggle.change(function(e) {
            togglePricingGroupDisplay($pricingToggle, $pricingFormElement);
        });
        togglePricingGroupDisplay($pricingToggle, $pricingFormElement);
        
        //handle updating the pricing for this resource
        var nid = parseInt($('#imoneza-current-nid').val());
        $.getJSON(Drupal.settings.basePath + 'admin/imoneza/ajax-pricing/' + nid, function(response) {
            var $pricingGroupSelect = $('#edit-imoneza-pricing-group-id'),
                $overrideSelectLabel = $('label[for=edit-imoneza-override-pricing]'),
                $overrideSelect = $('#edit-imoneza-override-pricing'),
                $autoDisplay = $('#message-automatically-manage'),
                $manualDisplay = $('#message-manually-manage');

            if (response.data.options.dynamicallyCreateResources) {
                $autoDisplay.show();
                $manualDisplay.hide();
                $overrideSelectLabel.html($overrideSelect.data('automatically-manage'));
            }
            else {
                $autoDisplay.hide();
                $manualDisplay.show();
                $overrideSelectLabel.html($overrideSelect.data('manually-manage'));
            }
            
            // pricing group
            var selected = $pricingGroupSelect.val();
            $pricingGroupSelect.empty();
            $.each(response.data.options.pricingGroups, function(key, pricingGroup) {
                $pricingGroupSelect.append($('<option />').attr('value', pricingGroup.pricingGroupID).text(pricingGroup.name));
            });
            if (window.location.pathname.substr(-5) == '/edit') {
                $pricingGroupSelect.val(selected);
            }
        });
    });
    
})(jQuery);