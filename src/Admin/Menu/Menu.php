<?php

namespace Ultimate\Upow\Admin\Menu;

use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Menu
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for adding custom submenus to the WordPress admin menu
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Menu
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
        add_action('admin_menu', array($this, 'upow_add_products_submenu'));
    }

    /**
     * Add a Product Extra Data submenu under the WooCommerce menu.
     *
     * This function adds a submenu page for 'Product Extra Data' under the main WooCommerce menu in the WordPress admin dashboard.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function upow_add_products_submenu()
    {
        add_submenu_page(
            'woocommerce',
            __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'manage_options',
            'edit.php?post_type=upow_product'
        );
    }
}
