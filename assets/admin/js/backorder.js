(function (window, document, $, undefined) {
    'use strict';

    $(document).ready(function($) {
        
        function toggleBackorderInfo() {

            // Check if the 'onbackorder' radio button is checked
            if ($('input[name="_stock_status"][value="onbackorder"]').is(':checked')) {
                // Show the backorder information
                $('.upow-backorder-information').css('display', 'block');
            } else {
                // Hide the backorder information if not on backorder
                $('.upow-backorder-information').css('display', 'none');
            }

        }
    
        // Initial check on page load
        toggleBackorderInfo();
    
        $('input[name="_stock_status"]').on('change', function() {
            toggleBackorderInfo(); // Check and toggle whenever a radio button is changed
        });
    });
    

})(window, document, jQuery);