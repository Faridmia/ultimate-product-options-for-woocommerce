<?php
namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class QuickCheckout
{
    use Traitval;
    private $options = [];

    private function get_upow_quickcheckout_fields_options() {
        $options = [
            'oneclick_enabled'       => $this->get_option_checked('upow_oneclick_checkout_on_off'),
            'shop_checkout_enabled'  => $this->get_option_checked('upow_oneclick_checkout_shop_enable'),
            'checkout_btn_text'      => get_option('upow_one_click_checkout_btn_text', ''),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option( $key, $default );
        }
    }


    // Method to handle displaying the Flash Sales Countdown section
    public function upow_quickcheckout_fields_backend() {
        $options = $this->get_upow_quickcheckout_fields_options();

        ?>
        <div class="upow-slide-opt-section" data-option="product-one-click-checkout-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('One-Click Checkout', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-one-click-checkout-options-fields">
                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item('One-Click Checkout Enable?', 'upow_oneclick_checkout_on_off', $this->options['oneclick_enabled']); ?>
                            <?php $this->render_checkbox_item('One-Click Checkout Enable Shop Page?', 'upow_oneclick_checkout_shop_enable', $this->options['shop_checkout_enabled']); ?>
                        </div>
                        <div class="upow-settings-right-panel">
                            <?php $this->render_text_input('One-Click Checkout Button Text', 'upow_one_click_checkout_btn_text', $this->options['checkout_btn_text'], 'Buy Now'); ?>
                        </div>
                    </div>
    
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_one_click_checkouit_product_fields_item_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

}