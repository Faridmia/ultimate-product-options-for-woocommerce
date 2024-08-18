<?php
namespace Ultimate\Upow\Common\PostType;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class PostType
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class PostType
{
    use Traitval;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It adds an action to the 'init' hook to create custom post types.
     */
    protected function initialize()
    {
        add_action('init', array($this, 'upow_create_custom_post_type'));
    }

    /**
     * Create a custom post type for Custom Product Data.
     *
     * This function registers a custom post type named 'Custom Product Data' with various labels and settings.
     * It includes configuration for UI visibility, capabilities, and support for specific features.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function upow_create_custom_post_type()
    {
        $labels = array(
            'name'               => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'singular_name'      => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'menu_name'          => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'name_admin_bar'     => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'add_new'            => __('Add New', 'ultimate-product-options-for-woocommerce'),
            'add_new_item'       => __('Add New Product Data', 'ultimate-product-options-for-woocommerce'),
            'new_item'           => __('New Product Data', 'ultimate-product-options-for-woocommerce'),
            'edit_item'          => __('Edit Product Data Group', 'ultimate-product-options-for-woocommerce'),
            'view_item'          => __('View Product Data', 'ultimate-product-options-for-woocommerce'),
            'all_items'          => __('All Product Data', 'ultimate-product-options-for-woocommerce'),
            'search_items'       => __('Search Product Data', 'ultimate-product-options-for-woocommerce'),
            'not_found'          => __('No product fields found.', 'ultimate-product-options-for-woocommerce'),
            'not_found_in_trash' => __('No product fields found in Trash.', 'ultimate-product-options-for-woocommerce'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // We will add it to the WooCommerce menu manually
            'query_var' => true,
            'rewrite' => array('slug' => 'upow-product'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        );

        register_post_type('upow_product', $args);
    }

    /**
     * Flush rewrite rules on theme activation.
     *
     * This function creates the custom post type and flushes rewrite rules
     * to ensure the custom post type's rewrite rules are registered and updated properly
     * when the theme is activated.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function upow_flush_rewrite_rules()
    {
        $this->upow_create_custom_post_type();
        flush_rewrite_rules();
    }
}
