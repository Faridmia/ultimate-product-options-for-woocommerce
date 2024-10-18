<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class Preorder
{
    use Traitval;
    private $options = [];

    private function get_upow_preorder_fields_options() {
        $options = [
            'upow_preorder_on_off_switch'        => $this->get_option_checked('upow_preorder_on_off'),
            'upow_preorder_label_text'           => get_option('upow_preorder_label_text', ''),
            'upow_preorder_addto_cart_text'      => get_option('upow_preorder_addto_cart_text', ''),
            'upow_preorder_available_text_msg'   => get_option('upow_preorder_available_text_msg', ''),
            'upow_preorder_pre_released_message' => get_option('upow_preorder_pre_released_message', ''),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option($key, $default);
        }
    }


    // Method to handle displaying the Flash Sales Countdown section
    public function upow_preorder_fields_backend() {
        $options = $this->get_upow_preorder_fields_options();

        ?>
        <div class="upow-slide-opt-section" data-option="product-preorder-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Preorder Settings', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-preorder-options-fields">
                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item('Preorder Enable?', 'upow_preorder_on_off', $this->options['upow_preorder_on_off_switch']); ?>
                            <?php $this->render_text_input('Preorder Label Text', 'upow_preorder_label_text', $this->options['upow_preorder_label_text'], 'Preorder'); ?>
                            <?php $this->render_text_input('Preorder Add to cart Text', 'upow_preorder_addto_cart_text', $this->options['upow_preorder_addto_cart_text'], 'Preorder Now'); ?>
                        </div>
                        <div class="upow-settings-right-panel">
                            
                            <?php $this->render_text_input('Availability message', 'upow_preorder_available_text_msg', $this->options['upow_preorder_available_text_msg'], 'Available On'); ?>

                            <?php $this->render_text_input('Pre-Released Message', 'upow_preorder_pre_released_message', $this->options['upow_preorder_pre_released_message'], 'Comming Soon..'); ?>

                        </div>
                    </div>
    
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_preorder_product_fields_item_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

}