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
	}
}
