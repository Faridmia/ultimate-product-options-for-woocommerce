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


    $(window).on('load', function() {
        // Function to show/hide the preorder options based on checkbox state

        function getProductType() {
            var productType = $('#product-type').val(); // Get value from the product type dropdown

            return productType;
        }


        function togglePreorderOptions() {
            var isChecked = $('#upow_preorder_sample').is(':checked');
            var productType = $('input[name="product-type"]').val(); 

            var productType = getProductType();
            
            if (isChecked || productType === 'variable') {
               
                $('.upow_preorder_options').show();
                $('#upow_preorder_product_options').show();

            } else {
               
                $('.upow_preorder_options').hide();
                $('#upow_preorder_product_options').hide();
            }
        }

        // Initial check when the page loads
        togglePreorderOptions();

        // Event listener when the checkbox changes
        $('#upow_preorder_sample').change(function() {
            togglePreorderOptions();
        });
    });
    

})( window, document, jQuery );