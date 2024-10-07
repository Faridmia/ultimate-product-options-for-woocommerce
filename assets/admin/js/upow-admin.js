(function (window, document, $, undefined) {
    'use strict';

    // Function to toggle field group visibility
    $(document).on('click', '.upow-extra-field-group-header', function() {
        var fieldGroup = $(this).closest('.upow-extra-field-group');
        var isOpen = fieldGroup.find('.upow-extra-field-group-body').is(':visible');
        if (isOpen) {
            fieldGroup.find('.upow-extra-field-group-body').slideUp();
        } else {
            fieldGroup.find('.upow-extra-field-group-body').slideDown();
        }
    });

    // Function to update field group titles based on the label text input
    $(document).on('input', 'input[name*="[field_label]"]', function() {
        UpowUpdateFieldGroupTitles();
    });

    // Function to add a new field group
    $('#add-upow-extra-field-group').on('click', function() {
        var index = $('.upow-extra-field-group').length;
        UpowAddFieldGroup(index);
    });

    // Event delegation for removing field groups
    $(document).on('click', '.remove-upow-extra-field-group', function() {
        $(this).closest('.upow-extra-field-group').remove();
        UpowUpdateFieldGroupTitles();
    });

    // Function to update field group titles based on the label text input
    function UpowUpdateFieldGroupTitles() {
        $('.upow-extra-field-group').each(function(index) {
            var label = $(this).find('input[name*="[field_label]"]').val();
            if (label) {
                $(this).find('.upow-extra-field-group-header').text(label);
            } else {
                $(this).find('.upow-extra-field-group-header').text('Field Group ' + (index + 1));
            }
        });
    }

    // Function to add a new field group
    function UpowAddFieldGroup(index) {
        var newFieldGroup = `
            <div class="upow-extra-field-group" data-index="${index}">
            <div class="upow-extra-field-group-item">
                <div class="upow-extra-field-group-header">Field Group ${index + 1}</div>
                <button type="button" class="remove-upow-extra-field-group"><svg width="18" height="18" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
                </svg></button>
                </div>
                <div class="upow-extra-field-group-body">
                    <p>
                        <label for="upow_product_${index}_field_type">Field Type</label>
                        <select name="upow_product[${index}][field_type]" id="upow_product_${index}_field_type">
                            <option value="text">Input</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                        </select>
                    </p>
                    <p>
                        <label for="upow_product_${index}_field_label">Field Label</label>
                        <input type="text" name="upow_product[${index}][field_label]" id="upow_product_${index}_field_label" value="">
                    </p>
                    <p>
                        <label for="upow_product_${index}_required">Required</label>
                        <label class="upow-label-switch">
                            <input type="checkbox" name="upow_product[${index}][required]" id="upow_product_${index}_required" value="1">
                            <span class="upow-slider upow-round"></span>
                        </label>
                    </p>
                    <p>
                        <label for="upow_product_${index}_default_value">Default Value</label>
                        <input type="text" name="upow_product[${index}][default_value]" id="upow_product_${index}_default_value" value="">
                    </p>
                    <p>
                        <label for="upow_product_${index}_placeholder_text">Placeholder Text</label>
                        <input type="text" name="upow_product[${index}][placeholder_text]" id="upow_product_${index}_placeholder_text" value="">
                    </p>
                    <!-- Add hidden fields or other custom fields here -->
                    <input type="hidden" name="upow_product[${index}][hidden_field]" value="">
                </div>
            </div>
        `;
        $('.upow-extra-options-wrapper').append(newFieldGroup);
        UpowUpdateFieldGroupTitles();
        initializeSortableFieldGroups();
    }

    // Function to initialize sortable for field groups
    function initializeSortableFieldGroups() {
        $('#upow-extra-options-wrapper').sortable({
            handle: '.upow-extra-field-group-header',
            items: '.upow-extra-field-group',
            update: function(event, ui) {
                UpowUpdateFieldGroupTitles();
            }
        });
    }

    // Initialize sortable field groups on document ready
    $(document).ready(function() {
        initializeSortableFieldGroups();
        UpowUpdateFieldGroupTitles();
    });

})(window, document, jQuery);