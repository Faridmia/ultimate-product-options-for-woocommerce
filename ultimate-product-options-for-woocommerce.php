<?php
/**
 * @package Ultimate Product Options For WooCommerce
 * @version 1.0.0
 */
/*
Plugin Name: Ultimate Product Options For WooCommerce
Plugin URI: http://github.com/faridmia/ultimate-product-options-for-woocommerce
Description: Add extra product options like text fields, radio fields, and checkboxes to your WooCommerce products.
Version: 1.0.6
Requires at least: 6.4
Requires PHP: 7.4
Author: faridmia
Author URI: https://profiles.wordpress.org/faridmia/
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ultimate-product-options-for-woocommerce
Domain Path: /i18n/languages
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('UPOW_VERSION', '1.0.6');
define('UPOW_CORE_URL', plugin_dir_url(__FILE__));
define('UPOW_PLUGIN_ROOT', __FILE__);
define('UPOW_PLUGIN_PATH', plugin_dir_path(UPOW_PLUGIN_ROOT));
define('UPOW_PLUGIN_TITLE', 'Ultimate Product Options For WooCommerce');


add_action('init', 'upow_load_textdomain');
if (!version_compare(PHP_VERSION, '7.4', '>=')) {
    add_action('admin_notices', 'upow_fail_php_version');
} elseif (!version_compare(get_bloginfo('version'), '6.4', '>=')) {
    add_action('admin_notices', 'upow_fail_wp_version');
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Display an admin notice if the PHP version is not sufficient for the plugin.
 *
 * This function checks the current PHP version and displays a warning notice in the WordPress admin
 * if the current PHP version is less than the required version.
 *
 * @since 1.0.0
 */
function upow_fail_php_version()
{

    $message = sprintf(
        // Translators: %1$s is the plugin title, %2$s is the required PHP version.
        __('%1$s requires PHP version %2$s+, plugin is currently NOT RUNNING.', 'ultimate-product-options-for-woocommerce'),
        '<strong>' . UPOW_PLUGIN_TITLE . '</strong>',
        '7.4'
    );

    printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message));
}
/**
 * Display an admin notice if the WordPress version is not sufficient for the plugin.
 *
 * This function checks the current WordPress version and displays a warning notice in the WordPress admin
 * if the current version is less than the required version.
 *
 * @since 1.0.0
 */
function upow_fail_wp_version()
{

    $message      = sprintf(
        // Translators: %1$s is the plugin title, %2$s is the WordPress version.
        esc_html__('To function, %1$s needs WordPress version %2$s or higher. The plugin is currently NOT RUNNING due to an outdated version.', 'ultimate-product-options-for-woocommerce'),
        UPOW_PLUGIN_TITLE,
        '6.4'
    );
    $error_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($error_message);
}

/**
 * upow_load_ultimate-product-options-for-woocommerce loads ultimate-product-options-for-woocommerce ultimate-product-options-for-woocommerce.
 *
 * Load gettext translate for the ultimate-product-options-for-woocommerce text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function upow_load_textdomain()
{
    load_plugin_textdomain('ultimate-product-options-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/i18n/languages');

    // woocommerce  plugin dependency
    if (!function_exists('WC')) {
        add_action('admin_notices', 'upow_admin_notices');
    }
}

/**
 * The code that runs during plugin activation.
 * This action is documented in src/class-ultimate-product-options-activator.php
 */
function upow_activate_func()
{
    require_once UPOW_PLUGIN_PATH . 'src/class-ultimate-product-options-activator.php';
    Upow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in src/class-ultimate-product-options-deactivator.php
 */
function upow__deactivate_func()
{
    require_once UPOW_PLUGIN_PATH . 'src/class-ultimate-product-options-deactivator.php';
    Upow_Woo_Deactivator::deactivate();
}

register_activation_hook(UPOW_PLUGIN_ROOT, 'upow_activate_func');
register_deactivation_hook(UPOW_PLUGIN_ROOT, 'upow__deactivate_func');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function Upow__run_func()
{
    require_once __DIR__ . '/src/class-woocommerce-product-options.php';
}

function upow_admin_notices()
{
    $woocommerce_plugin = 'woocommerce/woocommerce.php';
    $plugin_name = esc_html__('Ultimate Product Options For WooCommerce', 'ultimate-product-options-for-woocommerce');

    // Check if WooCommerce is installed
    if (file_exists(WP_PLUGIN_DIR . '/' . $woocommerce_plugin)) {
        // WooCommerce is installed but may not be active
        if (!is_plugin_active($woocommerce_plugin)) {
            $activation_url = wp_nonce_url(
                'plugins.php?action=activate&amp;plugin=' . $woocommerce_plugin . '&amp;plugin_status=all&amp;paged=1&amp;s',
                'activate-plugin_' . $woocommerce_plugin
            );
            $message = sprintf(
                '<strong>%1$s requires WooCommerce to be active. You can <a href="%2$s" class="message" target="_blank">%3$s</a> here.</strong>',
                $plugin_name,
                esc_url($activation_url),
                __("Activate WooCommerce", "ultimate-product-options-for-woocommerce"),
            );
        }
    } else {
        // WooCommerce is not installed
        $plugin_name = 'WooCommerce';
        $action = 'install-plugin';
        $slug = 'woocommerce';
        $install_link = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => $action,
                    'plugin' => $slug
                ),
                admin_url('update.php')
            ),
            $action . '_' . $slug
        );
        $message = sprintf(
            '<strong>%1$s requires WooCommerce to be installed. You can download <a href="%2$s" class="message" target="_blank">%3$s</a> here.</strong>',
            $plugin_name,
            esc_url($install_link),
            __("WooCommerce Install", "ultimate-product-options-for-woocommerce"),
        );
    }
?>
    <div class="error">
        <p><?php echo wp_kses($message, 'upow_kses'); ?></p>
    </div>
<?php
}

Upow__run_func();