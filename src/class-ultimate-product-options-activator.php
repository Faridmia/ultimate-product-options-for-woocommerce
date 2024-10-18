<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Faridmia/infinite-scroll-product-for-woocommerce
 * @since      1.0.0
 *
 * @package    Ultimate Product Options For WooCommerce
 * @subpackage Ultimate Product Options For WooCommerce/src
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ultimate Product Options For WooCommerce
 * @subpackage Ultimate Product Options For WooCommerce/src
 * @author     Farid Mia <mdfarid7830@gmail.com>
 */
class Upow_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		if (!class_exists('WooCommerce')) {
			return false;
		}

		update_option('upow_extra_feature_on_off_global', 0);
		update_option('upow_global_extra_feature_on_off', 0);
		update_option('upow_accordion_style_on_off', 0);
		update_option('upow_enable_extra_options_checkout_page', 1);
		update_option('upow_show_customer_cart_page', 1);
		update_option('upow_enable_extra_options_order_page', 1);
		update_option('upow_backorder_on_off', 0);
		update_option('upow_preorder_on_off', 0);
		update_option('upow_show_countdown_single_page', 1);
		update_option('upow_enable_flash_sale_here', 0);
		update_option('upow_override_saleflash', 0);
		update_option('upow_enable_global_swatch_on_off', 0);
		update_option('upow_enable_swatches_image_tooltip', 0);
		update_option('upow_enable_clear_btn', 0);
		update_option('upow_enable_swith_label', 0);
		update_option('upow_swatches_shape_style', 'squared' );
		update_option('upow_swatches_disable_attribute_effect','cross');
		update_option('upow_swatches_position','before_cart');
		update_option('upow_countdown_style','layout_style_1');
		update_option('upow_countdown_position','woocommerce_after_add_to_cart_form');

	}
}