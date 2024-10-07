<?php
namespace Ultimate\Upow\Admin\Ajax;

class FlashSaleAjax
{
    
    public function save_popup_settings() {
        
        // Check nonce for security
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }
    
        // Check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }
    
        $enable_flash_sale = isset($_POST['enable_flash_sale']) ? sanitize_text_field($_POST['enable_flash_sale']) : 0;
        $override_saleflash = isset($_POST['override_saleflash']) ? sanitize_text_field($_POST['override_saleflash']) : 0;
    
        // Initialize data array
        $data = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
    
        // Save data to options or a custom table
        update_option('upow_flash_sale_settings', $data);
        update_option('upow_enable_flash_sale_here', $enable_flash_sale);
        update_option('upow_override_saleflash', $override_saleflash);
    
        wp_send_json_success('Settings saved successfully!');
    }
    

    /**
     * flashsale settings save data
    */

    public function upow_flashsale_settings_save_options() {
    
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
            'upow_show_countdown_single_page' => '',
            'offer_title_text_color' => '',
            'upow_countdown_timer_title' => '',
            'offer_title_bg_color' => '',
            'upow_countdown_position' => '',
            'upow_countdown_style' => '',
            'offer_countdown_text_color' => '',
            'offer_countdown_bg_color' => '',
            'offer_countdown_item_title_color' => '',
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