<?php

/**
 * Filter the allowed HTML tags for a specific context.
 *
 * This function extends the list of allowed HTML tags and attributes for specific contexts
 * using the `wp_kses` function. The contexts can be 'upow_kses' for general HTML content
 * or 'upow_img' for image-specific tags.
 *
 * @param array  $upow_tags    The default allowed HTML tags and attributes.
 * @param string $upow_context The context in which the HTML is being filtered.
 * @return array The modified list of allowed HTML tags and attributes.
 *
 * @since 1.0.0
 */
function upow_kses_allowed_html($upow_tags, $upow_context)
{
    switch ($upow_context) {
        case 'upow_kses':
            $upow_tags = array(
                'div'    => array(
                    'class' => array(),
                ),
                'ul'     => array(
                    'class' => array(),
                ),
                'li'     => array(),
                'span'   => array(
                    'class' => array(),
                ),
                'a'      => array(
                    'href'  => array(),
                    'class' => array(),
                ),
                'i'      => array(
                    'class' => array(),
                ),
                'p'      => array(),
                'em'     => array(),
                'br'     => array(),
                'strong' => array(),
                'h1'     => array(),
                'h2'     => array(),
                'h3'     => array(),
                'h4'     => array(),
                'h5'     => array(),
                'h6'     => array(),
                'del'    => array(),
                'ins'    => array(),
            );
            return $upow_tags;
        case 'upow_img':
            $upow_tags = array(
                'img' => array(
                    'class'  => array(),
                    'height' => array(),
                    'width'  => array(),
                    'src'    => array(),
                    'alt'    => array(),
                ),
            );
            return $upow_tags;
        default:
            return $upow_tags;
    }
}


function sanitize_upow_custom_field_items_data($data)
{
    $sanitized_data = array();

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $sanitized_key = sanitize_key($key);

            // Check if $value is an array before using array_map
            if (is_array($value)) {
                $sanitized_value = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_value = sanitize_text_field($value);
            }

            $sanitized_data[$sanitized_key] = $sanitized_value;
        }
    } else {
        // Sanitize non-array data
        $sanitized_data = sanitize_text_field($data);
    }

    return $sanitized_data;
}


// product per page
function upow_product_per_page( $products ) {
    $upow_product_per_page  = '';
    if (!empty(get_option('upow_product_per_page'))) {
        $products = get_option('upow_product_per_page');
    }
    
    return $products;
}

add_filter( 'loop_shop_per_page', "upow_product_per_page"  , 30 );

/** search panel initial output and after widget save this output will show  */
function upow_all_product_panel_output($select_all_product = [] ) {
    
    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );

    
    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Product</option>';
    if ($product_query->have_posts()) :
        $search_item_name = 'album';
        $count = 0;
        while ($product_query->have_posts()) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = " ";
            if( is_array( $select_all_product ) || is_object( $select_all_product ) ) {
                foreach( $select_all_product as $key => $value ) {
                    $get_product =  $select_all_product[$key];
                    if ( $get_product == $product_id  ) {
                        $select_product = "selected='selected'";
                    } 
                }
            }
            
           $product_output .= '<option '.$select_product.' data-item="'.$product_id.'" value="'.$product_id.'">'.get_the_title().'</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif; 
   
    return $product_output;
}

/**
 * All exclude product functions
 */

 function upow_exclude_product_panel_output( $upow_exclude_product = [] ) {
    
    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );

    
    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Product</option>';

    if ( $product_query->have_posts() ) :

        $search_item_name = 'album';
        $count = 0;
        
        while ( $product_query->have_posts() ) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = " ";
            if( is_array( $upow_exclude_product ) || is_object( $upow_exclude_product ) ) {
                foreach( $upow_exclude_product as $key => $value ) {
                    $get_product =  $upow_exclude_product[$key];
                    if ( $get_product == $product_id  ) {
                        $select_product = "selected='selected'";
                    } 
                }
            }
            
           $product_output .= '<option '.$select_product.' data-item="'.$product_id.'" value="'.$product_id.'">'.get_the_title().'</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif; 
   
    return $product_output;
}

