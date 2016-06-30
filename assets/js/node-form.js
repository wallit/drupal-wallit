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
            
            // select the proper pricing group id 
            if (response.data.resourcePricingGroupId) {
                $pricingGroupSelect.val(response.data.resourcePricingGroupId);

                // check the box if needs be checking
                
                // if its dynamic, then check to see if the pricing group is not the default one (first one)  if it is
                // the first one, leave it unchecked.  If its not, check it
                // if its not dynamically create resource, and we're in this area where there is a pricing group id, that
                // means it was made by hand - so check it
                if (response.data.options.dynamicallyCreateResources) {
                    if ($('option:first', $pricingGroupSelect).val() != response.data.resourcePricingGroupId) {
                        $overrideSelect.attr('checked', true);
                    }
                }
                else {
                    $overrideSelect.attr('checked', true);
                }

                togglePricingGroupDisplay($pricingToggle, $pricingFormElement);
            }
        });
    });
    
})(jQuery);