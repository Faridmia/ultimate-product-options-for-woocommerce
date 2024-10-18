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

    public function __construct() {

       
        $this->preorder_enable = get_option('upow_preorder_on_off',true);

        if( $this->preorder_enable != '1') {
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

        $this->preorder_price = new PriceOverride();
    }


    /**
     * Enqueue frontend assets for preorder
     *
     * This function enqueues the necessary CSS and JavaScript files for the swatch functionality on the frontend.
     *
     * @return void
     */
    public function upow_enqueue_preorder_frontend_assets() {

        wp_enqueue_style('upow-preorder-front-css', UPOW_CORE_ASSETS . 'src/Front/Preorder/assets/css/preorder-front.css', array(), UPOW_VERSION);
        wp_enqueue_script('upow-preorder-front-js', UPOW_CORE_ASSETS . 'src/Front/Preorder/assets/js/preorder-frontend.js', array('jquery'), time(), true);

        if (is_product()) {
            wp_localize_script('upow-preorder-front-js', 'preorder_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'preordrDefaultAddToCartText' => __( 'Add to cart', 'ultimate-product-options-for-woocommerce' )
            ));
        }
        
    }

    
    function upow_display_preorder_info( $product ) {
    
        if ( !$product || ! $product->get_id() ) {
            return; // Ensure product object is available
        }
    
        // Fetch all the meta data at once to avoid multiple database calls
        $preorder_data = [
            'pre_release_message' => get_post_meta($product->get_id(), '_upow_preorder_pre_released_message', true),
            'preorder_limit' => get_post_meta($product->get_id(), '_upow_preorder_available_quantity', true),
            'availability_date' => get_post_meta($product->get_id(), '_upow_preorder_availability_date', true),
            'availability_message' => get_post_meta($product->get_id(), '_upow_preorder_availability_message', true),
            'available_quantity' => get_post_meta($product->get_id(), '_upow_preorder_available_quantity', true),
        ];
    
        $availability_message = '';
    
        if ( !empty( $preorder_data['available_quantity'] ) && !empty( $preorder_data['availability_date'] ) && !empty( $preorder_data['availability_message'] ) ) {
            $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
            $availability_message .= '<p class="preorder-availability-message">' . esc_html( $preorder_data['availability_message'] ) . ' ' . esc_html( $formatted_date ) . '</p>';
        }
    
        if ( empty( $availability_message ) && !empty( $preorder_data['pre_release_message'] ) ) {
            $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
        }


        if ( !is_checkout() && !is_cart()  && !empty( $preorder_data['preorder_limit'] ) ) { 

            $availability_message .= '<p class="preorder-limit">' . esc_html__('Limited to:', 'ultimate-product-options-for-woocommerce') . ' ' . esc_html( $preorder_data['preorder_limit'] ) . '</p>';

        }
    
        // Return the final message
        return wp_kses_post( $availability_message );
    }

    public function upow_display_preorder_info_output() {

        global $product;
        echo wp_kses_post( $this->upow_display_preorder_info( $product ) ); // Echo the returned message
    }
    

    public function upow_add_preorder_data_to_variations( $variation_data, $product, $variation ) {
            
        if ( !$product || ! $variation->get_id() ) {
            return; 
        }

        
        if ( $product->is_type( 'variable' ) ) {
            $preorder_status = get_post_meta( $variation->get_id(), '_upow_preorder_variable_product', true );
            
            if ( $preorder_status === 'yes' ) {
                $variation_data['preorder_label'] = get_option( 'upow_preorder_addto_cart_text', 'Preorder Now' );
            } else {
                $variation_data['preorder_label'] = ''; 
            }
        }
    
        return $variation_data;
    }
    
    public function upow_add_preorder_to_variations_data( $variation_data, $product, $variation ) {

        if ( $product->is_type( 'variable' ) ) {
            
            $preorder_status = get_post_meta( $variation->get_id(), '_upow_preorder_variable_product', true );
            // Set a label text if preorder is enabled
            if ( $preorder_status === 'yes' ) {

                $variation_id = $variation->get_id();
            
                if ( !$product || ! $variation->get_id() ) {
                    return; // Ensure product object is available
                }
            
                // Fetch all the meta data at once to avoid multiple database calls
                $preorder_data = [
                    'pre_release_message'   => get_post_meta( $variation_id, '_upow_preorder_pre_released_message', true),
                    'preorder_limit'        => get_post_meta( $variation_id, '_upow_preorder_available_quantity', true),
                    'availability_date'     => get_post_meta( $variation_id, '_upow_preorder_availability_date', true),
                    'availability_message'  => get_post_meta( $variation_id, '_upow_preorder_availability_message', true),
                    'available_quantity'    => get_post_meta( $variation_id, '_upow_preorder_available_quantity', true),
                ];
            
                // Initialize message variable
                $availability_message = '';
            
                // Check if both availability date and message are available
                if ( !empty( $preorder_data['available_quantity'] ) && !empty( $preorder_data['availability_date'] ) && !empty( $preorder_data['availability_message'] ) ) {
                    $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
                    $availability_message .= '<p class="preorder-availability-message">' . esc_html( $preorder_data['availability_message'] ) . ' ' . esc_html( $formatted_date ) . '</p>';
                }
            
                if ( empty( $availability_message ) && !empty( $preorder_data['pre_release_message'] ) ) {
                    $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
                }
            
                if ( !empty( $preorder_data['preorder_limit'] ) ) {
                    $availability_message .= '<p class="preorder-limit">' . esc_html__('Limited to:', 'ultimate-product-options-for-woocommerce') . ' ' . esc_html( $preorder_data['preorder_limit'] ) . '</p>';
                }
            
                // Output the final message
                $variation_data['variation_description'] .= $availability_message . '<br>';
            }
        }
    
        return $variation_data;
    }
    
    
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

    public function display_preorder_notification( $item_id, $item, $order, $plain_text ) {

        $product = $item->get_product();
        $product_id = $product->get_id();

        $simple_preorder_enable  = get_post_meta( $product_id, '_upow_preorder_sample', true );
        $enable_preorder         = get_post_meta( $product_id, '_upow_preorder_variable_product', true );
    
        // Check if the product is currently on preorder
        if  ( $enable_preorder === 'yes' || $simple_preorder_enable == 'yes' ) {
            
            if ( $product->is_type( 'simple' ) ) {
                echo  wp_kses_post( $this->upow_display_preorder_info( $product ) );
            } elseif( $product->is_type( 'variation' ) ) {
                
                $preorder_data = $item->get_meta('preorder_data', true);

                // Check if preorder data exists
                if ( ! empty( $preorder_data ) ) {
                   
                    $availability_message = '';

                    if ( ! empty( $preorder_data['availability_date'] ) && ! empty( $preorder_data['availability_message'] ) ) {
                        $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
                        $availability_message .= '<p class="preorder-availability-message">' . esc_html( $preorder_data['availability_message'] ) . ' ' . esc_html( $formatted_date ) . '</p>';
                    }

                    if ( empty( $availability_message ) && ! empty( $preorder_data['pre_release_message'] ) ) {
                        $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
                    }

                    echo  wp_kses_post( $availability_message );
                }

            }
        }
    }

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

    
    function add_preorder_data_to_order_item( $item, $cart_item_key, $values, $order ) {
        if ( ! empty( $values['preorder_data'] ) ) {
            // Save preorder data to the order item
            $item->add_meta_data( 'preorder_data', $values['preorder_data'], true );
        }
    }

    public function display_preorder_notification_in_admin_order( $product, $item, $item_id ) {

        $availability_date = $product->get_meta( '_upow_preorder_availability_date' );
        $today = strtotime('today');
        $product_id = $product->get_id();

        $simple_preorder_enable  = get_post_meta( $product_id, '_upow_preorder_sample', true );
        $enable_preorder         = get_post_meta( $product_id, '_upow_preorder_variable_product', true );

        $preorder_column = '<td class="upow-preorder-column">';
    
        if  ( $enable_preorder === 'yes' || $simple_preorder_enable == 'yes' ) {

            if ( $product->is_type( 'simple' ) ) {
                $preorder_column .= $this->upow_display_preorder_info( $product );
            } elseif( $product->is_type( 'variation' ) ) {

                $preorder_data = $item->get_meta('preorder_data', true);

                // Check if preorder data exists
                if ( ! empty( $preorder_data ) ) {
                    // Initialize message variable
                    $availability_message = '';

                    if ( ! empty( $preorder_data['availability_date'] ) && ! empty( $preorder_data['availability_message'] ) ) {
                        $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
                        $availability_message .= '<p class="preorder-availability-message">' . esc_html( $preorder_data['availability_message'] ) . ' ' . esc_html( $formatted_date ) . '</p>';
                    }

                    if ( empty( $availability_message ) && ! empty( $preorder_data['pre_release_message'] ) ) {
                        $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
                    }

                    $preorder_column .= $availability_message;
                }
            }
            
        } else {
            $preorder_column .= '-';
        }
    
        $preorder_column .= '</td>';
    
        // Output the final HTML
        echo wp_kses_post( $preorder_column );
       

    }


    // Add custom preorder data to WooCommerce cart item data
    public function render_preorder_availability_cart_page( $item_data, $cart_item ) {
        if ( ! empty( $cart_item['preorder_data'] ) ) {
            $preorder_data = $cart_item['preorder_data'];

            $availability_message = '';

            if ( !empty( $preorder_data['availability_date'] ) && !empty( $preorder_data['availability_message'] ) ) {
                $formatted_date = date( 'F j, Y h:i:sa', strtotime( $preorder_data['availability_date'] ) );
                $availability_message .= '<p class="preorder-availability-message">' . esc_html( $preorder_data['availability_message'] ) . ' ' . esc_html( $formatted_date ) . '</p>';
            }

            if ( empty( $availability_message ) && !empty( $preorder_data['pre_release_message'] ) ) {
                $availability_message .= '<p class="preorder-pre-release-message">' . esc_html( $preorder_data['pre_release_message'] ) . '</p>';
            }

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
                ];

                // Save preorder data to cart item
                $cart_item_data['preorder_data'] = $preorder_data;
            }
        }

        return $cart_item_data;

    }

}