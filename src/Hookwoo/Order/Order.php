<?php
namespace Ultimate\Upow\Hookwoo\Order;
use Ultimate\Upow\Traitval\Traitval;

class Order
{
    use Traitval;

    /**
     * Class Constructor
     *
     * Adds actions and filters to modify WooCommerce order admin views and order item metadata.
     */
    public function __construct()
    {
        $this->set_currency_position();
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'upow_display_price_field_in_admin_order'), 10, 1);
        add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'upow_remove_meta_from_order'), 10, 2);
        add_action('woocommerce_after_order_itemmeta', array($this, 'upow_display_field_below_product_title'), 10, 3);
        add_action('woocommerce_order_item_meta_end', array($this, 'upow_display_custom_fields_order_details'), 10, 4);
        add_action('woocommerce_add_order_item_meta', array($this, 'upow_save_custom_fields_order_meta'), 10, 2);
    }

    /**
     * Display custom fields in the admin order page.
     *
     * This function hooks into 'woocommerce_admin_order_data_after_order_details' to display custom fields
     * in the admin order page for each order item.
     *
     * @since 1.0.0
     *
     * @param WC_Order $order The order object.
     * @return void
     */
    function upow_display_price_field_in_admin_order($order)
    {
        // Get the custom field value
        $order_id = $order->get_id();
        $items = $order->get_items();

        // Initialize custom price variable
        $custom_price = 0;

        foreach ($items as $item_id => $item) {
            $custom_field_value = wc_get_order_item_meta($item_id, 'upow_custom_field_items_data_price', true);

            if ($custom_field_value) {
                $custom_price += floatval($custom_field_value);
            }
        }

        // Display the custom price field
        $price_field_markup = '<div class="order_data_column">';
        $price_field_markup .= '<h4>' . __('Custom Price', 'ultimate-product-options-for-woocommerce') . '</h4>';
        $price_field_markup .= '<p><strong>' . __('Total Custom Price:', 'ultimate-product-options-for-woocommerce') . '</strong> ' . wc_price($custom_price) . '</p>';
        $price_field_markup .= '</div>';

        echo wp_kses_post($price_field_markup);
    }


    /**
     * Remove custom meta data from the WooCommerce order details page.
     *
     * @param array $formatted_meta The formatted meta data array.
     * @param WC_Order_Item $item The order item object.
     * @return array The modified formatted meta data array.
     */

    function upow_remove_meta_from_order($formatted_meta, $item)
    {
        $meta_key_to_remove = 'upow_custom_field_items_data_price'; // Replace with your meta key
        foreach ($formatted_meta as $key => $meta) {
            if ($meta->key === $meta_key_to_remove) {
                unset($formatted_meta[$key]);
            }
        }

        return $formatted_meta;
    }

    /**
     * Display custom field data below the product title on the WooCommerce admin order page.
     *
     * @param int $item_id The item ID.
     * @param WC_Order_Item_Product $item The order item object.
     * @param WC_Order $order The order object.
     */


    function upow_display_field_below_product_title($item_id, $item, $order)
    {
        $upow_item_label_text           = wc_get_order_item_meta($item_id, 'upow_item_label_text', true);
        $upow_custom_field_items_data   = wc_get_order_item_meta($item_id, 'upow_custom_field_items_data', true);

        // Check if the custom field data is an array or an object
        if (is_array($upow_custom_field_items_data) || is_object($upow_custom_field_items_data)) {
            $count = 0;
            foreach ($upow_custom_field_items_data as $key => $value) {

                $formatted_value = number_format((float)$value, 2, '.', '');
               $extra_fields = $upow_item_label_text[$count] . ' : ' . $this->currency_left . $formatted_value . $this->currency_right . '<br/>';
                echo wp_kses_post($extra_fields);
                $count++;
            }
        }
    }


    // new code

    /**
     * Save custom fields to order item meta.
     *
     * This function hooks into 'woocommerce_add_order_item_meta' to save custom fields
     * data as order item meta when an order is created.
     *
     * @since 1.0.0
     *
     * @param int    $item_id The item ID.
     * @param array  $values  The item data.
     * @return void
     */

    function upow_save_custom_fields_order_meta($item_id, $values)
    {
        if (isset($values['upow_custom_field_items_data'])) {
            wc_add_order_item_meta($item_id, 'upow_custom_field_items_data', $values['upow_custom_field_items_data']);
        }
        if (isset($values['upow_custom_field_items_data_price'])) {
            wc_add_order_item_meta($item_id, 'upow_custom_field_items_data_price', $values['upow_custom_field_items_data_price']);
        }

        if (isset($values['upow_item_label_text'])) {
            wc_add_order_item_meta($item_id, 'upow_item_label_text', $values['upow_item_label_text']);
        }
    }


    /**
     * Display custom fields in order details page.
     *
     * This function hooks into 'woocommerce_order_item_meta_end' to display custom fields
     * in the order details on the WooCommerce order page.
     *
     * @since 1.0.0
     *
     * @param int      $item_id    The item ID.
     * @param WC_Order_Item_Product|WC_Order_Item $item  The order item object.
     * @param WC_Order $order      The order object.
     * @param boolean  $plain_text Whether to display in plain text or not.
     * @return void
     */

    function upow_display_custom_fields_order_details($item_id, $item, $order, $plain_text)
    {
        $upow_custom_field_items_data       = wc_get_order_item_meta($item_id, 'upow_custom_field_items_data', true);
        $upow_item_label_text              = wc_get_order_item_meta($item_id, 'upow_item_label_text', true);
        $upow_custom_field_items_data_price = wc_get_order_item_meta($item_id, 'upow_custom_field_items_data_price', true);

        $upow_enable_extra_options_order_page = '';
        if (!empty(get_option('upow_enable_extra_options_order_page'))) {
            $upow_enable_extra_options_order_page = get_option('upow_enable_extra_options_order_page');
        }

        if( $upow_enable_extra_options_order_page == '1' ) {
            if (!empty($upow_custom_field_items_data) || !empty($upow_item_label_text)) {
                $count = 0;
                ?>
                <p>
                    <?php
                    foreach ($upow_custom_field_items_data as $key => $value) {
                        echo  esc_html($upow_item_label_text[$count]) . ': ' . wp_kses_post($this->currency_left) . esc_html(number_format((float)$value, 2, '.', '')) . wp_kses_post($this->currency_right) . '<br>';
                        $count++;
                    }
                    ?>
                </p>
            <?php }
            if (!empty($upow_custom_field_items_data_price)) {
                $options_price = sprintf('<p><strong>%s</strong>%s</p>', __('Options Price:', 'ultimate-product-options-for-woocommerce'), wc_price($upow_custom_field_items_data_price));

                echo wp_kses_post($options_price);
            }
        }
    }
}
