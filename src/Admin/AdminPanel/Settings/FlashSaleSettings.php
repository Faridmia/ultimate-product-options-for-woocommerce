<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class FlashSaleSettings
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

    }

    private function load_options() {

        $options = [
            'enable_show_countdown_single_page' => $this->get_option_checked('upow_show_countdown_single_page'),
            'upow_countdown_timer_title'      => '',
            'offer_title_text_color'          => '',
            'offer_title_bg_color'            => '',
            'upow_countdown_position'         => '',
            'upow_countdown_style'            => '',
            'offer_countdown_text_color'      => '',
            'offer_countdown_bg_color'        => '',
            'offer_countdown_item_title_color'=> '',
            'upow_flash_sale_settings' => [],
            'enable_flash_sale_here' => $this->get_option_checked('upow_enable_flash_sale_here'),
            'enable_override_saleflash' => $this->get_option_checked('upow_override_saleflash'),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option($key, $default);
        }
    }


    public function display() {
        ?>
        <div class="upow-slide-opt-section" data-option="flash-sale-countdown"> 
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Flash Sale Countdown', 'ultimate-product-options-for-woocommerce') ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-flashsale-countdown-from">
                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item(
                            'Show Countdown On Product Details Page',
                            'upow_show_countdown_single_page',
                            $this->options['enable_show_countdown_single_page']); ?>
                            <?php $this->render_button_item(
                                'FlashSale Settings',
                                'Click Here'
                            ); ?>
                            <?php $this->render_select_item(
                                'upow_countdown_style',
                                'Countdown Style',
                                [
                                    'layout_style_1' => 'Style 1',
                                    'layout_style_2' => 'Style 2'
                                ]
                            ); ?>
                            <?php $this->render_select_item(
                                'upow_countdown_position',
                                'Countdown Position',
                                [
                                    'woocommerce_after_add_to_cart_form' => 'After - Add to cart',
                                    'woocommerce_before_add_to_cart_form' => 'Before - Add to cart',
                                    'woocommerce_product_meta_end' => 'After - Product Meta',
                                    'woocommerce_product_meta_start' => 'Before - Product Meta',
                                    'woocommerce_single_product_summary' => 'Before - Product summary',
                                    'woocommerce_after_single_product_summary' => 'After - Product summary'
                                ]
                            ); ?>
                           
                        </div>
                        <div class="upow-settings-right-panel">
                            <?php $this->render_text_input(
                            'Countdown Timer Title',
                            'upow_countdown_timer_title',
                            'Hurry Up! Offer ends in',
                            $this->options['upow_countdown_timer_title']
                        ); ?>
                        <?php $this->render_color_input(
                            'Offer title text color',
                            'offer_title_text_color',
                            $this->options['offer_title_bg_color']
                        ); ?>
                        <?php $this->render_color_input(
                            'Offer title Background Color',
                            'offer_title_bg_color',
                            $this->options['offer_title_bg_color']
                            
                        ); ?>
                        <?php $this->render_color_input(
                            'Offer Countdown text Number color',
                            'offer_countdown_text_color',
                            $this->options['offer_countdown_text_color']
                        ); ?>
                        <?php $this->render_color_input(
                            'Offer Countdown Background Color',
                            'offer_countdown_bg_color',
                            $this->options['offer_countdown_bg_color']
                        ); ?>
                        <?php $this->render_color_input(
                            'Offer Countdown Title Color',
                            'offer_countdown_item_title_color',
                            $this->options['offer_countdown_item_title_color']
                        ); ?>
                        </div>
                    </div>
                    
                    
                    
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_flashsale_countdown_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    private function render_button_item($label, $button_text) {
        ?>
        <div class="upow-general-item upow-checkbox-item">
            <div class="upow-gen-item-con">
                <label for="upow-title-bg"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <div class="upow-extra-options-each-tem">
                    <span></span>
                    <button type="submit" class="upow-flash-sale-popup-active rbt-round-btn">
                        <?php echo esc_html__($button_text, 'ultimate-product-options-for-woocommerce'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }


    /**
     * Flashsale popup settings 
     */

    public function popup_display() {
        ?>
        <div class="upow-flash-sale-popup">
            <div class="ajax-search-close-icon">
                <a class="upow-flash-sale-popup-close-icon rbt-round-btn" href="#">
                    <svg width="14" height="14" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
                    </svg>
                </a>
            </div>

            <div class="wrapper">
                <form method="post" action="">
                    <div class="upow-flash-sale-settings-main-wrapper">
                        <?php $this->render_checkbox_item( 'Enable flash sale here?','upow_enable_flash_sale_here',$this->options['enable_flash_sale_here'] ); ?>
                        <?php $this->render_checkbox_item(  'Override sale price','upow_override_saleflash',$this->options['enable_override_saleflash'] ); ?>

                        <div class="upow-flash-sale-item-wrapper">
                            <?php $this->render_flash_sale_items(); ?>
                        </div>

                        <button type="button" id="add-upow-flash-sale-item-group">
                            <?php echo esc_html__("Add Item", "ultimate-product-options-for-woocommerce"); ?>
                        </button>
                    </div>
                    <div class="upow-popup-item-save">
                        <input type="submit" class="upow_popup_item_save" name="upow_flash_sale_popup_item_save"
                               value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    private function render_flash_sale_items() {
        $settings = $this->options['upow_flash_sale_settings'];

        if (is_array($settings)) {
            foreach ($settings as $index => $product_data) {
                foreach ($product_data['upow_flashsale_product'] as $key => $item) {
                    ?>
                    <div class="upow-flash-sale-item-group" data-index="<?php echo esc_attr($index); ?>">
                        <div class="upow-flash-sale-header">
                            <div class="upow-flash-sale-item-group-header">
                                <?php echo esc_html__("Field Item", "ultimate-product-options-for-woocommerce"); ?> <?php echo esc_html($index + 1); ?>
                            </div>
                            <button type="button" class="remove-upow-flash-sale-item-group">
                                <svg width="18" height="18" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="upow-flash-sale-item-group-body" style="display: none;">
                            <?php $this->render_flash_sale_item_fields($index, $item); ?>
                        </div>
                    </div>
                    <?php
                }
            }
        }
    }

    private function render_flash_sale_item_fields($index, $item) {
        $fields = [
            'field_label' => 'Field Label',
            'apply_all_product' => 'Apply Across All Products',
            'select_product' => 'Select Product',
            'exclude_product' => 'Exclude Product',
            'select_categories' => 'Select Categories',
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            'flashsale_start_date' => 'Flash Sale Start Date From',
            'flashsale_end_date' => 'Flash Sale End Date From'
        ];

        foreach ($fields as $key => $label) {
            $value = isset($item[$key]) ? $item[$key] : '';

            if (in_array($key, ['select_product', 'exclude_product', 'select_categories'])) {
                $this->render_select_field($index, $key, $label, $value);
            } elseif (in_array($key, ['apply_all_product'])) {
                $this->render_checkbox_field($index, $key, $label, $value);
            } elseif (in_array($key, ['discount_type'])) {
                $this->render_discount_type_field($index, $key, $label, $value);
            } elseif (in_array($key, ['flashsale_start_date','flashsale_end_date'])) {
                $this->render_text_field($index, $key, $label, $value, $class = 'upow-flashsale-datepicker',$type = 'text');
            } 
            elseif (in_array($key, ['discount_value'])) {
                $this->render_text_field($index, $key, $label, $value, $class = '',$type = 'number');
            }
            else {
                $this->render_text_field($index, $key, $label, $value ,$class = '',$type = 'text');
            }
        }
    }

    private function render_text_field($index, $key, $label, $value, $class,$type="text" ) {
        ?>
        <p>
            <label for="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]">
                <?php echo esc_html__($label, "ultimate-product-options-for-woocommerce"); ?>
            </label>
            <?php 
            if( $class == 'upow-flashsale-datepicker') { ?>
            <div class="date-picker-wrapper">
            <?php } ?>
            <input class="<?php echo esc_attr( $class ); ?>" type="<?php echo esc_attr( $type );?>" name="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]"
                   value="<?php echo esc_attr($value); ?>">
            <?php 
            if( $class == 'upow-flashsale-datepicker') { ?>
            </div>
            <?php } ?>
        </p>
        <?php
    }

    private function render_checkbox_field($index, $key, $label, $value) {
        ?>
        <p>
            <label for="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]">
                <?php echo esc_html__($label, "ultimate-product-options-for-woocommerce"); ?>
            </label>
            <label class="upow-label-switch">
                <input type="checkbox" name="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]"
                       value="1" id="upow_product_<?php echo esc_attr($index);?>_apply_all_product" <?php checked($value, '1'); ?>>
                <span class="upow-slider upow-round"></span>
            </label>
        </p>
        <?php
    }

    private function render_select_field( $index, $key, $label, $value ) {

        $class_name = $key === 'select_product' ? 'upow-flashsale-select-popup' : ($key === 'exclude_product' ? 'upow-flashsale-exclude-popup' : '');

        ?>
        <p class="upow-flashsale-product-fields-select <?php echo esc_attr( $class_name ); ?>">
            <label for="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]">
                <?php echo esc_html__($label, "ultimate-product-options-for-woocommerce"); ?>
            </label>
            <select multiple name="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>][]"
                    class="upow-select-product">
                <?php
                if ($key === 'select_product') {
                    echo (upow_all_product_panel_output($value));
                } elseif ($key === 'exclude_product') {
                    echo (upow_exclude_product_panel_output($value));
                } elseif ($key === 'select_categories') {
                    echo (upow_get_product_categories($value));
                }
                ?>
            </select>
        </p>
        <?php
    }

    private function render_discount_type_field($index, $key, $label, $value) {
        ?>
        <p>
            <label for="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]">
                <?php echo esc_html__($label, "ultimate-product-options-for-woocommerce"); ?>
            </label>
            <select name="upow_flashsale_product[<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]">
                <option value="percent_discount" <?php selected($value, 'percent_discount'); ?>>
                    <?php echo esc_html__("Percentage Discount", "ultimate-product-options-for-woocommerce"); ?>
                </option>
                <option value="fixed_discount" <?php selected($value, 'fixed_discount'); ?>>
                    <?php echo esc_html__("Fixed Discount", "ultimate-product-options-for-woocommerce"); ?>
                </option>
                <option value="fixed_price" <?php selected($value, 'fixed_price'); ?>>
                    <?php echo esc_html__("Fixed Price", "ultimate-product-options-for-woocommerce"); ?>
                </option>
            </select>
        </p>
        <?php
    }

}