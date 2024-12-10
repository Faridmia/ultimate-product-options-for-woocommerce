<?php
namespace Ultimate\Upow\Front\SortingManager;

use Ultimate\Upow\Traitval\Traitval;

/**
 * Class SortingManager
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class SortingManager
{
    use Traitval;

    public function __construct()
    {
        $upow_sorting_on_off = get_option( 'upow_sorting_on_off', true );

        if( $upow_sorting_on_off != 1 ) {
            return;
        }

        // Add filters for catalog ordering
        add_filter('woocommerce_default_catalog_orderby_options', [$this, 'upow_wc_add_advanced_sorting_options']);
        add_filter('woocommerce_catalog_orderby', [$this, 'upow_wc_add_advanced_sorting_options']);
        add_filter('woocommerce_get_catalog_ordering_args', [$this, 'upow_wc_custom_sorting_logic']);

        add_action('wp_ajax_ajax_sort_products', [$this, 'handle_ajax_sort_products'] );
        add_action('wp_ajax_nopriv_ajax_sort_products', [$this, 'handle_ajax_sort_products'] );
        add_action('wp_enqueue_scripts',  [$this, 'wc_advanced_sorting_enqueue_scripts'] );
    }

    /**
     * Custom sorting logic
     */
    public function upow_wc_custom_sorting_logic($args)
    {
        $orderby = $_GET['orderby'] ?? '';

        switch ($orderby) {
            case 'alphabetical':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'reverse_alpha':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'by_stock':
                $args['meta_key'] = '_stock_status';
                $args['orderby'] = 'meta_value';
                $args['order'] = 'ASC';
                break;
            case 'review_count':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_wc_review_count';
                $args['order'] = 'DESC';
                break;
            case 'on_sale_first':
                $args['meta_key'] = '_sale_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
        }
        return $args;
    }

    /**
     * Add advanced sorting options to WooCommerce
     */
    public function upow_wc_add_advanced_sorting_options($options)
    {
        
        $sorting_options = [
            'menu_order'   => ['group' => 'default', 'label' => __('Default sorting', 'ultimate-product-options-for-woocommerce')],
            'popularity'   => ['group' => 'default', 'label' => __('Sort by popularity', 'ultimate-product-options-for-woocommerce')],
            'rating'       => ['group' => 'default', 'label' => __('Sort by average rating', 'ultimate-product-options-for-woocommerce')],
            'date'         => ['group' => 'default', 'label' => __('Sort by latest', 'ultimate-product-options-for-woocommerce')],
            'price'        => ['group' => 'default', 'label' => __('Sort by price: low to high', 'ultimate-product-options-for-woocommerce')],
            'price-desc'   => ['group' => 'default', 'label' => __('Sort by price: high to low', 'ultimate-product-options-for-woocommerce')],
            'alphabetical' => ['group' => 'custom', 'label' => __('Sort by name: A to Z', 'ultimate-product-options-for-woocommerce')],
            'reverse_alpha'=> ['group' => 'custom', 'label' => __('Sort by name: Z to A', 'ultimate-product-options-for-woocommerce')],
            'by_stock'     => ['group' => 'custom', 'label' => __('Sort by availability', 'ultimate-product-options-for-woocommerce')],
            'review_count' => ['group' => 'custom', 'label' => __('Sort by review count', 'ultimate-product-options-for-woocommerce')],
            'on_sale_first'=> ['group' => 'custom', 'label' => __('Show sale items first', 'ultimate-product-options-for-woocommerce')],
        ];

        // Loop through all sorting options
        foreach ($sorting_options as $key => $data) {
            $option_value = get_option("upow_wc_advanced_sorting_settings[{$data['group']}][$key]", true);

            if (!empty($option_value) && $data['group'] == 'custom') {
                $options[$key] = $data['label']; // Add option
            } 
            if (!empty($option_value) && $data['group'] == 'default') {
                unset($options[$key]); // Add option
            }
        }

        return $options;
    }

    
    function wc_advanced_sorting_enqueue_scripts()
    {

        // Enqueue custom AJAX script
        wp_enqueue_script('wc-advanced-sorting-ajax', UPOW_CORE_ASSETS . 'src/Front/SortingManager/assets/js/wc-advanced-sorting.js', array('jquery'), UPOW_VERSION, true);
        wp_localize_script('wc-advanced-sorting-ajax', 'wc_ajax_url', 
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'sortByNonce' => wp_create_nonce( 'upow_product_nonce' )
            )
        );
    }

    function handle_ajax_sort_products() {

        // Nonce validation
        if (
            ! isset($_POST['productNonce']) || 
            ! wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['productNonce'])),
                'upow_product_nonce'
            )
        ) {
            wp_send_json_error(array('message' => 'Invalid nonce'), 403);
            exit;
        }

        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';

        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        switch ($orderby) {
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;

            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;

            case 'date':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;

            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;

            case 'price-desc':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'alphabetical':
                $args['orderby']  = 'title';
                $args['order']    = 'ASC';
                break;
            case 'reverse_alpha':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'by_stock':
                $args['meta_key'] = '_stock_status';
                $args['orderby'] = 'meta_value';
                $args['order'] = 'ASC';
                break;
            case 'review_count':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_wc_review_count';
                $args['order'] = 'DESC';
                break;
            case 'on_sale_first':
                $args['meta_key'] = '_sale_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;

            default:
                $args['orderby'] = 'menu_order';
                break;
        }

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product');
            }
        } else {
            printf('%s', '<p>' . esc_html__('No products found.', 'your-text-domain') . '</p>');
        }

        wp_reset_postdata();
        wp_die(); 
    }

}