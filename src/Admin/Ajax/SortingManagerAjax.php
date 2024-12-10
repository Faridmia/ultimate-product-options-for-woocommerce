<?php
namespace Ultimate\Upow\Admin\Ajax;

class SortingManagerAjax
{
     /**
    * extra options fields
    */
    public function upow_sorting_manager_fields_save_options() {
        // Check nonce for security
        check_ajax_referer('upow_flashsale_nonce', 'nonce');
    
        if ( !current_user_can('manage_options') ) {
            wp_send_json_error('Unauthorized user');
        }
    
        // Retrieve posted data
        $datas = $_POST;

        if ( isset( $_POST['upow_sorting_on_off'] ) && !empty($_POST['upow_sorting_on_off'] )) {
            $upow_sorting_on_off = sanitize_text_field( wp_unslash( $_POST['upow_sorting_on_off'] ) );
            update_option( 'upow_sorting_on_off', $upow_sorting_on_off );
        } else {
            update_option( 'upow_sorting_on_off', 0 );
        }
    
        unset($datas['action']);
        unset($datas['nonce']);
    
        $default_options = [
            'default' => [
                'menu_order' => 0,
                'popularity' => 0,
                'rating' => 0,
                'date' => 0,
                'price' => 0,
                'price-desc' => 0
            ],
            'custom' => [
                'alphabetical' => 0,
                'reverse_alpha' => 0,
                'by_stock' => 0,
                'review_count' => 0,
                'on_sale_first' => 0
            ]
        ];
    
        $final_data = array_replace_recursive($default_options, $datas['upow_wc_advanced_sorting_settings'] ?? []);
    
        foreach ( $final_data as $group => $options ) {
            foreach ( $options as $key => $value ) {
                update_option("upow_wc_advanced_sorting_settings[{$group}][{$key}]", $value);
            }
        }
    
        wp_send_json_success('Settings saved successfully');
    }
}
    