<?php
namespace Ultimate\Upow\Front\Preorder;
use Ultimate\Upow\Traitval\Traitval;
/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class Preorder
{
    use Traitval;

    public $preorder_enable = '';
    public $preorder_price = '';
    public $availability_message = '';

    public function __construct() {

       
        $this->preorder_enable = get_option( 'upow_preorder_on_off', true );

        if( $this->preorder_enable != 1 ) {
            return;
        }

        add_action('wp_enqueue_scripts', [ $this, 'upow_enqueue_preorder_frontend_assets' ]);

        add_action('woocommerce_single_product_summary', [ $this,'upow_display_preorder_info_output'], 25);

        add_filter('woocommerce_available_variation', [ $this, 'upow_add_preorder_to_variations_data'], 10, 3);

        add_filter( 'woocommerce_available_variation', [ $this,'upow_add_preorder_data_to_variations'], 10, 3 );

        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'upow_check_preorder_quantity' ], 10, 3 );

        add_action( 'woocommerce_order_item_meta_end', [ $this, 'display_preorder_notification' ], 10, 4 );
        add_action( 'woocommerce_admin_order_item_headers', [ $this,'add_custom_admin_order_header'], 10, 1 );
        add_action( 'woocommerce_admin_order_item_values', [ $this,'display_preorder_notification_in_admin_order'], 10, 3 );

        add_filter( 'woocommerce_get_item_data', [ $this, 'render_preorder_availability_cart_page' ], 99, 2 );

        // Add preorder data to cart item when a product is added to the cart
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'upow_add_preorder_data_to_cart_item_data'], 10, 2 );

        // order  page
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'add_preorder_data_to_order_item' ], 10, 4 );

        add_filter( 'woocommerce_product_stock_status_options',  [ $this,'filter_get_stock_status_callback' ] );
        add_filter( 'posts_clauses',  [ $this, 'upow_filter_pre_order_product'], 10, 2 );
        add_filter( 'the_posts', [ $this, 'upow_filter_pre_order_prouduct_variable' ], 10, 2 );

        add_action( 'woocommerce_after_order_itemmeta', [ $this,'pre_order_add_text_order_detail_admin' ], 10, 3 );

        // Add custom column for Pre-Order Dates
        add_filter('manage_edit-product_columns', [ $this, 'add_preorder_date_column'], 99);
        // Populate the custom column
        add_action('manage_product_posts_custom_column', [ $this, 'populate_preorder_date_column' ], 10, 2 );

        $this->preorder_price = new PriceOverride();

        $this->availability_message = get_option('upow_preorder_available_text_msg', true );

    }


    /**
     * Enqueue frontend assets for preorder
     *
     * This function enqueues the necessary CSS and JavaScript files for the swatch functionality on the frontend.
     *
     * @return void
     */
    public function upow_enqueue_preorder_frontend_assets() {

        wp_enqueue_style('upow-preorder-front-css', UPOW_CORE_ASSETS . 'src/Front/Preorder/assets/css/preorder-front.css', array(), UPOW_VERSION );
        wp_enqueue_script('upow-preorder-front-js', UPOW_CORE_ASSETS . 'src/Front/Preorder/assets/js/preorder-frontend.js', array('jquery'), time(), true );

        if ( is_product() ) {

            wp_localize_script('upow-preorder-front-js', 'preorder_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'preordrDefaultAddToCartText' => __( 'Add to cart', 'ultimate-product-options-for-woocommerce' )
            ));
        }
        
    }

    /**
     * Displays pre-order information for a given WooCommerce product.
     *
     * Retrieves and formats pre-order metadata such as availability date, messages, 
     * and available quantity for the product.
     *
     * @param WC_Product $product The WooCommerce product object.
     * 
     * @return string|null The formatted availability message, or null if the product is invalid.
     */
    public function upow_display_preorder_info( $product ) {
    
        if ( !$product || ! $product->get_id() ) {
            return; // Ensure product object is available
        }
    
        $preorder_data = [
            'pre_release_message'   => get_post_meta( $product->get_id(), '_upow_preorder_pre_released_message', true ),
            'preorder_limit'        => get_post_meta( $product->get_id(), '_upow_preorder_available_quantity', true ) ,
            'availability_date'     => get_post_meta( $product->get_id(), '_upow_preorder_availability_date', true ),
            'availability_message'  => get_post_meta( $product->get_id(), '_upow_preorder_availability_message', true ),
            'available_quantity'    => get_post_meta( $product->get_id(), '_upow_preorder_available_quantity', true ),
        ];

        $availability_message = $this->get_availability_message( $preorder_data );
        return wp_kses_post( $availability_message );

    }

    /**
     * Outputs the pre-order information for the current global WooCommerce product.
     *
     * Calls the pre-order display function and echoes the formatted pre-order message for the product.
     *
     * @return void
     */
    public function upow_display_preorder_info_output() {

        global $product;
        echo wp_kses_post( $this->upow_display_preorder_info( $product ) ); // Echo the returned message
    }
    
    /**
     * Adds preorder data to WooCommerce variation data.
     *
     * @param array    $variation_data The variation data array.
     * @param WC_Product $product      The product object.
     * @param WC_Product_Variation $variation The product variation object.
     * @return array                   Modified variation data with preorder label if applicable.
     */
    public function upow_add_preorder_data_to_variations( $variation_data, $product, $variation ) {
            
        if ( !$product || ! $variation->get_id() ) {
            return; 
        }

        if ( $product->is_type( 'variable' ) ) {
            $preorder_status = get_post_meta( $variation->get_id(), '_upow_preorder_variable_product', true );
            
            if ( $preorder_status === 'yes' ) {
                $variation_data['preorder_label'] = get_option( 'upow_preorder_addto_cart_text', __( 'Preorder Now', 'ultimate-product-options-for-woocommerce' ) );
            } else {
                $variation_data['preorder_label'] = ''; 
            }
        }
    
        return $variation_data;
    }

    /**
     * Adds preorder-related data to WooCommerce variation data.
     *
     * This function checks if the variation is part of a variable product and retrieves preorder metadata if applicable.
     * It appends a preorder availability message to the variation description.
     *
     * @param array    $variation_data The variation data array.
     * @param WC_Product $product      The product object.
     * @param WC_Product_Variation $variation The product variation object.
     * @return array                   Modified variation data with preorder details and availability message.
     */
    
    public function upow_add_preorder_to_variations_data( $variation_data, $product, $variation ) {

        if ( $product->is_type( 'variable' ) ) {
            
            $preorder_status = get_post_meta( $variation->get_id(), '_upow_preorder_variable_product', true );
            // Set a label text if preorder is enabled
            if ( $preorder_status === 'yes' ) {

                $variation_id = $variation->get_id();
            
                if ( !$product || ! $variation->get_id() ) {
                    return; 
                }
            
                $preorder_data = [
                    'pre_release_message'   => get_post_meta( $variation_id, '_upow_preorder_pre_released_message', true),
                    'preorder_limit'        => get_post_meta( $variation_id, '_upow_preorder_available_quantity', true),
                    'availability_date'     => get_post_meta( $variation_id, '_upow_preorder_availability_date', true),
                    'availability_message'  => get_post_meta( $variation_id, '_upow_preorder_availability_message', true),
                    'available_quantity'    => get_post_meta( $variation_id, '_upow_preorder_available_quantity', true),
                ];

                $availability_message = $this->get_availability_message( $preorder_data );
                $variation_data['variation_description'] .= $availability_message . '<br>';
            }
        }
    
        return $variation_data;
    }
    
    /**
     * Ensures the quantity added to the cart does not exceed the available preorder limit and prevents adding to cart if exceeded.
     *
     * @param bool  $passed      Whether the product can be added to the cart.
     * @param int   $product_id  The ID of the product being added.
     * @param int   $quantity    The quantity of the product being added.
     * @return bool              True if the product can be added, false if it exceeds the preorder limit.
     */
    public function upow_check_preorder_quantity( $passed, $product_id, $quantity ) {

        $available_quantity = get_post_meta( $product_id, '_upow_preorder_available_quantity', true );
        
        if ( ! empty( $available_quantity ) && $available_quantity > 0 ) {
           
            if ( $quantity > $available_quantity ) {
                wc_add_notice( sprintf( __( 'You cannot preorder more than %d of this item.', 'ultimate-product-options-for-woocommerce' ), $available_quantity ), 'error' );
                return false; // Prevent adding to cart
            }
        }
        
        return $passed;
    }


    /**
     * Displays preorder notification with availability message for simple or variable products on the order page.
     *
     * @param int    $item_id     The order item ID.
     * @param object $item        The order item object.
     * @param object $order       The order object.
     * @param bool   $plain_text  Whether the output should be in plain text.
     */
    public function display_preorder_notification( $item_id, $item, $order, $plain_text ) {

        $product                    = $item->get_product();
        $product_id                 = $product->get_id();
        $simple_preorder_enable     = get_post_meta( $product_id, '_upow_preorder_sample', true );
        $enable_variation_preorder  = get_post_meta( $product_id , '_upow_preorder_variable_product', true );
    
        if  ( $enable_variation_preorder === 'yes' || $simple_preorder_enable == 'yes' ) {
           
            if ( $product->is_type( 'simple' ) ) {
                echo  wp_kses_post( $this->upow_display_preorder_info( $product ) );
            } elseif( $product->is_type( 'variation' ) ) {

                $preorder_data = $item->get_meta('preorder_data', true);
               
                // Check if preorder data exists
                if ( isset($preorder_data ) && ! empty( $preorder_data ) ) {
                   
                    $availability_message = $this->get_availability_message( $preorder_data );

                    echo  wp_kses_post( $availability_message );
                }
            }
        }
    }

    /**
     * Adds a custom "Preorder Info" header to the admin order table if any product in the order has preorder enabled.
     *
     * @param object $order  The WooCommerce order object.
     */
    public function add_custom_admin_order_header( $order ) {

        // Loop through the order items to find the product
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            if ( $product ) {

                $product_id = $product->get_id(); // Get the product ID
                $simple_preorder_enable  = get_post_meta( $product_id, '_upow_preorder_sample', true );
                $enable_preorder         = get_post_meta( $product_id, '_upow_preorder_variable_product', true );

                if ( $enable_preorder == 'yes' ||  $simple_preorder_enable == 'yes' ) {
                    printf(
                        '<th class="upow-preorder-column-header">%s</th>',
                        esc_html__( 'Preorder Info', 'ultimate-product-options-for-woocommerce' )
                    );
                    break; // We found the product and added the header, no need to continue looping
                }
            }
        }

    }
    
    /**
     * Adds preorder data as metadata to the order item when an order is created.
     *
     * @param object $item           The order item object.
     * @param string $cart_item_key  The cart item key.
     * @param array  $values         The cart item values, including preorder data.
     * @param object $order          The WooCommerce order object.
     */
    public function add_preorder_data_to_order_item( $item, $cart_item_key, $values, $order ) {

        if ( isset( $values['preorder_data'] ) && ! empty( $values['preorder_data'] ) ) {
            $item->add_meta_data( 'preorder_data', $values['preorder_data'], true );
        }

    }

    /**
     * Displays preorder information in the admin order page for simple or variable products with preorder enabled.
     *
     * @param object $product  The WooCommerce product object.
     * @param object $item     The order item object.
     * @param int    $item_id  The order item ID.
     */
    public function display_preorder_notification_in_admin_order( $product, $item, $item_id ) {

        $availability_date       = $product->get_meta( '_upow_preorder_availability_date' );
        $today                   = strtotime('today');
        $product_id              = $product->get_id();
        $simple_preorder_enable  = get_post_meta( $product_id, '_upow_preorder_sample', true );
        $enable_preorder         = get_post_meta( $product_id, '_upow_preorder_variable_product', true );

        $preorder_column = '<td class="upow-preorder-column">';
    
        if  ( $enable_preorder === 'yes' || $simple_preorder_enable == 'yes' ) {

            if ( $product->is_type( 'simple' ) ) {
                $preorder_column .= $this->upow_display_preorder_info( $product );
            } elseif( $product->is_type( 'variation' ) ) {

                $preorder_data = $item->get_meta('preorder_data', true);

                if ( ! empty( $preorder_data ) ) {

                    $availability_message = $this->get_availability_message( $preorder_data );

                    $preorder_column .= $availability_message;
                }
            }
            
        } else {
            $preorder_column .= '-';
        }
    
        $preorder_column .= '</td>';
        echo wp_kses_post( $preorder_column );

    }

    /**
     * Renders preorder availability information on the cart page by appending an availability message to the cart item data.
     *
     * @param array $item_data  The cart item data to display.
     * @param array $cart_item  The cart item array containing preorder data.
     * @return array            The modified cart item data with the preorder availability message.
     */
    public function render_preorder_availability_cart_page( $item_data, $cart_item ) {

        if ( ! empty( $cart_item['preorder_data'] ) ) {

            $preorder_data          = $cart_item['preorder_data'];
            $availability_message   = $this->get_availability_message( $preorder_data );

            // Append the availability message to the item data
            if ( ! empty( $availability_message ) ) {
                $item_data[] = [
                    'name'  => __( 'Preorder:', 'ultimate-product-options-for-woocommerce' ),
                    'value' => $availability_message,
                ];
            }
        }

        return $item_data;
    }

    /**
     * Adds preorder data to the cart item if the product is a simple or variable type with preorder enabled.
     *
     * @param array $cart_item_data  The cart item data to be modified.
     * @param int   $product_id      The ID of the product being added to the cart.
     * @return array                 The modified cart item data including preorder information.
     */
    
    public function upow_add_preorder_data_to_cart_item_data( $cart_item_data, $product_id ) {

        $product = wc_get_product( $product_id );

        $simpleProId = $product->get_id();

        if ( $product && $product->is_type( 'variable' ) ) {
            $product_global_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        }
        elseif ( $product && $product->is_type( 'simple' ) ) {
            $product_global_id = isset( $simpleProId  ) ? absint( $simpleProId  ) : 0;
        }

        if ( $product_global_id > 0 ) {

            $simple_preorder_enable  = get_post_meta( $product_id, '_upow_preorder_sample', true );
            $preorder_status = get_post_meta( $product_global_id, '_upow_preorder_variable_product', true );

            if ( $preorder_status === 'yes' || $simple_preorder_enable == 'yes' ) {
                $preorder_data = [
                    'pre_release_message'   => get_post_meta( $product_global_id, '_upow_preorder_pre_released_message', true),
                    'availability_date'     => get_post_meta( $product_global_id, '_upow_preorder_availability_date', true),
                    'availability_message'  => get_post_meta( $product_global_id, '_upow_preorder_availability_message', true),
                    'available_quantity'    => get_post_meta( $product_global_id, '_upow_preorder_available_quantity', true),
                ];

                // Save preorder data to cart item
                $cart_item_data['preorder_data'] = $preorder_data;
            }
        }

        return $cart_item_data;

    }

    /**
     * Generates a formatted availability message for preorder products based on the provided preorder data.
     *
     * @param array $preorder_data  The preorder data containing availability information.
     * @return string               The formatted availability message, including release date and pre-release message.
     */
    public function get_availability_message( $preorder_data ) {

        if( isset( $preorder_data['availability_message'] ) && !empty( $preorder_data['availability_message'] ) ) {
            $availability_on = $preorder_data['availability_message'];
        } else if ( isset( $this->availability_message ) && !empty( $this->availability_message  ) ) {
            $availability_on = $this->availability_message;
        } else {
            $availability_on = '';
        }
    
        // Initialize message variable
        $availability_message = '';
    
        // Check if both availability date and message are available
        if (  !empty( $preorder_data['availability_date'] ) && !empty( $availability_on ) ) {
            $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
            $availability_message .= '<p class="preorder-availability-message">' . esc_html( $availability_on ) . ' ' . esc_html( $formatted_date ) . '</p>';
        }
    
        if ( empty( $availability_on ) && !empty( $preorder_data['pre_release_message'] ) ) {
            $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
        }
    
        if ( !is_checkout() && !is_cart()  && !empty( $preorder_data['preorder_limit'] ) ) { 
            $availability_message .= '<p class="preorder-limit">' . esc_html__('Limited to:', 'ultimate-product-options-for-woocommerce') . ' ' . esc_html( $preorder_data['preorder_limit'] ) . '</p>';
        }

        return $availability_message;

    }

    /**
     * Adds a custom stock status 'Pre-Order' for products in the admin product listing.
     *
     * @param array $status The current stock status array.
     * @return array       The modified stock status array with the added 'Pre-Order' status for products.
     */

    public function filter_get_stock_status_callback( $status ) {

        if ( isset( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $post_type = sanitize_text_field( $_GET['post_type'] );

            if( 'product' == $post_type ) {
                $status['preorder'] = __( 'Pre-Order', 'ultimate-product-options-for-woocommerce' );
            }
        }

        return $status; 
    }


    /**
     * Modifies the product archive query to filter for products with a 'preorder' stock status.
     *
     * @param array   $args  The original query arguments.
     * @param WP_Query $query The current WP_Query instance.
     * @return array         The modified query arguments to include preorder products.
     */
    public function upow_filter_pre_order_product( $args, $query ) {
        global $wpdb;

        if ( ! $query->is_main_query() || ! is_post_type_archive('product') ) {
            return $args; // Only modify main query for product archive
        }
    
        if ( isset( $_GET['stock_status'] ) && ! empty( $_GET['stock_status'] ) && $_GET['stock_status'] === 'preorder' ) {
            
            $args['where'] = str_replace(
                "AND {$wpdb->posts}.post_type = 'product'", 
                "AND ({$wpdb->posts}.post_type = 'product' OR {$wpdb->posts}.post_type = 'product_variation')", 
                $args['where']
            );
            
            $args['where'] = " AND ( 
                {$wpdb->postmeta}.meta_key = '_upow_preorder_sample' 
                AND {$wpdb->postmeta}.meta_value = 'yes'
                ) 
                OR (
                {$wpdb->postmeta}.meta_key = '_upow_preorder_variable_product' 
                AND {$wpdb->postmeta}.meta_value = 'yes'
                )" . $args['where'];
            
            // Ensure the join includes the postmeta table for accessing the custom fields
            $args['join']  .= " INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) ";
        }
    
       return $args;
    }

    /**
     * Filters the product list in the admin area to only include simple products that are available for pre-order.
     *
     * @param array      $posts The array of posts (products) to filter.
     * @param WP_Query   $query The current WP_Query instance.
     * @return array             The filtered array of posts, including only those with pre-order enabled.
     */
    public function upow_filter_pre_order_prouduct_variable( $posts, $query ) {

        if ( is_admin() && isset( $_GET['stock_status'] ) && $_GET['stock_status'] == 'preorder' ) {

            foreach ( $posts as $key => $post ) {
    
                $product_id         = $post->ID;
                $product            = wc_get_product( $product_id );
    
                // Check for simple product pre-order
                $simple_preorder_enable = get_post_meta($product_id, '_upow_preorder_sample', true);

                if ( $simple_preorder_enable !== 'yes' ) {
                    unset( $posts[$key] ); 
                }
            }
        }
    
        return $posts;
    }

    /**
     * Adds a pre-order notification text to the order details in the admin for line items that are pre-order enabled.
     *
     * @param int        $item_id The ID of the order item.
     * @param WC_Order_Item_Product $item The order item object.
     * @param WC_Order   $order The order object.
     */
    public function pre_order_add_text_order_detail_admin( $item_id, $item, $order ) {
        
        if ( ! $item->is_type( 'line_item' ) ) {
            return; // Skip if it's not a line item
        }

        $product_id                 = $item->get_product_id();
        $variation_id               = $item->get_variation_id();
        $simple_preorder_enable     = get_post_meta( $product_id, '_upow_preorder_sample', true );
        $enable_preorder            = get_post_meta( $variation_id, '_upow_preorder_variable_product', true );

        if ($simple_preorder_enable === 'yes' || $enable_preorder === 'yes') {

            $pre_order_text = apply_filters('upow_order_details_pre_order_text', __('Pre-Order Product', 'ultimate-product-options-for-woocommerce'));
            echo wp_kses_post('<p style="font-weight: bold; color: orange;">' . $pre_order_text . '</p>');
        }
    }

    /**
     * Adds a custom column for pre-order dates to the WooCommerce admin products list.
     *
     * @param array $columns The existing columns in the products list.
     * @return array Modified columns with the new pre-order dates column.
     */
    public function add_preorder_date_column( $columns ) {

        $columns['preorder_dates'] = __('Pre-Order Dates', 'ultimate-product-options-for-woocommerce');
        return $columns;

    }

    /**
     * Populates the pre-order dates column in the WooCommerce admin products list for simple and variable products.
     *
     * @param string $column The name of the column being populated.
     * @param int    $product_id The ID of the product being processed.
     */
    public function populate_preorder_date_column( $column, $product_id ) {

        if ('preorder_dates' !== $column) {
            return;
        }
    
        $product = wc_get_product( $product_id );
        $preorder_dates = [];
    
        $product_type = $product->get_type();
        $variation_attr = [];
        if ( $product_type === 'simple' ) {
            $this->process_preorder_dates( $product_id, '_upow_preorder_sample', $preorder_dates, $product_type, $variation_attr  );

        } else if ($product_type === 'variable') {

            $variation_ids = $product->get_children();
            foreach ( $variation_ids as $variation_id ) {

                $product_variation = new \WC_Product_Variation( $variation_id );
				$variation_attr    = implode( " / ", $product_variation->get_variation_attributes() );
                $this->process_preorder_dates( $variation_id, '_upow_preorder_variable_product', $preorder_dates, $product_type, $variation_attr );
            }

        }
    
        // Output pre-order dates or an empty string
        !empty($preorder_dates) ? printf('%s', wp_kses_post(implode(', ', $preorder_dates))) : printf('%s', ' ');

    }
    
    /**
     * Processes and formats pre-order dates for a product, determining time until availability 
     * and appending formatted date information to the preorder_dates array based on product type.
     *
     * @param int    $product_id The ID of the product being processed.
     * @param string $meta_key The meta key to check for pre-order status.
     * @param array  &$preorder_dates Reference to the array where formatted pre-order date information is added.
     * @param string $product_type The type of product ('simple' or 'variable').
     * @param string $variation_attr Attributes for the product variation, if applicable.
     */
    private function process_preorder_dates( $product_id, $meta_key, &$preorder_dates, $product_type, $variation_attr ) {
       
        $preorder_enable = get_post_meta( $product_id, $meta_key, true );
    
        if ( $preorder_enable === 'yes' ) {

            // Retrieve and format the pre-order date
            $preorder_date = get_post_meta( $product_id, '_upow_preorder_availability_date', true );
            $quantity      = get_post_meta( $product_id, '_upow_preorder_available_quantity', true );

            if ( $preorder_date ) { 

                $today = strtotime('today');
                $preorder_timestamp = strtotime($preorder_date);

                $time_diff = abs($preorder_timestamp - $today);

                $days = floor($time_diff / (60 * 60 * 24)); 
                $hours = floor(($time_diff % (60 * 60 * 24)) / (60 * 60)); 
                $minutes = floor(($time_diff % (60 * 60)) / 60); 

                if ($days > 1) {
                    $time_difference = "{$days} days and {$hours} hours";
                } else {
                    $time_difference = "{$hours} hours and {$minutes} minutes";
                }

                $formatted_date = date('F j, Y h:i:sa', $preorder_timestamp);

                if ($product_type == 'simple') {
                    $preorder_dates[] = " ($time_difference)";
                } elseif ($product_type == 'variable') {
                    $preorder_dates[] = $variation_attr . " (In $time_difference)<br/>";
                }
            } else {
                if( $product_type == 'variable') {
                    $variation_attr_name = $variation_attr; 
                } else {
                    $variation_attr_name = ' '; 
                }

                $preorder_dates[] = $variation_attr_name . esc_html__("No date set","ultimate-product-options-for-woocommece");
            }
        }
    }

}