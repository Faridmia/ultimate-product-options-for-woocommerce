<?php

namespace Ultimate\Upow\Front\Options;

use Ultimate\Upow\Traitval\Traitval;

class Options
{
    use Traitval;

    /**
     * Constructor
     * 
     * The constructor adds an action to the 'admin_menu' hook to add custom submenus
     * to the WordPress admin menu.
     */
    public function __construct()
    {
        add_action('woocommerce_before_add_to_cart_button', array($this, 'upow_add_custom_fields_single_page'));
        add_filter('woocommerce_add_to_cart_validation', array($this, 'upow_validate_custom_fields'), 10, 3);
        add_filter('woocommerce_add_cart_item_data', array($this, 'upow_add_custom_fields_to_cart'), 10, 2);
    }


    /**
     * Adds custom fields to the WooCommerce product single page.
     *
     * This function hooks into 'woocommerce_before_add_to_cart_button' to display custom fields
     * on the product single page. These fields will appear before the "Add to cart" button.
     *
     * @since 1.0.0
     *
     * @return void
     */

     public function upow_add_custom_fields_single_page() {
        global $product;
        global $post;
    
        $args = array(
            'post_type'      => 'upow_product',
            'posts_per_page' => 10,
            'order'          => 'DESC',
            'orderby'        => 'date',
        );
    
        $custom_query = new \WP_Query($args);
        $global_product_id = get_the_ID();
        ob_start();
        $upow_product = get_post_meta($global_product_id, 'upow_product', true);
    
        $currency_position = get_option('woocommerce_currency_pos');
        $currency_symbol   = get_woocommerce_currency_symbol();
        $currency_left = '';
        $currency_right = '';
    
        switch ($currency_position) {
            case 'left':
                $currency_left = $currency_symbol;
                break;
            case 'right':
                $currency_right = $currency_symbol;
                break;
            case 'left_space':
                $currency_left = $currency_symbol . '&nbsp;';
                break;
            case 'right_space':
                $currency_right = '&nbsp;' . $currency_symbol;
                break;
            default:
                $currency_left = '';
                $currency_right = '';
        }
    
        ?>
        <div class="upow-extra-acc-wrap">
            <label for="iconic-engraving">
                <h2 class="upow-extra-wrap-title"><?php echo esc_html__('Extra Product Feature', 'ultimate-product-options-for-woocommerce'); ?></h2>
            </label>
            <?php
            $main_id = 2;
            $total_field_value  = get_option('upow_extra_fields_items', true);
            if (is_array($upow_product) || is_object($upow_product)) {
                foreach ($upow_product as $key => $values) {
                    $count = 1;
                    $field_type       = isset($values['field_type']) ? $values['field_type'] : '';
                    $field_label      = isset($values['field_label']) ? $values['field_label'] : '';
                    $default_value    = isset($values['default_value']) ? $values['default_value'] : '';
                    $required         = isset($values['required']) ? $values['required'] : '';
                    $placeholder_text = isset($values['placeholder_text']) ? $values['placeholder_text'] : '';
    
                    $required_check = '';
                    $label_after = '';
                    if ($required == 1) {
                        $required_check = 'required';
                        $label_after = '*';
                    }
    
                    if ($field_type == 'text' || $field_type == 'checkbox') {
                        $main_value = isset($total_field_value['upow_' . $main_id . "_" . $global_product_id]) 
                                      ? $total_field_value['upow_' . $main_id . "_" . $global_product_id] 
                                      : $default_value;
                        $select_value = isset($total_field_value['upow_' . $main_id . "_" . $global_product_id]);
                    }
    
                    if ($field_type == 'radio') {
                        $main_value = isset($total_field_value['upow_' . $main_id . "_" . $global_product_id]) 
                                      ? $total_field_value['upow_' . $main_id . "_" . $global_product_id] 
                                      : $default_value;
                        $select_value   = isset($total_field_value['upow_' . $main_id . "_" . $global_product_id]);
                        $checked        = (!empty($select_value)) ?  "checked" : "";
                    }
    
                    if ($field_type == 'checkbox') {
                        $checked = (!empty($select_value)) ?  "checked='checked'" : "";
                    }
    
                    if ($upow_product) { ?>
                        <input type="hidden" name="upow_item_label_text[]" value="<?php echo esc_attr($field_label); ?>" />
                        <div class="upow-extra-options <?php echo ($field_type == 'text') ? 'upow-input-field-group' : ''; ?>">
                            <label>
                                <?php if ($field_type == 'text') { ?>
                                    <span class="upow-item-label-text"><?php echo esc_html($field_label); ?><?php echo esc_html($label_after); ?></span>
                                    <input type="<?php echo esc_attr($field_type); ?>" name="upow_custom_field_items_data[upow_<?php echo esc_attr($main_id); ?>_<?php echo esc_attr($global_product_id); ?>]" value="<?php echo esc_attr($main_value); ?>" data-price="<?php echo esc_attr($main_value); ?>" placeholder="<?php echo esc_attr($placeholder_text); ?>" <?php echo esc_attr($required_check); ?>>
                                <?php } else { ?>
                                    <input type="<?php echo esc_attr($field_type); ?>" name="upow_custom_field_items_data[upow_<?php echo esc_attr($main_id); ?>_<?php echo esc_attr($global_product_id); ?>]" value="<?php echo esc_attr($main_value); ?>" data-price="<?php echo esc_attr($main_value); ?>" <?php echo esc_attr($checked); ?> <?php echo esc_attr($required_check); ?>> <?php echo esc_html($field_label); ?><?php echo esc_html($label_after); ?>
                                <?php } ?>
                            </label>
                            <?php if ($field_type != 'text') { ?>
                                <span class="upow-extra-item-price">
                                    <?php echo  wp_kses_post($currency_left . $default_value . $currency_right); ?>
                                </span>
                            <?php } ?>
                        </div>
                    <?php }
                    $main_id++;
                }
            }
            ?>
            <div class="upow-extra-acc">
                <?php
                if ($custom_query->have_posts()) :
                    $main_id = 1;
                    while ($custom_query->have_posts()) : $custom_query->the_post();
                        $product_id         = $post->ID;
                        $upow_product       = get_post_meta($product_id, 'upow_product', true);
                        $total_field_value  = get_option('upow_extra_fields_items', true);
                        $nonce = wp_create_nonce('upow_template_nonce');
                        ?>
                        <input type="hidden" name="upow_template_nonce" value="<?php echo esc_attr($nonce); ?>" />
                        <div class="upow-extra-acc-item">
                            <div class="upow-extra-title-tab">
                                <h3 class="title"><?php the_title(); ?><span class="icon"></span></h3>
                            </div>
                            <div class="upow-inner-content">
                                <?php
                                $count = 1;
                                if (is_array($upow_product) || is_object($upow_product)) {
                                    foreach ($upow_product as $key => $extra_item) {
                                        $field_type         = isset($extra_item['field_type']) ? $extra_item['field_type'] : '';
                                        $field_label        = isset($extra_item['field_label']) ? $extra_item['field_label'] : '';
                                        $default_value      = htmlspecialchars($extra_item['default_value'], ENT_QUOTES, 'UTF-8');
                                        $placeholder_text   = isset($extra_item['placeholder_text']) ? $extra_item['placeholder_text'] : '';
                                        $required           = isset($extra_item['required']) ? $extra_item['required'] : " ";
                                        $required_check = ($required == 1) ? 'required' : '';
                                        $label_after = ($required == 1) ? '*' : '';
    
                                        if ($field_type == 'text' || $field_type == 'checkbox') {
                                            $main_value = isset($total_field_value['upow_' . $count  . '_' . $main_id]) 
                                                          ? $total_field_value['upow_' . $count  . '_' . $main_id] 
                                                          : $default_value;
                                            $select_value = isset($total_field_value['upow_' . $count  . '_' . $main_id]);
                                        }
    
                                        if ($field_type == 'radio') {
                                            $main_value = isset($total_field_value['upow_' . $main_id]) 
                                                          ? $total_field_value['upow_' . $main_id] 
                                                          : $default_value;
                                            $select_value   = isset($total_field_value['upow_' . $main_id]);
                                            $checked        = (!empty($select_value)) ?  "checked" : "";
                                        }
    
                                        if ($field_type == 'checkbox') {
                                            $checked = (!empty($select_value)) ?  "checked='checked'" : "";
                                        }
                                        ?>
                                        <input type="hidden" name="upow_item_label_text[]" value="<?php echo esc_attr($field_label); ?>" />
                                        <div class="upow-extra-options <?php echo ($field_type == 'text') ? 'upow-input-field-group' : ''; ?>">
                                            <label>
                                                <?php if ($field_type == 'text') { ?>
                                                    <span class="upow-item-label-text"><?php echo esc_html($field_label); ?><?php echo esc_html($label_after); ?></span>
                                                    <input type="<?php echo esc_attr($field_type); ?>" name="upow_custom_field_items_data[upow_<?php echo esc_attr($count); ?>_<?php echo esc_attr($main_id); ?>]" value="<?php echo esc_attr($main_value); ?>" data-price="<?php echo esc_attr($main_value); ?>" placeholder="<?php echo esc_attr($placeholder_text); ?>" <?php echo esc_attr($required_check); ?>>
                                                <?php } else { ?>
                                                    <input type="<?php echo esc_attr($field_type); ?>" name="upow_custom_field_items_data[upow_<?php echo esc_attr($count); ?>_<?php echo esc_attr($main_id); ?>]" value="<?php echo esc_attr($main_value); ?>" data-price="<?php echo esc_attr($main_value); ?>" <?php echo esc_attr($checked); ?> <?php echo esc_attr($required_check); ?>> <?php echo esc_html($field_label); ?><?php echo esc_html($label_after); ?>
                                                <?php } ?>
                                            </label>
                                            <?php if ($field_type != 'text') { ?>
                                                <span class="upow-extra-item-price">
                                                    <?php echo wp_kses_post($currency_left . $default_value . $currency_right); ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                        <?php $count++;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php
                    $main_id++;
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo esc_html__('No posts found', 'ultimate-product-options-for-woocommerce');
                endif;
                ?>
                <div class="upow-extra-addons-pricing-info">
                    <div class="upow-options-total-prices">
                        <p>
                            <span class="upow-total-price-label"><?php esc_html_e('Options Amount:', 'ultimate-product-options-for-woocommerce'); ?></span>
                            <span class="upow-options-total-price">0</span>
                        </p>
                    </div>
                    <div class="upow-total-prices">
                        <p>
                            <span class="upow-total-price-label"><?php esc_html_e('Final Total:', 'ultimate-product-options-for-woocommerce'); ?></span>
                            <span class="upow-total-price"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    
        $content = ob_get_clean();
        printf('%s', do_shortcode($content));
    }
    

        public function upow_validate_custom_fields($passed, $product_id, $quantity)
        {

            if (!isset($_POST['upow_template_nonce']) || !wp_verify_nonce($_POST['upow_template_nonce'], 'upow_template_nonce')) {
                return;
            }

            if (isset($_POST['upow_custom_field_items_data']) && !empty($_POST['upow_custom_field_items_data'])) {
                $passed = true;
            }

            return $passed;
        }

        /**
         * Adds custom fields to cart item data.
         *
         * This function hooks into 'woocommerce_add_cart_item_data' to add custom field data
         * to the cart items. It checks if the custom field 'upow_custom_field_items_data' is set and adds it to the cart item data.
         *
         * @since 1.0.0
         *
         * @param array $cart_item_data The cart item data array.
         * @param int $product_id The ID of the product being added to the cart.
         * @return array Modified cart item data with custom fields.
         */
        public function upow_add_custom_fields_to_cart($cart_item_data, $product_id)
        {

            if (!isset($_POST['upow_template_nonce'])  && !wp_verify_nonce($_POST['upow_template_nonce'], 'upow_template_nonce')) {
                return;
            }

            if (isset($_POST['upow_custom_field_items_data'])) {

                $upow_item_label_text  = isset($_POST['upow_item_label_text']) ? wc_clean($_POST['upow_item_label_text']) : array();
                $extra_item_data        = array();
                $extra_item_prices      = 0;

                foreach ($_POST['upow_custom_field_items_data'] as $key => $value) {

                    if (is_array($value)) {
                        foreach ($value as $sub_value) {
                            $extra_item_prices += floatval($sub_value);
                            $extra_item_data[$key][] = wc_clean($sub_value);
                        }
                    } else {
                        $extra_item_prices += floatval($value);
                        $extra_item_data[$key] = wc_clean($value);
                    }
                }

                // Store custom field data in cart item
                $cart_item_data['upow_custom_field_items_data']        = $extra_item_data;
                $cart_item_data['upow_custom_field_items_data_price']  = $extra_item_prices;
                $cart_item_data['upow_item_label_text']                = $upow_item_label_text;

                // $sanitized_data = sanitize_upow_custom_field_items_data($cart_item_data['upow_custom_field_items_data']);
                update_option('upow_extra_fields_items', $cart_item_data['upow_custom_field_items_data']);
            }

            return $cart_item_data;
        }
    }
