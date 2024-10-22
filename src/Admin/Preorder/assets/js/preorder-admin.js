(function (window, document, $, undefined) {
    'use strict';

    $(document).ready(function($) {
        const PreOrderModule = {
            timeoutId: null, // Store timeout ID to manage setTimeout calls
    
            // Function to initialize checkbox logic
            initCheckboxLogic: function() {
                $(document).on('change', '.upow_variable_checkbox', function() {
                    const isChecked = $(this).is(':checked');
                    const loopIndex = $(this).attr('name').match(/\[(\d+)\]/)[1];
                    const $preorderFields = $('#preorder_product_options_' + loopIndex);
                    if (isChecked) {
                        $preorderFields.show();
                    } else {
                        $preorderFields.hide();
                    }
                });
            },
    
            // Function to handle "Pre-Order" tab click event
            initPreOrderTab: function() {
                $('.pre_order_tab').on('click', function(e) {
                    e.preventDefault(); 
                    $('#upow_preorder_product_options').slideDown();
                });
            },
    
            // Function to manage price logic on a single preorder product
            initSingleManagePriceLogic: function() {
                const managePriceField = $('#_upow_preorder_manage_price');
    
                function checkPreorderManagePrice() {
                    const managePriceValue = managePriceField.val();
                    if ( managePriceValue === 'fixed_price' || managePriceValue == '' || managePriceValue == null ) {
                        $('._upow_preorder_amount_type_field').hide();
                    } else {
                        $('._upow_preorder_amount_type_field').show();
                    }
                }
    
                checkPreorderManagePrice();
                managePriceField.on('change', checkPreorderManagePrice);
            },
    
            // Function to handle variation-specific price management logic
            initVariationManagePriceLogic: function() {
                const toggleAmountTypeFields = function(managePriceField, index) {
                    const selectVal = managePriceField.val();
                    const $amountTypeField = $(`.woocommerce_variation:eq(${index}) ._upow_preorder_amount_type_${index}_field`);
    
                    if (selectVal === 'fixed_price' || selectVal == '' || selectVal == null ) {
                        $amountTypeField.hide();
                    } else {
                        $amountTypeField.show();
                    }
                };
    
                const applyManagePriceLogic = function() {
                    $(".woocommerce_variation").each(function(index) {
                        const managePriceField = $(this).find('select[id^="_upow_preorder_manage_price"]');
    
                        // Initially toggle based on the current value
                        toggleAmountTypeFields(managePriceField, index);
    
                        // Add change event listener to update dynamically
                        managePriceField.on('change', function() {
                            toggleAmountTypeFields($(this), index);
                        });
                    });
                };
    
                // Use MutationObserver to detect dynamically added variations
                const variationsContainer = document.querySelector('.woocommerce_variations');
                if (variationsContainer) {
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.addedNodes.length > 0) {
                                applyManagePriceLogic(); // Apply logic to newly added variations
                            }
                        });
                    });
    
                    // Observe changes in the variations wrapper
                    observer.observe(variationsContainer, { childList: true, subtree: true });
                }
    
                // Initial load logic
                applyManagePriceLogic();
            },

             // Function to show/hide the preorder options based on checkbox state
            togglePreorderOptionsOnLoad: function() {

                const togglePreorderOptions = function() {
                    const isChecked = $('#upow_preorder_sample').is(':checked');

                    if (isChecked) {
                        $('.upow_preorder_options').show();
                    } else {
                        $('.upow_preorder_options').hide();
                    }
                };

                // Initial check when the page loads
                togglePreorderOptions();

                // Event listener for when the checkbox changes
                $('#upow_preorder_sample').change(function() {
                    togglePreorderOptions();
                });
            },

    
            // Handle window load events and deferred actions
            handleWindowLoad: function() {
                $(window).on('load', () => {
                    if (this.timeoutId) {
                        clearTimeout(this.timeoutId);
                    }
    
                    this.timeoutId = setTimeout(() => {
                        $('#upow_preorder_product_options').hide();
    
                        if ($('.general_tab').hasClass('active')) {
                            $('#upow_preorder_product_options').hide();
                        }
                    }, 100); 

                    // Call the function to toggle preorder options on load
                    this.togglePreorderOptionsOnLoad();

                });
            },
    
            // Initialize all modules
            init: function() {
                this.initCheckboxLogic();            // Initialize checkbox logic
                this.initPreOrderTab();              // Initialize "Pre-Order" tab click logic
                this.initSingleManagePriceLogic();   // Initialize single product manage price logic
                this.initVariationManagePriceLogic(); // Initialize variation manage price logic
                this.handleWindowLoad();             // Handle window load event
            }
        };
    
        // Initialize the entire module
        PreOrderModule.init();
    });


})( window, document, jQuery );