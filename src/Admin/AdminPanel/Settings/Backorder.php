<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class Backorder
{
    use Traitval;
    private $options = [];

    private function get_upow_backorder_fields_options() {
        $options = [
            'upow_backorder_on_off_switch' => $this->get_option_checked('upow_backorder_on_off'),
            'upow_backorder_label_text' => get_option('upow_backorder_label_text', ''),
            'upow_backorder_addto_cart_text' => get_option('upow_backorder_addto_cart_text', ''),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option($key, $default);
        }
    }


    // Method to handle displaying the Flash Sales Countdown section
    public function upow_backorder_fields_backend() {
        $options = $this->get_upow_backorder_fields_options();

        ?>
        <div class="upow-slide-opt-section" data-option="product-backorder-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Backorder Settings', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-backorder-options-fields">
                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item('Backorder Enable?', 'upow_backorder_on_off', $this->options['upow_backorder_on_off_switch']); ?>
                        </div>
                        <div class="upow-settings-right-panel">
                            <?php $this->render_text_input('Backorder Label Text', 'upow_backorder_label_text', $this->options['upow_backorder_label_text'], 'Backorder'); ?>
                            <?php $this->render_text_input('Backorder Add to cart Text', 'upow_backorder_addto_cart_text', $this->options['upow_backorder_addto_cart_text'], 'Backorder Now'); ?>
                        </div>
                    </div>
    
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_backorder_product_fields_item_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

}