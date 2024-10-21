(function (window, document, $, undefined) {
    'use strict';

    $(document).ready(function($) {
        // Listen for checkbox click event
        $(document).on('change', '.upow_variable_checkbox', function() {
            // Get the checkbox state
            var isChecked = $(this).is(':checked');
            
            // Find the associated preorder fields panel
            var loopIndex = $(this).attr('name').match(/\[(\d+)\]/)[1];
            var $preorderFields = $('#preorder_product_options_' + loopIndex);
            
            // Show or hide the preorder fields based on checkbox state
            if (isChecked) {
                $preorderFields.show();
            } else {
                $preorderFields.hide();
            }
        });
    });

    let timeoutId; // Declare a variable to hold the timeout ID

    $(window).on('load', function() {
        // Clear any existing timeout before setting a new one
        if ( timeoutId ) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(function() {
           
            $('#upow_preorder_product_options').hide();
            if ($('.general_tab').hasClass('active')) {
                $('#upow_preorder_product_options').hide();
            }
        }, 100 ); 
    });
    
    // Click event for the "Pre-Order" tab
    $(document).ready(function($) {
        $('.pre_order_tab').on('click', function(e) {
            e.preventDefault(); 
            $('#upow_preorder_product_options').slideDown();
        });
    });

    // manage price 

    $(document).ready(function($) {
        function checkPreorderManagePrice() {
            var managePriceValue = $('#_upow_preorder_manage_price').val();
            if (managePriceValue === 'fixed_price') {
                $('._upow_preorder_amount_type_field').hide();
            } else {
                $('._upow_preorder_amount_type_field').show();
            }
        }

        checkPreorderManagePrice();
        $('#_upow_preorder_manage_price').on('change', function() {
            checkPreorderManagePrice();
        });
    });

    
    
    
    jQuery(document).ready(function($) {
        // Function to toggle the visibility based on the 'Manage Price' selection
        function toggleAmountTypeFields(managePriceField, index) {
            let selectVal = managePriceField.val(); // Get the selected value
            // Check if the selected value is 'fixed_price'
            if (selectVal === 'fixed_price') {
                $(`.woocommerce_variation:eq(${index}) ._upow_preorder_amount_type_${index}_field`).hide();
            } else {
                $(`.woocommerce_variation:eq(${index}) ._upow_preorder_amount_type_${index}_field`).show();
            }
        }
    
        // Loop through each variation to apply the conditional logic
        function applyManagePriceLogic() {
            $(".woocommerce_variation").each(function(index) {
                const managePriceField = $(this).find('select[id^="_upow_preorder_manage_price"]'); // Adjusted selector
    
                // Initially toggle based on the current value
                toggleAmountTypeFields(managePriceField, index);
    
                // Add an event listener to handle change events dynamically
                managePriceField.on('change', function() {
                    toggleAmountTypeFields($(this), index); // Call the toggle function when selection changes
                });
            });
        }
    
        // Use MutationObserver to detect when variations are dynamically added
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    applyManagePriceLogic(); // Apply the logic when new variations are added
                }
            });
        });
    
        // Observe changes in the variations wrapper
        const variationsContainer = document.querySelector('.woocommerce_variations');
        if (variationsContainer) {
            observer.observe(variationsContainer, { childList: true, subtree: true });
        }
    
        // Initial load logic
        $(window).on('load', function() {
            applyManagePriceLogic();
        });
    });
    
    


    

})( window, document, jQuery );