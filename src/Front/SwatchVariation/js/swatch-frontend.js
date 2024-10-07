jQuery(document).ready(function ($) {
  const formSelector = ".variations_form";
  const swatchItemSelector = ".upow-swatch-item";
  const productImageSelector = ".has-post-thumbnail img, .attachment-woocommerce_thumbnail, .wc-block-components-product-image img";

  // Define custom jQuery functions
  $.fn.initializeVariationHandlers = function () {
    const form = $(this);
    const productID = form.data("product_id");

    form.handleSwatchClick();
    form.changeVariationImage();
    form.updateSwatchesOnChange(productID);
    form.resetVariation();
  };

  $.fn.handleSwatchClick = function () {
    const form = $(this);
    $(document).on("click", swatchItemSelector, function () {
      const el = $(this);
      const select = el.closest(".value").find("select");
      const value = el.data("value");

      // Update swatch and dropdown value
      el.addClass("selected").siblings(".selected").removeClass("selected");
      select.val(value).trigger('change');
      form.trigger('check_variations');

      // Force WooCommerce to recheck variations
      select.trigger('input').trigger('change').trigger('woocommerce_variation_select_change');
      form.trigger('check_variations');
    });
  };

  $.fn.changeVariationImage = function () {
    const form = $(this);
    const productImage = form.closest(".product").find(productImageSelector);

    form.on("found_variation", function (event, variation) {
      if (variation && variation.image && variation.image.src) {
        productImage.attr("src", variation.image.src);
        productImage.attr("srcset", variation.image.srcset);
      }
    });
  };

  $.fn.synchronizeSwatchesWithDropdown = function (attributeName, productID) {
    const form = $(this);
    const dropdown = form.find(`select[name='${attributeName}']`);
    const swatchWrapper = form.find(`.upow-swatch-wrapper[data-attribute_name='${attributeName}']`);

    const enabledOptions = dropdown.find("option:not(:first)").filter(function () {
      return !$(this).is(":disabled");
    }).map(function () {
      return $(this).val();
    }).get();

    swatchWrapper.find(swatchItemSelector).each(function () {
      const swatch = $(this);
      if (enabledOptions.includes(swatch.data("value"))) {
        swatch.removeClass("disabled").addClass("enabled");
      } else {
        swatch.removeClass("enabled").addClass("disabled");
      }
    });
  };

  $.fn.updateSwatchesOnChange = function (productID) {
    const form = $(this);
    const availableVariations = form.data("product_variations");

    if (availableVariations) {
      form.on("change", "select[name^='attribute_']", function () {
        const selectedAttribute = $(this).attr("name");
        const selectedValue = $(this).val();
        const relatedAttributes = form.find("select[name^='attribute_']").not(`[name='${selectedAttribute}']`);

        relatedAttributes.each(function () {
          const relatedAttribute = $(this).attr("name");
          const swatchWrapper = form.find(`.upow-swatch-wrapper[data-attribute_name='${relatedAttribute}']`);

          // Disable all swatches initially
          swatchWrapper.find(swatchItemSelector).removeClass("enabled").addClass("disabled");

          // Enable swatches based on selected attribute
          if (selectedValue) {
            availableVariations.forEach((variation) => {
              if (variation.attributes[selectedAttribute] === selectedValue) {
                const relatedValue = variation.attributes[relatedAttribute];
                const matchingSwatch = swatchWrapper.find(`.upow-swatch-item[data-value="${relatedValue}"]`);
                matchingSwatch.removeClass("disabled").addClass("enabled");
              }
            });
          } else {
            swatchWrapper.find(swatchItemSelector).removeClass("disabled").addClass("enabled");
          }

          form.synchronizeSwatchesWithDropdown(relatedAttribute, productID);
        });
      });
    }
  };

  $.fn.resetSwatches = function () {
    // Iterate over each .upow-swatch-wrapper
    $(".upow-swatch-wrapper").each(function () {
        const swatchWrapper = $(this); // Store the current .upow-swatch-wrapper
        
        // Inside the current wrapper, find all .upow-swatch-item and remove the 'disabled' class
        swatchWrapper.find(".upow-swatch-item").each(function () {
            $(this).removeClass("disabled").addClass("enabled");
        });
    });
};

$.fn.resetVariation = function () {
    const form = $(this);
    form.on("click", ".reset_variations", function (e) {
        e.preventDefault();
        
        // Delay to allow WooCommerce reset to complete before our custom reset
        setTimeout(function() {
            const productImage = form.closest(".product").find(productImageSelector);
            const originalImageSrc = productImage.data("original-src");
            const originalImageSrcset = productImage.data("original-srcset");

            // Reset image to original
            if (originalImageSrc) productImage.attr("src", originalImageSrc);
            if (originalImageSrcset) productImage.attr("srcset", originalImageSrcset);

            // Clear selected options and reset swatches
            form.find("select").val("");
            form.trigger("reset_data");

            // Reset swatch selection and remove the 'selected' class
            $(swatchItemSelector).removeClass("selected");

            // Reset all swatches by removing 'disabled' class and adding 'enabled'
            form.resetSwatches(); 
            $(".upow-selected-color-name, .upow-selected-logo-name").remove();
        }, 100); // 100ms delay to ensure WooCommerce completes reset before applying custom reset
    });
  };

  // Initialize the reset functionality
  $('form.variations_form').resetVariation();
  

  // Initialize product images data attributes
  $(productImageSelector).each(function () {
    const productImage = $(this);
    productImage.data("original-src", productImage.attr("src"));
    productImage.data("original-srcset", productImage.attr("srcset"));
  });

  // Initialize variation handlers for each form
  $(formSelector).each(function () {
    $(this).initializeVariationHandlers();
  });

});