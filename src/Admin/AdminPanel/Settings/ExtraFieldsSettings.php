<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class ExtraFieldsSettings
{
    use Traitval;
    private $options = [];

    private function get_upow_extra_fields_options() {
        $options = [
            'global_upow_extra_feature_on_off' => $this->get_option_checked('upow_extra_feature_on_off_global',0 ),
            'upow_global_extra_feature' => $this->get_option_checked('upow_global_extra_feature_on_off',0 ),
            'upow_accordion_style' => $this->get_option_checked('upow_accordion_style_on_off',0 ),
            'upow_select_product' => get_option('upow_select_product', []),
            'upow_exclude_product' => get_option('upow_exclude_product', []),
            'upow_extra_product_feature_title' => get_option('upow_extra_product_feature_title', ''),
            'upow_addon_title_text_size' => get_option('upow_addon_title_text_size', ''),
            'upow_addon_title_text_color' => get_option('upow_addon_title_text_color', ''),
            'addon_item_title_text_color' => get_option('addon_item_title_text_color', ''),
            'addon_item_title_bg_color' => get_option('addon_item_title_bg_color', ''),
            'addon_item_label_text_color' => get_option('addon_item_label_text_color', ''),
            'addon_price_info_bg_color' => get_option('addon_price_info_bg_color', ''),
            'addon_price_info_text_price_color' => get_option('addon_price_info_text_price_color', ''),
            'addon_price_info_border_bottom_color' => get_option('addon_price_info_border_bottom_color', ''),
            'upow_addon_title_padding' => get_option('upow_addon_title_padding', ''),
            'upow_show_extra_options_checkout_page' => $this->get_option_checked('upow_enable_extra_options_checkout_page'),
            'upow_show_customer_cart' => $this->get_option_checked('upow_show_customer_cart_page'),
            'upow_enable_extra_options_order' => $this->get_option_checked('upow_enable_extra_options_order_page'),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option($key, $default);
        }
    }


    // Method to handle displaying the Flash Sales Countdown section
    public function upow_extra_fields_backend() {
        $options = $this->get_upow_extra_fields_options();

        ?>
        <div class="upow-slide-opt-section" data-option="product-extra-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Product Extra Fields', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-extra-options-fields">

                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item('Extra Feature On/Off', 'upow_extra_feature_on_off_global', $this->options['global_upow_extra_feature_on_off']); ?>
                            <?php $this->render_checkbox_item('Apply to All Products', 'upow_global_extra_feature_on_off', $this->options['upow_global_extra_feature']); ?>
                            <?php $this->render_checkbox_item('Show extra options cart page', 'upow_show_customer_cart_page', $this->options['upow_show_customer_cart']); ?>
                            <?php $this->render_checkbox_item('Show extra options checkout page', 'upow_enable_extra_options_checkout_page', $this->options['upow_show_extra_options_checkout_page']); ?>
                            <?php $this->render_checkbox_item('Show extra options Order page', 'upow_enable_extra_options_order_page', $this->options['upow_enable_extra_options_order']); ?>
                            <?php $this->render_checkbox_item('Enable Accordion Style', 'upow_accordion_style_on_off', $this->options['upow_accordion_style']); ?>
                            <?php $this->render_select_product_field('Select Product', 'upow_select_product', $this->options['upow_select_product']); ?>
                            <?php $this->render_select_product_field('Exclude Product', 'upow_exclude_product', $this->options['upow_exclude_product']); ?>
                        </div>
                        <div class="upow-settings-right-panel">
                            <?php $this->render_text_input('Product Addon Title', 'upow_extra_product_feature_title', $this->options['upow_extra_product_feature_title'], 'Extra Product Feature Title'); ?>

                            <?php $this->render_fontsize_input_func('Product Addon Title Size', 'upow_addon_title_text_size', $this->options['upow_addon_title_text_size'], ''); ?>
                            <?php $this->render_color_input('Product Addon Title text color', 'upow_addon_title_text_color', $this->options['upow_addon_title_text_color']); ?>
                            <?php $this->render_color_input('Addon Item title text color', 'addon_item_title_text_color', $this->options['addon_item_title_text_color']); ?>
                            <?php $this->render_color_input('Addon Item title Background Color', 'addon_item_title_bg_color', $this->options['addon_item_title_bg_color']); ?>
                            <?php $this->render_text_input('Addon Item title padding', 'upow_addon_title_padding', $this->options['upow_addon_title_padding'], '10px 30px 10px 30px'); ?>
                            <?php $this->render_color_input('Addon Item label and price text color', 'addon_item_label_text_color', $this->options['addon_item_label_text_color']); ?>
                            <?php $this->render_color_input('Addon Price Info Background color', 'addon_price_info_bg_color', $this->options['addon_price_info_bg_color']); ?>
                            <?php $this->render_color_input('Addon Price Info Text and price color', 'addon_price_info_text_price_color', $this->options['addon_price_info_text_price_color']); ?>
                            <?php $this->render_color_input('Price Info border bottom color', 'addon_price_info_border_bottom_color', $this->options['addon_price_info_border_bottom_color']); ?>
                        </div>
                    </div>
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_extra_product_fields_item_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // Method to render select product fields
    private function render_select_product_field($label, $name, $selected_values) {

        $select_exclude_class = ($name == 'upow_select_product' ) ? 'upow-select-product-fields' : ( ( $name == 'upow_exclude_product' )  ? 'upow-exclude-product' : '');
        ?>
        <div class="upow-general-item <?php echo esc_attr( $select_exclude_class ); ?>">
            <div class="upow-gen-item-con upow-extra-product-fields-select">
                <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce'); ?></label>
                <select multiple name="<?php echo esc_attr($name); ?>[]" class="upow-select-product">
                    <?php if( $name == 'upow_select_product') { 
                        echo upow_all_product_panel_output($selected_values); 
                    } else {
                        echo (upow_exclude_product_panel_output($selected_values));
                    }
                    
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}