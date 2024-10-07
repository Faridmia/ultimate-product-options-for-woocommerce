(function (window, document, $, undefined) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(document).ready(function () {
    if (
      localStorage.getItem("optionopen") != "undefibed" ||
      localStorage.getItem("optionopen") != ""
    ) {
      $(
        '.upow-slide-opt-section[data-option="' +
          localStorage.getItem("optionopen") +
          '"]'
      )
        .find(".upow-slide-item")
        .slideDown("slow");
    }
    $(document).on("click", ".upow-shado", function () {
      if (
        $(this)
          .closest(".upow-slide-opt-section")
          .find(".upow-slide-item")
          .is(":visible")
      ) {
        $(this)
          .closest(".upow-slide-opt-section")
          .find(".upow-slide-item")
          .slideUp("slow");
        localStorage.setItem("optionopen", "");
      } else {
        $(".upow-slide-item").slideUp("slow");
        $(this)
          .closest(".upow-slide-opt-section")
          .find(".upow-slide-item")
          .slideDown("slow");
        localStorage.setItem(
          "optionopen",
          $(this).closest(".upow-slide-opt-section").attr("data-option")
        );
      }
    });

    /* Add color picker when document is ready */
    $(".upow-general-item .upow-section-bg").wpColorPicker({
        change: function(event, ui) {
          // Enable the button when color is changed
          $('.upow_checkbox_item_save').prop('disabled', false);
        }
      }
    );
    $(".upow-last-input .upow-new-item-col").wpColorPicker({
      change: function(event, ui) {
        // Enable the button when color is changed
        $('.upow_checkbox_item_save').prop('disabled', false);
      }
    });
    //hide attribute label,select in widget
  });


  /* Select option to Select2 */
  function upow_option_select2() {
    var select2_args = {
      placeholder: upow_localize_obj.select_placeholder,
      allowClear: true
    }
    $(".upow-extra-product-fields-select .upow-select-product,.upow-flashsale-product-fields-select .upow-select-product").each(function (index) {
      if ($(this).next('.select2-container').length) {
        $(this).next('.select2-container').replaceWith('')
      }
      $(this).select2(select2_args);
    });
  }

  upow_option_select2();

  /* Select 2 */
  $('.upow-extra-product-fields-select .upow-select-product,.upow-flashsale-product-fields-select .upow-select-product').each(function(){
    const $this = $(this),
        $parent = $this.parent();
    $this.select2({
        dropdownParent: $parent,
        placeholder: "Select Product"
    });
  });

  /**
   * search triger
   */

  $(document).ready(function() {
    // Show popup when the button is clicked
    $('.upow-flash-sale-popup-active').on('click', function(e) {
        e.preventDefault();
        $('.upow-flash-sale-popup').addClass('active');
        $('html').addClass('popup-opened');
    });

    // Close popup when the close icon is clicked
    $('.upow-flash-sale-popup-close-icon').on('click', function(e) {
        e.preventDefault();
        $('.upow-flash-sale-popup').removeClass('active');
        $('html').removeClass('popup-opened');
    });
});
  
})(window, document, jQuery);
