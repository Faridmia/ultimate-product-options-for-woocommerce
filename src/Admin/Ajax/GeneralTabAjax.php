<?php
namespace Ultimate\Upow\Admin\Ajax;

class GeneralTabAjax
{
    /**
     * general settings save data
    */
    public function upow_general_settings_save_options() {
    
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }

        // check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }


        $datas = $_POST;
        unset($datas['action']);
        unset($datas['nonce']);

        // Set default values for specific options
        $default_options = [
            'upow_custom_css' => '',
            'upow_custom_js' => '',
            'upow_google_analytics' => '',
            'upow_product_per_page' => '',
        ];

        foreach ($default_options as $key => $default_value) {
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