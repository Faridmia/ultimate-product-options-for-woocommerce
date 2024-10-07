<?php
namespace Ultimate\Upow\Admin\Ajax;

class BackorderAjax
{
     /**
    * extra options fields
    */
    public function upow_backorder_options_fields_save_options() {
        
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
            'upow_backorder_on_off' => '',
            'upow_backorder_label_text' => '',
            'upow_backorder_addto_cart_text' => '',
            
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