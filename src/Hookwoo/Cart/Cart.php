<?php

namespace Ultimate\Upow\Hookwoo\Cart;

use Ultimate\Upow\Traitval\Traitval;

class Cart
{
    use Traitval;

    /**
     * Constructor
     * 
     * The constructor adds actions and filters to handle custom field functionality
     * in WooCommerce, such as adjusting cart item prices and displaying custom fields in the cart.
     */
    public function __construct()
    {
        add_action('woocommerce_before_calculate_totals', array($this, 'upow_adjust_cart_item_price'), 10, 1);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'upow_get_cart_items_from_session'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'upow_display_custom_fields_in_cart'), 10, 2);
    }


    /**
     * Calculate the item price including the extra item price.
     *
     * This function hooks into 'woocommerce_get_cart_item_from_session' to retrieve and set custom field data from session.
     * It adds the extra item price to the cart item price.
     *
     * @since 1.0.0
     *
     * @param array $cart_item The cart item array.
     * @param array $values The cart item values from the session.
     * @return array Modified cart item with extra item price included.
     */

    function upow_get_cart_items_from_session($cart_item, $values)
    {
        if (isset($values['upow_custom_field_items_data_price'])) {
            $cart_item['upow_custom_field_items_data_price'] = $values['upow_custom_field_items_data_price'];
        }

        return $cart_item;
    }

    /**
     * Display custom fields in the cart item data.
     *
     * This function hooks into 'woocommerce_get_item_data' to add custom fields
     * data to be displayed in the cart.
     *
     * @since 1.0.0
     *
     * @param array $item_data The cart item data array.
     * @param array $cart_item The cart item data.
     * @return array Modified cart item data array with additional custom fields.
     */

    function upow_display_custom_fields_in_cart($item_data, $cart_item)
    {

        if (isset($cart_item['upow_custom_field_items_data']) || isset($cart_item['upow_item_label_text'])) {

            $item_label_text   = $cart_item['upow_item_label_text'];
            $currency_position = get_option('woocommerce_currency_pos');
            $currency_symbol   = get_woocommerce_currency_symbol();
            // Initialize currency position variables
            $currency_left  = '';
            $currency_right = '';

            // Determine the position of the currency symbol
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

            $count = 0;

            foreach ($cart_item['upow_custom_field_items_data'] as $key => $values) {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        $item_data[] = array(
                            'name' => wc_clean($item_label_text[$count]),
                            'value' => $currency_left . wc_clean(number_format((float)$value, 2, '.', '')) . $currency_right
                        );
                    }
                } else {
                    $item_data[] = array(
                        'name' => wc_clean($item_label_text[$count]),
                        'value' => $currency_left . wc_clean(number_format((float)$values, 2, '.', '')) . $currency_right
                    );
                }
                $count++;
            }
        }

        return $item_data;
    }

    /**
     * Update the cart item price with the extra item price.
     *
     * This function hooks into 'woocommerce_before_calculate_totals' to update the cart item price 
     * with the custom extra item price before calculating the cart totals.
     *
     * @since 1.0.0
     *
     * @param WC_Cart $cart The cart object.
     * @return void
     */

    public function upow_adjust_cart_item_price($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['upow_custom_field_items_data_price'])) {
                $extra_price = $cart_item['upow_custom_field_items_data_price'];

                // Check if the product has a sale price
                $product = $cart_item['data'];
                $base_price = $product->is_on_sale() ? $product->get_sale_price() : $product->get_regular_price();

                // Set the adjusted price
                $cart_item['data']->set_price($base_price + $extra_price);
            }
        }
    }
}
