<?php
namespace Ultimate\Upow\Common\PostType;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class PostType
 * 
 * Handles the creation of a custom post type for the Ultimate Product Options For WooCommerce plugin.
 */
class PostType
{
    use Traitval;

    /**
     * @var string $post_type The name of the custom post type
     */
    private $post_type = 'upow_product';

    /**
     * @var array $labels The labels for the post type
     */
    private $labels = [];

    /**
     * @var array $args The arguments for the post type
     */
    private $args = [];

    /**
     * Initializes the class and creates the custom post type.
     */
    protected function initialize()
    {
        $this->set_labels();
        $this->set_args();

        add_action('init', [$this, 'register_custom_post_type']);
    }

    /**
     * Sets the labels for the custom post type.
     */
    private function set_labels()
    {
        $this->labels = [
            'name'                  => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'singular_name'         => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'menu_name'             => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'name_admin_bar'        => __('Product Extra Data', 'ultimate-product-options-for-woocommerce'),
            'add_new'               => __('Add New', 'ultimate-product-options-for-woocommerce'),
            'add_new_item'          => __('Add New Product Data', 'ultimate-product-options-for-woocommerce'),
            'new_item'              => __('New Product Data', 'ultimate-product-options-for-woocommerce'),
            'edit_item'             => __('Edit Product Data Group', 'ultimate-product-options-for-woocommerce'),
            'view_item'             => __('View Product Data', 'ultimate-product-options-for-woocommerce'),
            'all_items'             => __('All Product Data', 'ultimate-product-options-for-woocommerce'),
            'search_items'          => __('Search Product Data', 'ultimate-product-options-for-woocommerce'),
            'not_found'             => __('No product fields found.', 'ultimate-product-options-for-woocommerce'),
            'not_found_in_trash'    => __('No product fields found in Trash.', 'ultimate-product-options-for-woocommerce'),
        ];
    }

    /**
     * Sets the arguments for the custom post type.
     */
    private function set_args()
    {
        $this->args = [
            'labels'             => $this->labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // Manually added to the WooCommerce menu
            'query_var'          => true,
            'rewrite'            => ['slug' => 'upow-product'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
        ];
    }

    /**
     * Registers the custom post type.
     */
    public function register_custom_post_type()
    {
        register_post_type($this->post_type, $this->args);
    }

    /**
     * Flushes rewrite rules upon theme activation.
     */
    public function flush_rewrite_rules()
    {
        $this->register_custom_post_type();
        flush_rewrite_rules();
    }
}