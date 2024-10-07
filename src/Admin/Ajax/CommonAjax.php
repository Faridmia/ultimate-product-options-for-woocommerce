<?php
namespace Ultimate\Upow\Admin\Ajax;

class CommonAjax
{
    public function get_all_product_options() {
        // Verify nonce for security

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }
    
        // check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }
        // Generate options HTML using your existing function
        $options_html = upow_all_product_panel_output(); // Adjust as necessary
    
        // Return the HTML as a JSON response
        wp_send_json_success($options_html);
    }

    public function upow_get_exclude_all_product_options() {

        // Verify nonce for security
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }
    
        // check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        // Generate options HTML using your existing function
        $options_html = upow_exclude_product_panel_output(); // Adjust as necessary
    
        // Return the HTML as a JSON response
        wp_send_json_success($options_html);
    }

    public function upow_get_all_product_categories() {

        // Verify nonce for security
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'upow_flashsale_nonce' ) ) {
            wp_send_json_error('Invalid nonce');
            exit;
        }
    
        // check manage options capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        // Generate options HTML using your existing function
        $options_html = upow_get_product_categories();
    
        // Return the HTML as a JSON response
        wp_send_json_success($options_html);
    }
}