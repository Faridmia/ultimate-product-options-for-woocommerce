<?php
namespace Ultimate\Upow\Admin\Ajax;

class ProductOptionsAjax
{
     /**
    * extra options fields
    */
    public function upow_extra_options_fields_save_options() {
        
        check_ajax_referer('upow_flashsale_nonce', 'nonce');

        // check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }
        
        $datas = $_POST;
        unset($datas['action']);
        unset($datas['nonce']);

        // Set default values for specific options
        $default_options = [
            'upow_extra_feature_on_off_global' => '',
            'upow_global_extra_feature_on_off' => '',
            'upow_accordion_style_on_off' => '',
            'upow_select_product' => '',
            'upow_exclude_product' => '',
            'upow_extra_product_feature_title' => '',
            'upow_addon_title_text_size' => '',
            'upow_addon_title_text_color' => '',
            'addon_item_title_text_color' => '',
            'addon_item_title_bg_color' => '',
            'addon_item_label_text_color' => '',
            'addon_price_info_bg_color' => '',
            'addon_price_info_text_price_color' => '',
            'addon_price_info_border_bottom_color' => '',
            'upow_addon_title_padding' => '',
            'upow_enable_extra_options_checkout_page' => '',
            'upow_show_customer_cart_page' => '',
            'upow_enable_extra_options_order_page' => '',
            
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