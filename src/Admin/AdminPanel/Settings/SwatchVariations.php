<?php

namespace Ultimate\Upow\Admin\AdminPanel\Settings;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class SwatchVariations
{
    use Traitval;
    private $options = [];

    private function get_upow_swatch_fields_options() {

        $options = [
            'upow_enable_global_swatch_value_on_off'    => $this->get_option_checked( 'upow_enable_global_swatch_on_off' ),
            'upow_enable_swatch_val_product_page'       => $this->get_option_checked( 'upow_enable_swatch_product_page' ),
            'upow_enable_swatch_val_shop_page'          => $this->get_option_checked( 'upow_enable_swatch_shop_page' ),
            'upow_convert_dropdown_val_to_label'        => $this->get_option_checked( 'upow_convert_dropdown_to_label' ),
            'upow_enable_swith_label_val'               => $this->get_option_checked( 'upow_enable_swith_label' ),
            'upow_enable_disable_tooltip_design_val'    => $this->get_option_checked( 'upow_enable_disable_tooltip_design' ),
            'upow_enable_swatches_image_tooltip_val'    => $this->get_option_checked( 'upow_enable_swatches_image_tooltip' ),
            'upow_enable_clear_btn_shop_page'           => $this->get_option_checked( 'upow_enable_clear_btn' ),
            'upow_swatches_position'                => get_option( 'upow_swatches_position', '' ),
            'upow_swatches_shape_style'             => get_option( 'upow_swatches_shape_style', '' ),
            'upow_swatches_disable_attribute_effect'=> get_option( 'upow_swatches_disable_attribute_effect', '' ),
            'upow_variations_label_separator_text'  => get_option( 'upow_variations_label_separator_text', '' ),
            'change_ajax_variations_thresholds'     => get_option( 'change_ajax_variations_thresholds','' ),
            'upow_swatches_item_width'              => get_option( 'upow_swatches_item_width', '' ),
            'upow_swatches_item_height'             => get_option( 'upow_swatches_item_height', '' ),
            'upow_font_size'                        => get_option( 'upow_font_size', '' ),
            'upow_tooltip_box_width'                => get_option( 'upow_tooltip_box_width', '' ),
            'upow_tooltip_box_height'               => get_option( 'upow_tooltip_box_height', '' ),
            'upow_tooltip_background_color'         => get_option( 'upow_tooltip_background_color', '' ),
            'upow_tooltip_font_color'               => get_option( 'upow_tooltip_font_color', '' ),
            'upow_swatches_tooltip_pos'               => get_option( 'upow_swatches_tooltip_pos', '' ),
        ];

        foreach ($options as $key => $default) {
            $this->options[$key] = get_option($key, $default);
        }
    }


    // Method to handle displaying the Flash Sales Countdown section
    public function upow_swatchvariations_fields_backend() {
        $options = $this->get_upow_swatch_fields_options();

        ?>
        <div class="upow-slide-opt-section" data-option="product-variations-swatches-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Variations Swatches', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-variations-swatches-options-fields">
                    <div class="upow-settings-main-wrapper">
                        <div class="upow-settings-left-panel">
                            <?php $this->render_checkbox_item('Enable Variation Swatches?', 'upow_enable_global_swatch_on_off', $this->options['upow_enable_global_swatch_value_on_off']); ?>
                            <?php $this->render_checkbox_item('Enable for product single page?', 'upow_enable_swatch_product_page', $this->options['upow_enable_swatch_val_product_page']); ?>
                            <?php $this->render_checkbox_item('Enable for shop page?', 'upow_enable_swatch_shop_page', $this->options['upow_enable_swatch_val_shop_page']); ?>
                            <?php $this->render_checkbox_item('Auto convert dropdown to label?', 'upow_convert_dropdown_to_label', $this->options['upow_convert_dropdown_val_to_label']); ?>
                            <?php $this->render_checkbox_item('Enable Swatches Label Shop Page?', 'upow_enable_swith_label', $this->options['upow_enable_swith_label_val']); ?>
                            <?php $this->render_checkbox_item('Enable Clear Button Shop Page?', 'upow_enable_clear_btn', $this->options['upow_enable_clear_btn_shop_page']); ?>
                            <?php $this->render_checkbox_item('Enable/Disable Tooltip?', 'upow_enable_disable_tooltip_design', $this->options['upow_enable_disable_tooltip_design_val']); ?>
                            <?php $this->render_checkbox_item('Enable Swatch image as Tooltip?', 'upow_enable_swatches_image_tooltip', $this->options['upow_enable_swatches_image_tooltip_val']); ?>
                            <?php $this->render_select_item(
                                'upow_swatches_position',
                                'Swatches Position Shop Page',
                                [
                                    'before_title' => 'Before Title',
                                    'after_title' => 'After Title',
                                    'before_price' => 'Before Price',
                                    'after_price' => 'After Price',
                                    'before_cart' => 'Before Cart',
                                    'after_cart' => 'After Cart'
                                ]
                            ); ?>
                            <?php $this->render_select_item(
                                'upow_swatches_shape_style',
                                'Shape Style',
                                [
                                    'squared' => 'Squared',
                                    'rounded' => 'Rounded',
                                    'circle' => 'Circle',
                                ]
                            ); ?>
                            
                            <?php $this->render_select_item(
                                'upow_swatches_disable_attribute_effect',
                                'Disable attribute effects',
                                [
                                    'cross' => 'Cross Sign',
                                    'blur' => 'Blur',
                                    'hide' => 'Hide',
                                ]
                            ); ?>
                            

                        </div>
                        <div class="upow-settings-right-panel">
                            
                            <?php $this->render_number_input('Change ajax variation for Threshold?', 'change_ajax_variatios_threadholds', $this->options['change_ajax_variatios_threadholds'], '30'); ?>
                            <?php $this->render_text_input('Variations Label separator', 'upow_variations_label_separator_text', $this->options['upow_variations_label_separator_text'], ':'); ?>
                            

                            <?php $this->render_fontsize_input_func('Swatches Width', 'upow_swatches_item_width', $this->options['upow_swatches_item_width'], ''); ?>
                            <?php $this->render_fontsize_input_func('Swatches Height', 'upow_swatches_item_height', $this->options['upow_swatches_item_width'], ''); ?>
                            <?php $this->render_select_item(
                                'upow_swatches_tooltip_pos',
                                'Tooltip Position',
                                [
                                    'top' => 'Top',
                                    'bottom' => 'Bottom',
                                    'left' => 'Left',
                                    'right' => 'Right',
                                ]
                            ); ?>

                            <?php $this->render_fontsize_input_func('Tooltip font size', 'upow_font_size', $this->options['upow_font_size'], '14'); ?>
                            <?php $this->render_fontsize_input_func('Tooltip Width', 'upow_tooltip_box_width', $this->options['upow_tooltip_box_width'], ''); ?>
                            <?php $this->render_fontsize_input_func('Tooltip Height', 'upow_tooltip_box_height', $this->options['upow_tooltip_box_height'], ''); ?>

                            <?php $this->render_color_input('Tooltip Background Color', 'upow_tooltip_background_color', $this->options['upow_tooltip_background_color']); ?>
                            <?php $this->render_color_input('Tooltip Font Color', 'upow_tooltip_font_color', $this->options['upow_tooltip_font_color']); ?>
                        </div>
                    </div>
                    <div class="upow-general-item-save upow-variatio-swatch-button upow-settings-button-top">
                        <input type="submit" class="upow_checkbox_item_save" name="upow_variation_swatches_item_save"
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

}