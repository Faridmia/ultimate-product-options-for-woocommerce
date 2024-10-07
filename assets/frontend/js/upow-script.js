(function (window, document, $, undefined) {
    'use strict';
    // Function to get the current base price
    function UpowGetBasePrice() {
        // Check if there's a sale price

        var variable_price = $('.single_variation_wrap .woocommerce-variation .woocommerce-variation-price .woocommerce-Price-amount bdi');
        var salePriceElement2 = '';

        if (typeof upow_localize_product_obj !== 'undefined') {

            var productId = upow_localize_product_obj.productId;
            if( !variable_price.length > 0 ) {
                var salePriceElement2 = $('[data-is-descendent-of-single-product-template="true"] ins .woocommerce-Price-amount bdi').first();
                if( !salePriceElement2.length > 0 ) {
                    
                salePriceElement2 = $("#product-"+productId+" ins .woocommerce-Price-amount").first();
                }

                if( !salePriceElement2.length > 0 ) {
                var salePriceElement2 = $("#product-"+productId+" .woocommerce-Price-amount").first();
                }
            }
            
            if (salePriceElement2.length > 0) {
                return parseFloat(salePriceElement2.text().replace(/[^0-9.-]+/g, ""));
            } else if(variable_price.length > 0) {
            return parseFloat(variable_price.first().text().replace(/[^0-9.-]+/g, ""));
            }
            else {
                var priceElement = $('[data-is-descendent-of-single-product-template="true"] .woocommerce-Price-amount bdi').first();
                if(!priceElement.length > 0 ) {
                    var priceElement = $("#product-"+productId+" ins .woocommerce-Price-amount bdi").first();
                }
                return parseFloat(priceElement.text().replace(/[^0-9.-]+/g, ""));
            
            }
        }

    }

    // Update prices function
    function UpowUpdatePrices() {
        var base_price = UpowGetBasePrice();
        var extra_price = 0;

        // Handle radio buttons
        $('.upow-extra-options input[type=radio]:checked').each(function() {
            extra_price += parseFloat($(this).data('price'));
        });

        // Handle checkboxes
        $('.upow-extra-options input[type=checkbox]:checked').each(function() {
            extra_price += parseFloat($(this).data('price'));
        });

        // Handle text inputs (assuming the price is entered directly in the input field)
        $('.upow-extra-options input[type=text]').each(function() {
            var textPrice = parseFloat($(this).val());
            if (!isNaN(textPrice)) {
                extra_price += textPrice;
            }
        });

        // Variation price adjustment
        var variation_price = 0;
        $('.variations_form .variation_id').each(function() {
            var variation_id = $(this).val();
            if (variation_id) {
                var variation_element = $('.woocommerce-variation-price[data-variation-id="' + variation_id + '"] .woocommerce-Price-amount bdi');
                if (variation_element.length > 0) {
                    variation_price = parseFloat(variation_element.first().text().replace(/[^0-9.-]+/g, ""));
                }
            }
        });

        var subtotal_price = base_price + variation_price;
        var total_price = subtotal_price + extra_price;

        var formatted_extra_price = upowFormatCurrency(extra_price);
        var formatted_total_price = upowFormatCurrency(total_price);

        $('.upow-options-total-prices .upow-options-total-price').text(formatted_extra_price);
        $('.upow-total-price').text(formatted_total_price);
    }

    // Function to format the price according to WooCommerce settings
    function upowFormatCurrency(price) {
        let symbol = woo_front_obj.symbol;
        let position = woo_front_obj.position;
        let formatted_price = price.toFixed(2);

        switch (position) {
            case 'left':
                return symbol + formatted_price;
            case 'right':
                return formatted_price + symbol;
            case 'left_space':
                return symbol + ' ' + formatted_price;
            case 'right_space':
                return formatted_price + ' ' + symbol;
            default:
                return formatted_price;
        }
    }

    // Bind change event for all inputs
    $('.upow-extra-options').on('change', 'input[type=radio], input[type=checkbox]', function() {
        UpowUpdatePrices();
    });

    // Bind input event for text inputs
    $('.upow-extra-options').on('input', 'input[type=text]', function() {
        UpowUpdatePrices();
    });

    // Bind change event for variation selections
    $('.variations_form').on('change', '.variation_id', function() {
        UpowUpdatePrices();
    });

    // Initial price update
    UpowUpdatePrices();

    // accordion js
    jQuery(document).ready(function($) {

        if( woo_front_obj.upow_accordion_style_on_off == 'yes') {
            
            let $titleTab = $('.upow-extra-title-tab');
            $('.upow-extra-acc-item:eq(0)').find('.upow-extra-title-tab').addClass('active').next().stop().slideDown(300);
            $titleTab.on('click', function(e) {
                e.preventDefault();
                if ( $(this).hasClass('active') ) {
                    $(this).removeClass('active');
                    $(this).next().stop().slideUp(500);
                    $(this).next().find('p').removeClass('show');
                } else {
                    $(this).addClass('active');
                    $(this).next().stop().slideDown(500);
                    $(this).parent().siblings().children('.upow-extra-title-tab').removeClass('active');
                    $(this).parent().siblings().children('.upow-inner-content').slideUp(500);
                    $(this).parent().siblings().children('.upow-inner-content').find('p').removeClass('show');
                    $(this).next().find('p').addClass('show');
                }
            });

        } else {

            $('.upow-extra-acc-item').find('.upow-extra-title-tab').addClass('active');
            let innerContent = document.querySelectorAll('.upow-inner-content');
            let accIcon = document.querySelectorAll('.upow-extra-title-tab .icon');

            for( let i = 0; i<= innerContent.length; i++ ) {
                if(innerContent[i]) {
                    innerContent[i].style.display = "block";
                }
                
            }

            for( let i = 0; i<= accIcon.length; i++ ) {
                if(accIcon[i]) {
                    accIcon[i].style.display = "none";
                }
                
            }
            
        }
    });


})(window, document, jQuery);