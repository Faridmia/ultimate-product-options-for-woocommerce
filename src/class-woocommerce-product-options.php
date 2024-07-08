<?php
// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Ultimate\Upow\Admin\Admin;
use Ultimate\Upow\Traitval\Traitval;
use Ultimate\Upow\Common\Common;
use Ultimate\Upow\Front\Front;
use Ultimate\Upow\HookWoo\HookWoo;

final class UpowWooProductOptions
{

    use Traitval;
    /**
     * Plugin Version
     *
     * @since 1.0.0
     * @var string The plugin version.
     */

    private static $instance;
    public $admin;
    public $front;
    public $common;
    public $hookwoo;

    private function __construct()
    {
        $this->define_constants();

        add_action('plugins_loaded', array($this, 'init_plugin'));
        add_action('wp_enqueue_scripts', array($this, 'upow_enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'upow_enqueue_admin_assets'));
        add_filter('plugin_action_links_' . UPOW_PLUGIN_BASE,  array($this, 'upow_setting_page_link_func'));
        add_action('after_upow-label-switch_theme', array($this, 'upow_flush_rewrite_rules'));
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants()
    {
        // general constants
        define('UPOW_PLUGIN_URL', plugins_url('/', UPOW_PLUGIN_ROOT));
        define('UPOW_PLUGIN_BASE', plugin_basename(UPOW_PLUGIN_ROOT));
        define('UPOW_CORE_ASSETS', UPOW_PLUGIN_URL);
    }

    /**
     * Enqueues frontend CSS and JavaScript for the Ultimate Product Options For WooCommerce plugin.
     *
     * This function hooks into 'wp_enqueue_scripts' to load the necessary frontend assets (CSS and JS)
     * for the plugin. It ensures the assets are loaded on the front end of the site.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function upow_enqueue_frontend_assets()
    {

        // Enqueue frontend CSS
        wp_enqueue_style('upow-front-css', UPOW_CORE_ASSETS . 'assets/frontend/css/upow-front.css', array(), UPOW_VERSION);

        // Enqueue frontend JS
        wp_enqueue_script('upow-frontend-script', UPOW_CORE_ASSETS . 'assets/frontend/js/upow-script.js', array('jquery'), UPOW_VERSION, true);

        // Get WooCommerce currency settings
        $currency_symbol    = get_woocommerce_currency_symbol();
        $currency_position  = get_option('woocommerce_currency_pos');

        // Localize the script with currency data
        wp_localize_script('upow-frontend-script', 'woo_currency', array(
            'symbol'   => $currency_symbol,
            'position' => $currency_position,
        ));
    }

    /**
     * Enqueues admin CSS and JavaScript for the Ultimate Product Options For WooCommerce plugin.
     *
     * This function hooks into 'admin_enqueue_scripts' to load the necessary admin assets (CSS and JS)
     * for the plugin. It ensures the assets are loaded on the admin side of the site.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function upow_enqueue_admin_assets()
    {
        // Enqueue admin CSS
        wp_enqueue_style('upow-admin-css', UPOW_CORE_ASSETS . 'assets/admin/css/upow-admin.css', array(), UPOW_VERSION);

        // Enqueue admin JS
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('upow-admin-js', UPOW_CORE_ASSETS . 'assets/admin/js/upow-admin.js', array('jquery', 'jquery-ui-sortable'), UPOW_VERSION, true);
    }

    /**
     * Add a settings link to the plugin action links.
     *
     * This function adds a link to the settings page in the plugin's action links on the Plugins page.
     * It uses the 'plugin_action_links_' filter to append the settings link to the existing array of links.
     *
     * @since 1.0.0
     *
     * @param array $links An array of the plugin's action links.
     * @return array The modified array of action links with the settings page link appended.
     */

    function upow_setting_page_link_func($links)
    {
        $action_link = sprintf("<a href='%s'>%s</a>", admin_url('edit.php?post_type=upow_product'), __('Custom Fields', 'ultimate-product-options-for-woocommerce'));
        array_push($links, $action_link);
        return $links;
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin()
    {
        self::$instance         = self::getInstance();

        self::$instance->common = Common::getInstance();
        self::$instance->front  = Front::getInstance();
        self::$instance->hookwoo  = HookWoo::getInstance();

        if (is_admin()) {
            self::$instance->admin = Admin::getInstance();
        }
    }
}

/**
 * Initializes the main plugin
 *
 * This function returns the singleton instance of the UpowWooProductOptions class,
 * ensuring that there is only one instance of the plugin running at any time.
 *
 * @return \UpowWooProductOptions The singleton instance of the UpowWooProductOptions class.
 */
function UPOW_WPO()
{
    return UpowWooProductOptions::getInstance();
}

// Kick-off the plugin by calling the UPOW_WPO function to initialize the plugin.
UPOW_WPO();
