(function ( window, document, $, undefined ) {
    'use strict';

    $(document).ready(function($) {
        // Custom Add to Cart text for preorder products
        $('form.variations_form').on('found_variation', function(event, variation) {
            var addToCartBtn = $('form.variations_form .single_add_to_cart_button');
    
            // Check if the variation has a preorder label
            if (variation.preorder_label !== '') {
                addToCartBtn.text(variation.preorder_label); // Change the button text to the preorder label
            } else {
                addToCartBtn.text(preorder_obj.preordrDefaultAddToCartText);
            }
        });
    
        // Reset the button text when variations are reset
        $('form.variations_form').on('reset_data', function() {
            var addToCartBtn = $(this).find('.single_add_to_cart_button');
            addToCartBtn.text(preorder_obj.preordrDefaultAddToCartText);
        });
    });

    // pre order variation data

    $(document).ready(function($) {
        // When a variation is found (selected by the user)
        $('form.variations_form').on('found_variation', function(event, variation) {
            var addToCartBtn = $('form.variations_form .single_add_to_cart_button');
    
            if (variation.preorder_availability_message) {
                // Display preorder availability message
                $('.preorder-availability-message').html(variation.preorder_availability_message);
            }
    
            if (variation.preorder_pre_release_message) {
                // Display pre-release message
                $('.preorder-pre-release-message').html(variation.preorder_pre_release_message);
            }
    
            if (variation.preorder_limit) {
                // Display preorder limit
                $('.preorder-limit').html('Limited to: ' + variation.preorder_limit);
            }
    
            if (variation.preorder_availability_date) {
                // Display preorder availability date
                $('.preorder-availability-date').html('Available by: ' + variation.preorder_availability_date);
            }
        });
    
        // Reset the data when variations are reset
        $('form.variations_form').on('reset_data', function() {
            $('.preorder-availability-message, .preorder-pre-release-message, .preorder-limit, .preorder-availability-date').empty();
        });
    });

    

})( window, document, jQuery );