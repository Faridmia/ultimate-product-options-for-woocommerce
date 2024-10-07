(function (window, document, $, undefined) {
    'use strict';

    // Function to toggle field group visibility
    $(document).on('click', '.upow-flash-sale-item-group-header', function() {
        var fieldGroup = $(this).closest('.upow-flash-sale-item-group');
        var isOpen = fieldGroup.find('.upow-flash-sale-item-group-body').is(':visible');
        if (isOpen) {
            fieldGroup.find('.upow-flash-sale-item-group-body').slideUp();
        } else {
            fieldGroup.find('.upow-flash-sale-item-group-body').slideDown();
        }
    });

    // Function to update field group titles based on the label text input
    $(document).on('input', 'input[name*="[field_label]"]', function() {
        UpowUpdateFieldGroupTitles();
    });

    // Function to add a new field group
    $('#add-upow-flash-sale-item-group').on('click', function() {
        var index = $('.upow-flash-sale-item-group').length;
        UpowAddFieldGroup(index);
    });

    // Event delegation for removing field groups
    $(document).on('click', '.remove-upow-flash-sale-item-group', function() {
        $(this).closest('.upow-flash-sale-item-group').remove();
        UpowUpdateFieldGroupTitles();
    });

    // Function to update field group titles based on the label text input
    function UpowUpdateFieldGroupTitles() {
        $('.upow-flash-sale-item-group').each(function(index) {
            var label = $(this).find('input[name*="[field_label]"]').val();
            if (label) {
                $(this).find('.upow-flash-sale-item-group-header').text(label);
            } else {
                $(this).find('.upow-flash-sale-item-group-header').text('Field Group ' + (index + 1));
            }
        });
    }

    function initializeDatePickers() {
        $('.upow-flashsale-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: "focus"
        }).attr("placeholder", "yy-mm-dd");

        $(document).on('click', '.date-picker-svg', function() {
            $(this).siblings('input').focus();
        });

        $('.upow-flashsale-datepicker').each(function() {
            $(this).after('<span class="date-picker-svg" style="position: absolute; right: 10px; top: 46%; transform: translateY(-46%); cursor: pointer;">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar" viewBox="0 0 16 16">' +
                '<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1zm1-1h12V3a1 1 0 0 0-1-1h-1v.5a.5.5 0 0 1-1 0V2H4v.5a.5.5 0 0 1-1 0V2H2a1 1 0 0 0-1 1v.5z"/>' +
                '</svg>' +
                '</span>');
        });
    }

    // Initial call to initialize date pickers on page load
    initializeDatePickers();

    // Function to add a new field group
    function UpowAddFieldGroup(index) {
        var selectProductClass = 'upow-flashsale-select-popup';
        var excludeProductClass = 'upow-flashsale-exclude-popup';
        var newFieldGroup = `
            <div class="upow-flash-sale-item-group" data-index="${index}">
                <div class="upow-flash-sale-header">
                    <div class="upow-flash-sale-item-group-header">Field Group ${index + 1}</div>
                        <button type="button" class="remove-upow-flash-sale-item-group"><svg width="18" height="18" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
                </svg></button>
                    </div>
                    <div class="upow-flash-sale-item-group-body">
                    <p>
                        <label for="upow_product_${index}_field_label">Field Label</label>
                        <input type="text" name="upow_flashsale_product[${index}][field_label]" id="upow_product_${index}_field_label" value="">
                    </p>
                    <p>
                        <label for="upow_product_${index}_apply_all_product">Apply Across All Products</label>
                        <label class="upow-label-switch">
                            <input type="checkbox" name="upow_flashsale_product[${index}][apply_all_product]" id="upow_product_${index}_apply_all_product" value="yes">
                            <span class="upow-slider upow-round"></span>
                        </label>
                    </p>
                    <p class="upow-flashsale-product-fields-select  ${selectProductClass}">
                        <label for="upow_flashsale_product_${index}_select_product">Select Product</label>
                        <select multiple name="upow_flashsale_product[${index}][select_product][]" id="upow_flashsale_product_${index}_select_product" class="upow-select-product">
                            <!-- Options will be loaded here -->
                        </select>
                    </p>
                    <p class="upow-flashsale-product-fields-select ${excludeProductClass}">
                        <label for="upow_flashsale_product_${index}_exclude_product">Exclude Product</label>
                        <select multiple name="upow_flashsale_product[${index}][exclude_product][]" id="upow_flashsale_${index}_exclude_product" class="upow-select-product">
                            <!-- Options will be loaded here -->
                        </select>
                    </p>
                    <p class="upow-flashsale-product-categories-fields-select">
                        <label for="upow_flashsale_product_${index}_select_categories">Select Categories</label>
                        <select multiple name="upow_flashsale_product[${index}][select_categories][]" id="upow_flashsale_${index}_select_categories" class="upow-select-product">
                            <!-- Options will be loaded here -->
                        </select>
                    </p>
                    <p>
                        <label for="upow_flashsale_product_${index}_field_type">Discount Type</label>
                        <select name="upow_flashsale_product[${index}][discount_type]" id="upow_product_${index}_discount_type">
                            <option value="percent_discount">Percentage Discount</option>
                            <option value="fixed_discount">Fixed Discount</option>
                            <option value="fixed_price">Fixed Price</option>
                        </select>
                    </p>
                    <p>
                        <label for="upow_product_${index}_discount_value">Discount Value</label>
                        <input type="number" name="upow_flashsale_product[${index}][discount_value]" id="upow_product_${index}_discount_value" value="">
                    </p>
                    <p>
                        <label for="upow_product_${index}_flashsale_start_date">Flash Sale Start Date From</label>
                        <div class="date-picker-wrapper">
                            <input type="text" name="upow_flashsale_product[${index}][flashsale_start_date]" id="upow_product_${index}_flashsale_start_date" value="" class="upow-flashsale-datepicker">
                        </div>
                    </p>
                     <p>
                        <label for="upow_product_${index}_flashsale_end_date">Flash Sale End Date From</label>
                        <div class="date-picker-wrapper">
                            <input type="text" name="upow_flashsale_product[${index}][flashsale_end_date]" id="upow_product_${index}_flashsale_end_date" value="" class="upow-flashsale-datepicker">
                        </div>
                    </p>
                    <!-- Add hidden fields or other custom fields here -->
                    <input type="hidden" name="upow_flashsale_product[${index}][hidden_field]" value="">
                </div>
            </div>
        `;
        $('.upow-flash-sale-item-wrapper').append(newFieldGroup);
        // Fetch options via AJAX and insert into the select field
        $.ajax({
            url: upow_localize_obj.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'upow_get_all_product_options',
                nonce: upow_localize_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#upow_flashsale_product_' + index + '_select_product').html(response.data);
                } else {
                    console.error('Failed to load options:', response.data);
                }
            },
            error: function() {
                console.error('AJAX request failed');
            }
        });

        // exclude product
        $.ajax({
            url: upow_localize_obj.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'upow_get_exclude_all_product_options',
                nonce: upow_localize_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#upow_flashsale_' + index + '_exclude_product').html(response.data);
                } else {
                    console.error('Failed to load options:', response.data);
                }
            },
            error: function() {
                console.error('AJAX request failed');
            }
        });

        // select categories
        $.ajax({
            url: upow_localize_obj.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'upow_get_all_product_categories',
                nonce: upow_localize_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#upow_flashsale_' + index + '_select_categories').html(response.data);
                } else {
                    console.error('Failed to load options:', response.data);
                }
            },
            error: function() {
                console.error('AJAX request failed');
            }
        });


        allProductExcludeShowhide($('#upow_product_' + index + '_apply_all_product'), index);
       
        UpowUpdateFieldGroupTitles();
        initializeSortableFieldGroups();
        upow_option_select2();

        initializeDatePickers();
    }

    function upow_option_select2() {
        var select2_args = {
          placeholder: upow_localize_obj.select_placeholder,
          allowClear: true
        }
        $(".upow-flashsale-product-fields-select .upow-select-product,.upow-flashsale-product-categories-fields-select .upow-select-product").each(function (index) {
          if ($(this).next('.select2-container').length) {
            $(this).next('.select2-container').replaceWith('')
          }
          $(this).select2(select2_args);
        });
      }
    
      /* Select 2 */
      $('.upow-flashsale-product-fields-select .upow-select-product').each(function(){
        const $this = $(this),
            $parent = $this.parent();
        $this.select2({
            dropdownParent: $parent,
            placeholder: upow_localize_obj.select_placeholder,
        });
      });

      $('.upow-flashsale-product-categories-fields-select .upow-select-product').each(function(){
        const $this = $(this),
            $parent = $this.parent();
        $this.select2({
            dropdownParent: $parent,
            placeholder: upow_localize_obj.select_categories,
        });
      });

      

    // Function to initialize sortable for field groups
    function initializeSortableFieldGroups() {
        $('#upow-flash-sale-item-wrapper').sortable({
            handle: '.upow-flash-sale-item-group-header',
            items: '.upow-flash-sale-item-group',
            update: function(event, ui) {
                UpowUpdateFieldGroupTitles();
            }
        });
    }

    // Initialize sortable field groups on document ready
    $(document).ready(function() {
        initializeSortableFieldGroups();
        UpowUpdateFieldGroupTitles();

        // Bind event delegation to dynamically handle changes
        $(document).on('change', '.upow-flash-sale-item-group input[type="checkbox"]', function() {
            var index = $(this).closest('.upow-flash-sale-item-group').data('index');
            allProductExcludeShowhide($(this), index);
        });

        // Trigger change event for each existing checkbox on page load to ensure correct visibility
        $('.upow-flash-sale-item-group input[type="checkbox"]').each(function() {
            var index = $(this).closest('.upow-flash-sale-item-group').data('index');
            allProductExcludeShowhide($(this), index);
        });
    });

    function allProductExcludeShowhide($checkbox, index) {
       
        var $body = $checkbox.closest('.upow-flash-sale-item-group-body');
        if ($checkbox.is(':checked')) {
            $body.find('.upow-flashsale-select-popup').hide();
            $body.find('.upow-flashsale-exclude-popup').show();
        } else {
            $body.find('.upow-flashsale-select-popup').show();
            $body.find('.upow-flashsale-exclude-popup').hide();
        }
    }

    
})(window, document, jQuery);