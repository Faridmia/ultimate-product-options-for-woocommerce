<?php
namespace Ultimate\Upow\Front\Backorder;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class Backorder
{
    use Traitval;

    public $backorder_enable = '';

    public function __construct() {

        $this->backorder_enable = get_option('upow_backorder_on_off',true);

        if( $this->backorder_enable != '1') {
            return;
        }

        add_action( 'woocommerce_product_options_inventory_product_data', [$this,'add_custom_backorder_fields' ]);
        add_action( 'woocommerce_admin_process_product_object', [$this,'save_custom_backorder_fields'] );
        add_filter( 'woocommerce_get_availability_text', [$this,'filter_product_availability_text'], 10, 2 );
        add_filter( 'woocommerce_get_item_data', [ $this, 'render_backorder_availability_cart_page' ], 99, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this,'check_backorder_limit'], 55, 4 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this,'save_backorder_quantity_meta'], 10, 4 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this,'prevent_adding_to_cart_if_backorder_limit_exceeded'], 10, 2 );
        add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'customize_order_item_meta_display' ], 10, 2 );
        add_action( 'woocommerce_order_item_meta_end', [ $this, 'display_backorder_notification' ], 10, 4 );
        add_action( 'woocommerce_admin_order_item_headers', [ $this,'add_custom_admin_order_header'], 10, 1 );
        add_action( 'woocommerce_admin_order_item_values', [ $this,'display_backorder_notification_in_admin_order'], 10, 3 );

    }

    // Add custom fields to the Inventory tab
    function add_custom_backorder_fields() { ?>
        <div class="options_group upow-backorder-information">
        <h3 class="upow-backorder-heading"><?php echo esc_html__( "Shopmaster Backorder Information", 'ultimate-product-options-for-woocommerce' );?></h3>
        <?php 
        // Available Quantity (Number Field)
        woocommerce_wp_text_input( array(
            'id'          => '_upow_available_quantity',
            'label'       => __( 'Backorder Limit', 'ultimate-product-options-for-woocommerce' ),
            'desc_tip'    => 'true',
            'description' => __( 'Enter the quantity available for backorder.', 'ultimate-product-options-for-woocommerce' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'min' => '0',
                'step' => '1',
            ),
        ));
        
        // Availability Date (Date Field)
        woocommerce_wp_text_input( array(
            'id'          => '_upow_availability_date',
            'label'       => __( 'Availability Date', 'ultimate-product-options-for-woocommerce' ),
            'desc_tip'    => 'true',
            'description' => __( 'Select the date the product will be available.', 'ultimate-product-options-for-woocommerce' ),
            'type'        => 'date',
        ));

        // Availability Message (Text Field)
        woocommerce_wp_text_input( array(
            'id'          => '_upow_availability_message',
            'label'       => __( 'Availability Message', 'ultimate-product-options-for-woocommerce' ),
            'default'     => __("On Backorder: Will be available on",'ultimate-product-options-for-woocommerce'),
            'desc_tip'    => 'true',
            'description' => __( 'Enter a custom message regarding availability.', 'ultimate-product-options-for-woocommerce' ),
            'type'        => 'text',
        ));
        ?>
        </div>
        <?php
    }

    public function customize_order_item_meta_display( $formatted_meta, $item ) {
        
        foreach ( $formatted_meta as $key => $meta ) {
            if ( 'upow_backordered' === $meta->key ) {
                // Update the display key for backordered items
                $meta->display_key = esc_html__( 'Backordered', 'ultimate-product-options-for-woocommerce' );
            }
        }
    
        return $formatted_meta;
    }

    // Save custom fields
    function save_custom_backorder_fields( $product ) {
        // Save Available Quantity
        if ( isset( $_POST['_upow_available_quantity'] ) ) {
            $product->update_meta_data( '_upow_available_quantity', sanitize_text_field( $_POST['_upow_available_quantity'] ) );
        }

        // Save Availability Date
        if ( isset( $_POST['_upow_availability_date'] ) ) {
            $product->update_meta_data( '_upow_availability_date', sanitize_text_field( $_POST['_upow_availability_date'] ) );
        }

        // Save Availability Message
        if ( isset( $_POST['_upow_availability_message'] ) ) {
            $product->update_meta_data( '_upow_availability_message', sanitize_text_field( $_POST['_upow_availability_message'] ) );
        }
    }

    
    function filter_product_availability_text( $availability_text, $product ) {

        $availability_date    = $product->get_meta( '_upow_availability_date' );
        $availability_message = $product->get_meta( '_upow_availability_message' );
        $_upow_available_quantity = $product->get_meta( '_upow_available_quantity' );
        // Check if product status is on backorder
        if ( ! empty( $availability_date ) ) {
            if ($product->get_stock_status() === 'onbackorder') {
                $availability_text = esc_html(  $availability_message ) . " " . esc_html( date( 'F j, Y', strtotime( $availability_date ) ) );
            }
        }

        return $availability_text;

    }

    public function render_backorder_availability_cart_page( $item_data, $cart_item ) {
        
        $product_data = $cart_item['data'];
        $product_id = $product_data->get_id();

        if ( $product_data->is_type('variation') ) {
            $product_id = $product_data->get_parent_id();
        }

        if ( $product_data->is_on_backorder() ) {
            $availability_message = $this->get_availability_message( $product_id );

            if ( $availability_message ) {
                $item_data[] = array(
                    'name' => __( 'upow_backorder_availability', 'ultimate-product-options-for-woocommerce' ),
                    'value' => wp_kses_post( $availability_message ),
                    'display' => '',
                );
            }
        }

        return $item_data;
    }

    /**
     * Generate and return backorder availability message using custom meta fields
     */
    public function get_availability_message( $product_id ){
        // Get the product object
        $product = wc_get_product( $product_id );

        $availability_date = $product->get_meta( '_upow_availability_date' );
        
        $timestamp = strtotime($availability_date);
        if($timestamp){
            $availability_date = gmdate(get_option('date_format'), $timestamp); 
        }

        $_upow_available_quantity = $product->get_meta( '_upow_available_quantity' );
        $availability_message = $product->get_meta( '_upow_availability_message' );

        $availability_message = esc_html(  $availability_message ) . " " . esc_html( date( 'F j, Y', strtotime( $availability_date ) ) );

        if( $_upow_available_quantity && $availability_date && empty($availability_message) ){
            $availability_message = __( 'On Backorder. Will be available on: '. $availability_date, 'ultimate-product-options-for-woocommerce' );
        }

        return $availability_message;
    }

    public function get_total_qty_already_backordered( $product_id = '', $variation_id = '' ) {
        global $wpdb;
    
        // Ensure product and variation IDs are valid integers
        $product_id   = absint( $product_id );
        $variation_id = absint( $variation_id );
        $p_id         = $variation_id ? $variation_id : $product_id;
    
        $total_backordered_qty = 0;
    
        // Query to fetch sum of backordered quantities from order items
        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $posts_table = $wpdb->prefix . 'posts';

        // Create the SQL query with placeholders
        $query = "
            SELECT SUM(meta_qty.meta_value) AS backorder_qty
            FROM $order_items_table AS order_items
            JOIN $order_itemmeta_table AS meta_qty ON order_items.order_item_id = meta_qty.order_item_id
            JOIN $posts_table AS orders ON orders.ID = order_items.order_id
            WHERE meta_qty.meta_key = '_qty'
            AND order_items.order_item_type = 'line_item'
            AND order_items.order_item_id IN (
                SELECT meta_product.order_item_id
                FROM $order_itemmeta_table AS meta_product
                WHERE meta_product.meta_key IN ( '_variation_id', '_product_id' )
                AND meta_product.meta_value = %d
            )
            AND orders.post_status IN ( 'wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending' )
        ";

        // Prepare and execute the SQL query
        $prepared_query = $wpdb->prepare( $query, $p_id );
        $result = $wpdb->get_var( $prepared_query );
    
        // Add the result to the total backordered quantity
        $total_backordered_qty += absint( $result );
    
        // Fetch recent orders and loop through each order item to check for backorder metadata
        $order_query = new \WC_Order_Query( array(
            'status'  => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed'),
            'limit'   => apply_filters( 'upow_backorder_query_limit', 200 ),
            'orderby' => 'date',
            'order'   => 'DESC',
        ) );
    
        $orders = $order_query->get_orders();
    
        foreach ( $orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $item_product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
    
                if ( $p_id == $item_product_id ) {
                    $backorder_meta_qty = 0;
    
                    // Check if custom backorder meta exists and add its quantity
                    if ( $item->meta_exists( 'upow_backordered' ) ) {
                        $backorder_meta_qty = (int) $item->get_meta( 'upow_backordered' );
                    }
    
                    if ( $backorder_meta_qty > 0 ) {
                        $total_backordered_qty += $backorder_meta_qty;
                        break;
                    }
                }
            }
        }
    
        return $total_backordered_qty;
    }
    
    public function check_backorder_limit( $passed, $product_id, $quantity, $variation_id = '' ) {
        // Get the product object
        $product = wc_get_product( $product_id );
        
        if ( $product->is_on_backorder( $quantity ) ) {
            $backorder_limit = $product->get_meta( '_upow_available_quantity' );

            if ( ! empty( $backorder_limit ) && $backorder_limit > 0 ) {
                $backorder_quantity_in_cart = 0;
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    if ( $cart_item['product_id'] == $product_id && $product->is_on_backorder( $cart_item['quantity'] ) ) {
                        $backorder_quantity_in_cart += $cart_item['quantity'];
                    }
                }

                $total_backorder_quantity = $backorder_quantity_in_cart + $quantity;

                // Check if the total exceeds the limit
                if ( $total_backorder_quantity > $backorder_limit ) {
                    wc_add_notice( sprintf( __( 'You cannot backorder more than %d of this item.', 'ultimate-product-options-for-woocommerce' ), $backorder_limit ), 'error' );
                    return false;
                }
            }
        }

        return $passed;
    }
    
    public function save_backorder_quantity_meta( $item, $cart_item_key, $values, $order ) {
        if ( $values['quantity'] > $values['data']->get_stock_quantity() ) {
            $backordered_qty = $values['quantity'] - $values['data']->get_stock_quantity();
            $item->add_meta_data( 'upow_backordered', $backordered_qty, true );
        }
    }

    public function is_backorder_limit_exceeded( $product, $product_id, $variation_id = '' ) {

        if ( $product->is_on_backorder() ) {
            $backorder_limit = $product->get_meta( '_upow_available_quantity' );
            $already_backordered_qty = $this->get_total_qty_already_backordered( $product_id, $variation_id );
        
            if ( $already_backordered_qty >= $backorder_limit ) {
                return true;
            }
        }
    
        return false;
    }

    
    public function prevent_adding_to_cart_if_backorder_limit_exceeded( $passed, $product_id ) {

        $product = wc_get_product( $product_id );
        if ( $product->is_on_backorder() ) {
            if ( $product->get_stock_quantity() <= 0 ) {
                $variation_id = ''; // or set it if you're dealing with variations
                if ( $this->is_backorder_limit_exceeded( $product, $product_id, $variation_id ) ) {
                    wc_add_notice( __( 'Backorder limit exceeded for this product!', 'ultimate-product-options-for-woocommerce' ), 'error' );
                    return false;
                }
            }
        }
        
        return $passed;
    }

    public function display_backorder_notification( $item_id, $item, $order, $plain_text ) {

        // Get the product associated with the order item
        $product = $item->get_product();
        $product_id = $product->get_id();
    
        // For variable products, get the parent product ID
        if ( $product->is_type( 'variation' ) ) {
            $product_id = $product->get_parent_id();
        }
    
        // Check if the product is currently on backorder
        if ( $product->is_on_backorder() ) {
            printf(
                '<p class="upow-backorder-notification">%s</p>',
                wp_kses_post( $this->get_availability_message( $product_id ) )
            );
        }
    }

    public function add_custom_admin_order_header( $order ) {
        // Loop through the order items to find the product
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            if ( $product ) {
                $availability_date = $product->get_meta( '_upow_availability_date' );
                $today = strtotime('today');
    
                if ( $availability_date && strtotime( $availability_date ) >= $today ) {
                    printf(
                        '<th class="upow-backorder-column-header">%s</th>',
                        esc_html__( 'Backorder Info', 'ultimate-product-options-for-woocommerce' )
                    );
                    break; // We found the product and added the header, no need to continue looping
                }
            }
        }
    }

    public function display_backorder_notification_in_admin_order( $product, $item, $item_id ) {
        $availability_date = $product->get_meta( '_upow_availability_date' );
        $today = strtotime('today');

        if ( $availability_date && strtotime( $availability_date ) >= $today ) {
            // Initialize the backorder column content
            $backorder_column = '<td class="upow-backorder-column">';
        
            // Check if the product is on backorder
            if ( $product->is_on_backorder() ) {
                $backorder_column .= '<p class="upow-backorder-notification">';
                $backorder_column .= wp_kses_post( $this->get_availability_message( $product->get_id() ) );
                $backorder_column .= '</p>';
            } else {
                $backorder_column .= '-';
            }
        
            // Close the table cell
            $backorder_column .= '</td>';
        
            // Output the final HTML
            echo wp_kses_post($backorder_column);
        }
    }
}