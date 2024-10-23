(function (window, document, $, undefined) {
    'use strict';

    $(window).on('load', function() {
        $('.upow_popup_item_save').val('Save All Data').prop('disabled', true);
        $('.upow_popup_item_save').val('Save All Data').attr('disabled', 'disabled');
        $('.upow_checkbox_item_save').val('Save All Data').prop('disabled', true);
        $('.upow_checkbox_item_save').val('Save All Data').attr('disabled', 'disabled');

    });
   
    $('.upow-flash-sale-popup form').on('click', 'input, select, textarea', function() {
        $('.upow_popup_item_save').val('Save Changes').prop('disabled', false);
    });

    $('.upow-backorder-options-fields,.upow-variations-swatches-options-fields,.upow-preorder-options-fields,.upow-one-click-checkout-options-fields').on('click', 'input, select, textarea', function() {
        $('.upow_checkbox_item_save').val('Save Changes').prop('disabled', false);
    });

    $('.upow-general-settings-options').on('click', 'input, select, textarea', function() {
        $('.upow_checkbox_item_save').val('Save Changes').prop('disabled', false);
    });

    
    $('.upow-flashsale-countdown-from').on('click', 'input, select, textarea', function() {
        $('.upow_checkbox_item_save').val('Save Changes').prop('disabled', false);
    });

    $('.upow-extra-options-fields').on('click', 'input, select, textarea', function() {
        $('.upow_checkbox_item_save').val('Save Changes').prop('disabled', false);
    });
    $('.upow-extra-options-fields').on('change', 'select', function() {
        $('.upow_checkbox_item_save').val('Save Changes').prop('disabled', false);
    });

    $('#add-upow-flash-sale-item-group,.remove-upow-flash-sale-item-group').on('click', function() {
        $('.upow_popup_item_save').val('Save Changes').prop('disabled', false);
    });


    // // Function to save settings via AJAX
    // Function to save settings via AJAX
    function saveFlashSaleSettings(event) {
        event.preventDefault();

        var data = [];

        // Check if any flash sale items exist
        if ($('.upow-flash-sale-item-group').length > 0) {
            $('.upow-flash-sale-item-group').each(function () {
                var fields = {};
                $(this).find('input, select').each(function () {
                    var name = $(this).attr('name');
                    var value;

                    // Handle checkbox and radio inputs
                    if ($(this).is(':checkbox')) {
                        value = $(this).is(':checked') ? '1' : '0';
                    } else if ($(this).is(':radio')) {
                        value = $(this).is(':checked') ? $(this).val() : '';
                    } else {
                        value = $(this).val();
                    }

                    fields[name] = value;
                });
                data.push(fields);
            });
        }

        // Transform the data array to the desired structure
        var transformedData = [];
        $.each(data, function (index, item) {
            var transformedItem = { upow_flashsale_product: {} };
            $.each(item, function (key, value) {
                var matches = key.match(/upow_flashsale_product\[(\d+)\]\[(.+)\]/);
                if (matches) {
                    var fieldIndex = matches[1];
                    var fieldName = matches[2];
                    if (!transformedItem['upow_flashsale_product'][fieldIndex]) {
                        transformedItem['upow_flashsale_product'][fieldIndex] = {};
                    }
                    transformedItem['upow_flashsale_product'][fieldIndex][fieldName] = value;
                } else {
                    transformedItem[key] = value;
                }
            });
            transformedData.push(transformedItem);
        });

        // Capture the global checkbox values
        var enableFlashSale = $('input[name="upow_enable_flash_sale_here"]').is(':checked') ? 1 : 0;
        var override_saleflash = $('input[name="upow_override_saleflash"]').is(':checked') ? 1 : 0;

        var saveButton = $(this);

        // Perform the AJAX request
        $.ajax({
            url: upow_localize_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'save_flashsale_popup_settings',
                nonce: upow_localize_obj.nonce,
                data: transformedData, // Send empty array if no items
                enable_flash_sale: enableFlashSale,
                override_saleflash: override_saleflash,
            },
            beforeSend: function () {
                saveButton.val('Saving...').prop('disabled', false);
            },
            success: function (response) {
                if (response.success) {
                    displayMessage('Your settings have been saved', 'success');
                } else {
                    displayMessage('There was an error saving your settings', 'error');
                }
                saveButton.val('Save All Data').prop('disabled', true);
                saveButton.attr('disabled', 'disabled');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
                alert('AJAX error: ' + textStatus);
            }
        });
    }

    // Bind the save button click event
    $(document).on('click', '.upow_popup_item_save', saveFlashSaleSettings);


    // // general settings ajax data save functions

    $(document).ready(function($) {
        $('.upow-general-settings-options').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_general_settings_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');
            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });


    /*
    * flashsale countdown settings from
    */

    $(document).ready(function($) {
        $('.upow-flashsale-countdown-from').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_flashsale_settings_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');
            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    /*extra fields options fields*/

    $(document).ready(function($) {
        $('.upow-extra-options-fields').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_extra_options_fields_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');

            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    $(document).ready(function() {
        // Function to handle the show/hide logic
        function toggleExcludeProduct() {
            var enableExtraFeature = $('input[name="upow_global_extra_feature_on_off"]').is(':checked') ? 1 : 0;

            if (enableExtraFeature === 1) {
                $('.upow-exclude-product').show(); 
                $('.upow-select-product-fields').hide(); 
            } else {
                $('.upow-exclude-product').hide(); 
                $('.upow-select-product-fields').show(); 
            }
        }
    
        // Initial check on page load
        toggleExcludeProduct();
    
        // Event listener for click/change on the input
        $('input[name="upow_global_extra_feature_on_off"]').on('change', function() {
            toggleExcludeProduct();
        });
        
    });

    // backorder settings

    $(document).ready(function($) {
        $('.upow-backorder-options-fields').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_backorder_options_fields_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');

            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    // swatch variations

    $(document).ready(function($) {
        $('.upow-variations-swatches-options-fields').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_swatches_variations_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');

            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    function displayMessage(message, type = 'success') {
        var messageBox = $('#upow-message-box');
        
        // Message styling based on type (success or error)
        if (type === 'success') {
            messageBox.html('<p class="upow-saved-success-message">' + message + '</p>').fadeIn().delay(1500).fadeOut();
        } 
    }

    
    // preorder settings

    $(document).ready(function($) {
        $('.upow-preorder-options-fields').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_preorder_options_fields_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');

            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    // quick checkout ajax

    $(document).ready(function($) {
        $('.upow-one-click-checkout-options-fields').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=upow_quick_checkout_fields_save_options';
            formData += '&nonce=' + upow_localize_obj.nonce;
            
            var saveButton = $('.upow_checkbox_item_save');

            $.ajax({
                type: 'POST',
                url: upow_localize_obj.ajax_url,
                data: formData,
                beforeSend: function() {
                    saveButton.val('Saving...').prop('disabled', false);
                },
                success: function (response) {
                    if (response.success) {
                        displayMessage('Your settings have been saved', 'success');
                    } else {
                        displayMessage('There was an error saving your settings', 'error');
                    }
                    saveButton.val('Save All Data').prop('disabled', true);
                    saveButton.attr('disabled', 'disabled');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, errorThrown);
                    alert('AJAX error: ' + textStatus);
                }
            });
        });
    });

    


    
    
    

})(window, document, jQuery);