<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;

use Ultimate\Upow\Traitval\Traitval;

/**
 * Class GeneralSettings
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class GeneralSettings
{
    use Traitval;

    private $options = [];

    /**
     * Constructor
     * 
     * The constructor adds actions to the WordPress hooks for saving post data
     * and adding meta boxes to the post edit screen. These actions ensure that
     * custom metadata is handled properly within the Ultimate Product Options For WooCommerce plugin.
     */
    public function __construct()
    {
        $this->load_options();
        add_action('admin_menu', [$this, 'upow_add_menu']);
    }

    /**
     * Load UPOW options
     *
     * @since 1.0.0
     */
    private function load_options()
    {
        $this->options = [
            'upow_product_per_page' => get_option('upow_product_per_page', ''),
            'upow_google_analytics' => get_option('upow_google_analytics', ''),
            'upow_custom_css' => get_option('upow_custom_css', ''),
            'upow_custom_js' => get_option('upow_custom_js', ''),
        ];
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function upow_add_menu()
    {
        add_menu_page(
            __('UPOW Options', 'ultimate-product-options-for-woocommerce'),
            __('UPOW Options', 'ultimate-product-options-for-woocommerce'),
            'manage_options',
            'upow-option-setting',
            [$this, 'upow_menu_callback'],
            'dashicons-archive',
            100
        );
        // Only remove admin notices on your specific page
        add_action('admin_head', [$this, 'upow_hide_admin_notices']);
    }

    /**
     * Hide other plugin's admin notices on your plugin's admin page.
     */
    public function upow_hide_admin_notices() {
        // Check if we are on the UPOW Options page
        if (isset($_GET['page']) && $_GET['page'] === 'upow-option-setting') {
            ?>
            <style>
                .notice, .update-nag, .error, .updated {
                    display: none !important;
                }
            </style>
            <?php
        }
    }

    /**
     * Admin page callback function
     *
     * @since 1.0.0
     */
    public function upow_menu_callback()
    {
        if (isset($_GET['page'])) {
            $this->render_page_header();
            $this->render_page_body();
        }
    }

    /**
     * Render page header
     */
    private function render_page_header()
    {
        ?>
        <div class="upow-option-page-wrapper">
            <!-- header section -->
            <div class="upow-header">
                <div class="upow-font">
                    <img src="<?php echo esc_url(UPOW_CORE_URL); ?>/assets/admin/img/upow_option_page_logo.png"
                         alt="<?php echo esc_attr__('Image', 'ultimate-product-options-for-woocommerce'); ?>">
                    <span class="upow-option-logo"><?php echo esc_html__('Ultimate Product Options Settings', 'ultimate-product-options-for-woocommerce'); ?> </span>
                </div>
            </div>
        <?php
    }

    /**
     * Render page body
     */
    private function render_page_body()
    {
        ?>
            <!-- body section -->
            <div class="upow-body-all">
                <div class="upow-abs-page-wrapper">
                    <div id="upow-message-box"></div>
                    <div class="upow-slide-opt-section" data-option="general">
                        <div class="upow-shado">
                            <div class="upow-ati-all">
                                <div class="upow-ati-a"></div>
                                <div class="upow-ati-b"><?php echo esc_html__('General Setting', 'ultimate-product-options-for-woocommerce'); ?></div>
                            </div>
                        </div>
                        <div class="upow-general-section upow-slide-item">
                            <form method="post" class="upow-general-settings-options">
                                <div class="upow-settings-main-wrapper">
                                    <div class="upow-settings-left-panel">
                                        <?php 
                                        $this->render_general_item('Products Per Page', 'upow_product_per_page', 'number', $this->options['upow_product_per_page']);
                                        $this->render_general_item('Custom CSS Code', 'upow_custom_css', 'textarea', $this->options['upow_custom_css'], 'You can use custom css code here.');
                                        ?>
                                    </div>
                                    <div class="upow-settings-right-panel">
                                    <?php
                                        $this->render_general_item('Custom JS Code', 'upow_custom_js', 'textarea', $this->options['upow_custom_js'], 'You can use custom javascript/jQuery code here.');
                                        $this->render_general_item('Google Analytics', 'upow_google_analytics', 'textarea', $this->options['upow_google_analytics'], 'Copy Google Analytics Code From Analytics Dashboard And Paste Here.');
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="upow-general-item-save upow-settings-button-top">
                                    <input type="submit" class="upow_checkbox_item_save"
                                           name="upow_checkbox_item_save"
                                           value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php do_action('upow_after_add_option'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render individual general item
     * 
     * @param string $label
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @param string|null $description
     */
    private function render_general_item($label, $name, $type, $value, $description = null)
    {
        ?>
        <div class="upow-general-item">
            <div class="upow-gen-item-con">
                <label for="upow-title-bg"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce'); ?></label>
                <?php if ($type === 'textarea'): ?>
                    <textarea name="<?php echo esc_attr($name); ?>"
                              class="<?php echo esc_attr($name); ?>"><?php echo wp_unslash($value); ?></textarea>
                <?php else: ?>
                    <input type="<?php echo esc_attr($type); ?>" class="upow-options-text-size"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($value); ?>">
                <?php endif; ?>
                <?php if ($description): ?>
                    <span class="upow_option_details"><?php echo esc_html__($description, 'ultimate-product-options-for-woocommerce'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}