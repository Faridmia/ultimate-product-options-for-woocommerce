<?php
namespace Ultimate\Upow\Admin\Ajax;

class SwatchVariationAjax
{
     /**
    * extra options fields
    */
    public function upow_swatches_variations_save_options() {
        
        // Check nonce for security
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }
    
        // Check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }
        
        $datas = $_POST;
        unset($datas['action']);
        unset($datas['nonce']);

        // Set default values for specific options
        $default_options = [
            'upow_enable_global_swatch_on_off' => '',
            'upow_enable_clear_btn' => '',
            'upow_enable_swatch_product_page' => '',
            'upow_enable_swatch_shop_page' => '',
            'upow_convert_dropdown_to_label' => '',
            'upow_enable_swith_label' => '',
            'upow_enable_disable_tooltip_design' => '',
            'upow_enable_swatches_image_tooltip' => '',
            'upow_swatches_position' => 'before_cart',
            'upow_swatches_shape_style' => 'squared',
            'upow_swatches_disable_attribute_effect' => 'cross',
            'upow_variations_label_separator_text' => ':',
            'change_ajax_variatios_threadholds' => ':',
            'upow_swatches_item_width' => ':',
            'upow_swatches_item_height' => ':',
            'upow_font_size' => ':',
            'upow_tooltip_box_width' => ':',
            'upow_tooltip_box_height' => ':',
            'upow_tooltip_background_color' => ':',
            'upow_tooltip_font_color' => ':',
            'upow_swatches_tooltip_pos' => ':',
            
        ];

        foreach ( $default_options as $key => $default_value ) {
            if (!isset($datas[$key])) {
                $datas[$key] = $default_value;
            }
        }

        foreach ( $datas as $key => $value ) {
            update_option($key, $value);
        }

        wp_send_json_success();
    }
}