/**
 * get all product categories
 */

function upow_get_product_categories( $product_ids = [] ) {

    $options = array();
    $taxonomy = 'product_cat';
    $category_output = '';

    if (!empty($taxonomy)) {
        $terms = get_terms(
                array(
                    'parent' => 0,
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                )
        );

        $category_output .=  '<option  data-item="" value="empty">Select Categories</option>';

        if ( !empty( $terms ) ) {
            foreach ( $terms as $index => $term ) {
                if ( isset( $term ) ) {
                    $options[''] = 'Select';
                    $select_product = ' ';
                    // Get the option and check if it is an array or object
                   
                    if ( $product_ids ) {
                        if ( is_array($product_ids ) || is_object( $product_ids ) ) {
                            foreach ($product_ids as $key => $value) {
                                $get_category = $value; // Retrieve the value correctly
                                if ( $get_category == $term->term_id ) {
                                    $select_product = "selected='selected'";
                                    break; // Exit the loop once a match is found
                                }
                            }
                        }
                    }
                    if ( isset($term->slug ) && isset( $term->name ) ) {
                        $category_output .= '<option  '.$select_product.' data-item="'.$term->term_id.'" value="'.$term->term_id.'">'.$term->name.'</option>';
                    }
                }
                
            }
        }
    }

    return $category_output;
}

/**
 * Custom function to retrieve an option with a default value.
 *
 * @param string $option_name The name of the option to retrieve.
 * @param mixed $default The default value to return if the option does not exist.
 * @return mixed The value of the option, or the default value if the option does not exist.
 */
function get_option_upow( $option_name, $default = false ) {
    // Check if the option exists
    $option_value = get_option($option_name, $default);
    
    // If the option does not exist, return the default value
    if ($option_value === false) {
        return $default;
    }

    return $option_value;
}

add_filter('woocommerce_loop_add_to_cart_link', 'upow_custom_cart_hook', 10, 3);

function upow_custom_cart_hook( $cart_html, $product, $args ) {
    $before = $after = '';

    $cart_before_filter_top   = apply_filters("upow_top_before_cart",  $content = '', $product, $args );
    $cart_after_filter_bottom = apply_filters("upow_bottom_after_cart", $content = '', $product, $args );

    if( $cart_before_filter_top ) {
        $before .= '<div class="upow-shop-before-title">';
        $before .= $cart_before_filter_top;
        $before .= '</div>';
    }

    if( $cart_after_filter_bottom ) {
        $after .= '<div class="upow-shop-after-title">';
        $after .= $cart_after_filter_bottom;
        $after .= '</div>';
    }

    return $before . $cart_html . $after;
}


function upow_change_add_to_cart_text( $text, $product ) {
    // Get the custom Add to Cart texts for both backorder and preorder
    $upow_backorder_addto_cart_text = get_option( 'upow_backorder_addto_cart_text', true );
    $upow_preorder_addto_cart_text  = get_option( 'upow_preorder_addto_cart_text', true );
    
    // Check if the product is on backorder
    if ( $product->is_on_backorder( 1 ) ) {
        return $upow_backorder_addto_cart_text;
    }
    
    // Check if the product is on preorder
    $is_preorder = get_post_meta( $product->get_id(), '_upow_preorder_sample', true );
    if ( $is_preorder == 'yes' ) {
        return $upow_preorder_addto_cart_text;
    }
    
    // Default to 'Add to cart'
    return __( 'Add to cart', 'ultimate-product-options-for-woocommerce' );
}

// Apply the combined function to both single product and shop page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'upow_change_add_to_cart_text', 20, 2 );
add_filter( 'woocommerce_product_add_to_cart_text', 'upow_change_add_to_cart_text', 20, 2 );
