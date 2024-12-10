(function (window, document, $, undefined) {
    'use strict';
    $(document).on('change', '.woocommerce-ordering .orderby', function (e) {
        e.preventDefault();

        const sortBy = $(this).val();

        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('orderby', sortBy);
        window.history.pushState({}, '', currentUrl);
        console.log(wc_ajax_url.sortByNonce)

        $.ajax({
            url: wc_ajax_url.ajax_url,
            type: 'POST',
            data: {
                action: 'ajax_sort_products',
                orderby: sortBy,
                productNonce: wc_ajax_url.sortByNonce,
            },
            beforeSend: function () {
                if (!$('.loading-overlay').length) {
                    $('.wp-block-woocommerce-product-template,.products').append(`
                        <div class="loading-overlay">
                            <svg class="loading-spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                                <circle cx="50" cy="50" r="35" stroke-width="8" stroke="#3498db" stroke-dasharray="164.93361431346415 56.97787143782138" fill="none" stroke-linecap="round">
                                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                                </circle>
                            </svg>
                        </div>
                    `);
                }
                $('.loading-overlay').fadeIn();
            },
            success: function (response) {
                $('.wp-block-woocommerce-product-template,.products').html(response);
                $('ul.wp-block-woocommerce-product-template .product,ul.products .product').addClass('wc-block-product');
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            },
            complete: function () {
                $('.loading-overlay').fadeOut(function () {
                    $(this).remove();
                });
            },
        });
    });

    $(document).on('submit', '.woocommerce-ordering', function (e) {
        e.preventDefault();
    });
    
})(window, document, jQuery);
