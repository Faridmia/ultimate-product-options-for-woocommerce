<?php
namespace Ultimate\Upow\Admin\Preorder;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Admin
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class Preorder
{
    use Traitval;

    public $preorder_enable = '';

    public function __construct() {

        add_filter( 'product_type_options', array( $this, 'pre_order_checkbox' ), 5 );
        
        // Save Pre-order checkbox value
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_pre_order_checkbox' ) );
        add_filter('woocommerce_product_data_tabs', array( $this,'upow_product_tab'));
        add_action('woocommerce_product_data_panels', array( $this,'upow_options_product_preorder_data_tab_content') );
        add_action('woocommerce_process_product_meta', array( $this,'save_preorder_product_options_field') );

        add_action( 'woocommerce_variation_options', array( $this, 'upow_preorder_action_woocommerce_variation_options' ), 10, 3);
        add_action( 'woocommerce_save_product_variation', array( $this, 'upow_preorder_action_woocommerce_save_product_variation' ), 10, 2 );

        add_action('woocommerce_variation_options', array( $this,'add_pre_order_fields_to_variation' ), 10, 3);
        add_action('woocommerce_save_product_variation', array( $this, 'save_pre_order_fields_variation' ), 10, 2);

        add_action('admin_enqueue_scripts', [ $this, 'upow_enqueue_preorder_admin_assets' ]);

    }

    /**
     * Enqueue admin assets for preorder
     *
     * This function enqueues the necessary JavaScript or css file for swatch functionality in the admin panel.
     * It only loads the script when inside the WordPress admin dashboard.
     *
     * @return void
     */
    public function upow_enqueue_preorder_admin_assets()
    {

        if( is_admin(  ) ) {
            // Enqueue admin JS
            wp_enqueue_script('upow-preorder-admin-js', UPOW_CORE_ASSETS . 'src/Admin/Preorder/assets/js/preorder-admin.js', array('jquery'), UPOW_VERSION, true);
            wp_enqueue_script('upow-preorder-admin-css', UPOW_CORE_ASSETS . 'src/Admin/Preorder/assets/css/preorder-admin.css', array(), UPOW_VERSION, true);

        }
        
    }

    // Add the Pre-order checkbox to product type options
    public function pre_order_checkbox( $options ) {
       
        $options['upow_preorder_sample'] = array(
            'id'            => 'upow_preorder_sample',
            'wrapper_class' => 'show_if_simple hide_if_bundle', 
            'label'         => __( 'Pre-order', 'ultimate-product-options-for-woocommerce' ),
            'description'   => __( 'Enable this to allow pre-orders for this product.', 'ultimate-product-options-for-woocommerce' ),
            'default'       => 'no',
        );
        
        return $options;
    }


    // Save the Pre-order checkbox value
    public function save_pre_order_checkbox( $post_id ) {

        $is_preorder       = isset( $_POST['upow_preorder_sample'] ) && sanitize_text_field( wp_unslash( $_POST['upow_preorder_sample'] ) ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_upow_preorder_sample', $is_preorder );
        
    }

    public function upow_product_tab( $tabs )
    {

        $tabs['pre_order'] = array(
            'label'	 => __('Pre-Order', 'ultimate-product-options-for-woocommerce'),
            'target' => 'upow_preorder_product_options',
            'class'  => array('show_if_simple_product hide_if_external hide_if_grouped upow_preorder_options'),
        );

        return $tabs;
    }


    public function upow_options_product_preorder_data_tab_content()
    {
        global $post;
    ?>
        <div id='upow_preorder_product_options' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <?php
                // Custom Product Checkbox Field

                // Available Quantity (Number Field)
                woocommerce_wp_text_input( array(
                    'id'          => '_upow_preorder_available_quantity',
                    'label'       => __( 'Preorder Limit', 'ultimate-product-options-for-woocommerce' ),
                    'desc_tip'    => 'true',
                    'description' => __( 'Enter the quantity available for backorder.', 'ultimate-product-options-for-woocommerce' ),
                    'type'        => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1',
                    ),
                ));
                
                woocommerce_wp_text_input( array(
                    'id'          => '_upow_preorder_availability_date',
                    'label'       => __( 'Availability Date', 'ultimate-product-options-for-woocommerce' ),
                    'desc_tip'    => 'true',
                    'description' => __( 'Select the date the product will be available.', 'ultimate-product-options-for-woocommerce' ),
                    'type'        => 'datetime-local', // Use 'datetime-local' for date and time
                    'placeholder' => 'YYYY-MM-DDTHH:MM',
                ));

                // Availability Message (Text Field)
                woocommerce_wp_text_input( array(
                    'id'          => '_upow_preorder_availability_message',
                    'label'       => __( 'Availability Message', 'ultimate-product-options-for-woocommerce' ),
                    'default'     => __("On Preorder: Will be available on",'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __( 'Enter a custom message regarding availability.', 'ultimate-product-options-for-woocommerce' ),
                    'type'        => 'text',
                ));

                woocommerce_wp_text_input( array(
                    'id'          => '_upow_preorder_pre_released_message',
                    'label'       => __( 'Pre-Release Message', 'ultimate-product-options-for-woocommerce' ),
                    'desc_tip'    => 'true',
                    'description' => __( 'Enter a pre released message.', 'ultimate-product-options-for-woocommerce' ),
                    'type'        => 'text',
                ));

                woocommerce_wp_select( array(
                    'id'          => '_upow_preorder_manage_price', // Field ID
                    'label'       => __('Manage Price', 'ultimate-product-options-for-woocommerce'), // Label for the select field
                    'description' => __('Select an option for this product.', 'ultimate-product-options-for-woocommerce'), // Description for the select field
                    'options'     => array( // Options for the select field
                        ''        => __('Product Price', 'ultimate-product-options-for-woocommerce'),
                        'increase_price' => __('Increase Price', 'ultimate-product-options-for-woocommerce'),
                        'decrease_price' => __('Decrease Price', 'ultimate-product-options-for-woocommerce'),
                        'fixed_price' => __('Fixed Price', 'ultimate-product-options-for-woocommerce'),
                    ),
                    'desc_tip'    => true, // Show tooltip
                ) );

                woocommerce_wp_select( array(
                    'id'          => '_upow_preorder_amount_type', // Field ID
                    'label'       => __('Amount Type', 'ultimate-product-options-for-woocommerce'), // Label for the select field
                    'description' => __('Select an option for this product.', 'ultimate-product-options-for-woocommerce'), // Description for the select field
                    'options'     => array( // Options for the select field
                        'fixed_amount' => __('Fixed Amount', 'ultimate-product-options-for-woocommerce'),
                        'percentage' => __('Percentage', 'ultimate-product-options-for-woocommerce'),
                    ),
                    'desc_tip'    => true, // Show tooltip
                ) );

                woocommerce_wp_text_input( array(
                    'id'          => '_upow_preorder_amount',
                    'label'       => __( 'Amount', 'ultimate-product-options-for-woocommerce' ),
                    'desc_tip'    => 'true',
                    'description' => __( 'Enter a pre order amount.', 'ultimate-product-options-for-woocommerce' ),
                    'type'        => 'number',
                ));

                ?>
            </div>
        </div>
    <?php
    }

    public function save_preorder_product_options_field( $post_id )
    {

        // service post meta update

        if (isset($_POST['_upow_preorder_availability_date'])) :
            update_post_meta($post_id, '_upow_preorder_availability_date', sanitize_text_field($_POST['_upow_preorder_availability_date']));
        endif;

        if (isset($_POST['_upow_preorder_available_quantity'])) :
            update_post_meta($post_id, '_upow_preorder_available_quantity', sanitize_text_field($_POST['_upow_preorder_available_quantity']));
        endif;

        if (isset($_POST['_upow_preorder_availability_message'])) :
            update_post_meta($post_id, '_upow_preorder_availability_message', sanitize_text_field($_POST['_upow_preorder_availability_message']));
        endif;

        if (isset($_POST['_upow_preorder_pre_released_message'])) :
            update_post_meta($post_id, '_upow_preorder_pre_released_message', sanitize_text_field($_POST['_upow_preorder_pre_released_message']));
        endif;

        if (isset($_POST['_upow_preorder_manage_price'])) :
            update_post_meta( $post_id, '_upow_preorder_manage_price', sanitize_text_field( $_POST['_upow_preorder_manage_price'] ) );

        endif;

        if (isset($_POST['_upow_preorder_amount'])) :
            update_post_meta( $post_id, '_upow_preorder_amount', sanitize_text_field( $_POST['_upow_preorder_amount'] ) );
        endif;

        if (isset($_POST['_upow_preorder_amount_type'])) :
            update_post_meta( $post_id, '_upow_preorder_amount_type', sanitize_text_field( $_POST['_upow_preorder_amount_type'] ) );
        endif;

        

    }

    // varition product custom preorder  checkbox fields add
    public function upow_preorder_action_woocommerce_variation_options( $loop, $variation_data, $variation ) {

        $is_checked = get_post_meta( $variation->ID, '_upow_preorder_variable_product', true );

        if ( $is_checked == 'yes' ) {
            $is_checked = 'checked';
        } else {
            $is_checked = '';     
        }

        ?>
        <label class="tips" data-tip="<?php esc_attr_e( 'Enable Pre-Order', 'ultimate-product-options-for-woocommerce' ); ?>">
            <?php esc_html_e( 'Enable Pre-Order:', 'ultimate-product-options-for-woocommerce' ); ?>
            <input type="checkbox" class="checkbox upow_variable_checkbox" name="_upow_preorder_variable_product[<?php echo esc_attr( $loop ); ?>]"<?php echo esc_attr($is_checked); ?>/>
            <?php
            echo sprintf(
                '<span class="woocommerce-help-tip" data-tip="%s"></span>',
                esc_attr__( 'Enable this option to set this variation to the Pre-Order status.', 'ultimate-product-options-for-woocommerce' )
            );
            ?>

        </label>

        <?php
    }

    // Save checkbox
    public function upow_preorder_action_woocommerce_save_product_variation( $variation_id, $i ) {

        if ( ! empty( $_POST['_upow_preorder_variable_product'] ) && ! empty( $_POST['_upow_preorder_variable_product'][$i] ) ) {
            update_post_meta( $variation_id, '_upow_preorder_variable_product', 'yes' );
        } else {
            update_post_meta( $variation_id, '_upow_preorder_variable_product', 'no' ); 
        }  

    }


    // Add fields to each variation
   
    public function add_pre_order_fields_to_variation( $loop, $variation_data, $variation ) { ?>

        <div id="preorder_product_options_<?php echo esc_attr( $loop); ?>" class='panel woocommerce_options_panel preorder-fields' style="display: <?php echo get_post_meta($variation->ID, '_upow_preorder_variable_product', true) === 'yes' ? 'block' : 'none'; ?>;">
            <div class='options_group'>
                <?php 
                // Available Quantity (Number Field)
                woocommerce_wp_text_input(array(
                    'id'          => "_upow_preorder_available_quantity_{$loop}",
                    'label'       => __('Preorder Limit', 'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __('Enter the quantity available for backorder.', 'ultimate-product-options-for-woocommerce'),
                    'type'        => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1',
                    ),
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_available_quantity', true),
                ));

                // Availability Date (Date and Time Field)
                woocommerce_wp_text_input(array(
                    'id'          => "_upow_preorder_availability_date_{$loop}",
                    'label'       => __('Availability Date', 'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __('Select the date the product will be available.', 'ultimate-product-options-for-woocommerce'),
                    'type'        => 'datetime-local',
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_availability_date', true),
                    'placeholder' => 'YYYY-MM-DDTHH:MM',
                ));

                // Availability Message (Text Field)
                woocommerce_wp_text_input(array(
                    'id'          => "_upow_preorder_availability_message_{$loop}",
                    'label'       => __('Availability Message', 'ultimate-product-options-for-woocommerce'),
                    'default'     => __("On Preorder: Will be available on", 'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __('Enter a custom message regarding availability.', 'ultimate-product-options-for-woocommerce'),
                    'type'        => 'text',
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_availability_message', true),
                ));

                // Pre-Release Message (Text Field)
                woocommerce_wp_text_input(array(
                    'id'          => "_upow_preorder_pre_released_message_{$loop}",
                    'label'       => __('Pre-Release Message', 'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __('Enter a pre-released message.', 'ultimate-product-options-for-woocommerce'),
                    'type'        => 'text',
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_pre_released_message', true),
                ));

                // Manage Price (Select Field)
                woocommerce_wp_select(array(
                    'id'          => "_upow_preorder_manage_price_{$loop}",
                    'label'       => __('Manage Price', 'ultimate-product-options-for-woocommerce'),
                    'description' => __('Select an option for this product.', 'ultimate-product-options-for-woocommerce'),
                    'options'     => array(
                        ''               => __('Product Price', 'ultimate-product-options-for-woocommerce'),
                        'increase_price' => __('Increase Price', 'ultimate-product-options-for-woocommerce'),
                        'decrease_price' => __('Decrease Price', 'ultimate-product-options-for-woocommerce'),
                        'fixed_price'    => __('Fixed Price', 'ultimate-product-options-for-woocommerce'),
                    ),
                    'desc_tip'    => true,
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_manage_price', true),
                ));
                woocommerce_wp_select( array(
                    'id'          => "_upow_preorder_amount_type_{$loop}",
                    'label'       => __('Amount Type', 'ultimate-product-options-for-woocommerce'), 
                    'description' => __('Select an option for this product.', 'ultimate-product-options-for-woocommerce'), 
                    'options'     => array( 
                        'fixed_amount' => __('Fixed Amount', 'ultimate-product-options-for-woocommerce'),
                        'percentage' => __('Percentage', 'ultimate-product-options-for-woocommerce'),
                    ),
                    'desc_tip'    => true, 
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_amount_type', true),
                ) );

                // Amount (Number Field)
                woocommerce_wp_text_input(array(
                    'id'          => "_upow_preorder_amount_{$loop}",
                    'label'       => __('Amount', 'ultimate-product-options-for-woocommerce'),
                    'desc_tip'    => 'true',
                    'description' => __('Enter a pre-order amount.', 'ultimate-product-options-for-woocommerce'),
                    'type'        => 'number',
                    'value'       => get_post_meta($variation->ID, '_upow_preorder_amount', true),
                ));
                ?>
            </div>
        </div>

        <?php
    }

    // Save fields data for each variation
    
    public function save_pre_order_fields_variation( $variation_id, $i ) {

        // Save Available Quantity
        $available_quantity = isset($_POST["_upow_preorder_available_quantity_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_available_quantity_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_available_quantity', $available_quantity);

        // Save Availability Date
        $availability_date = isset($_POST["_upow_preorder_availability_date_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_availability_date_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_availability_date', $availability_date);

        // Save Availability Message
        $availability_message = isset($_POST["_upow_preorder_availability_message_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_availability_message_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_availability_message', $availability_message);

        // Save Pre-Release Message
        $pre_released_message = isset($_POST["_upow_preorder_pre_released_message_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_pre_released_message_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_pre_released_message', $pre_released_message);

        // Save Manage Price
        $manage_price = isset($_POST["_upow_preorder_manage_price_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_manage_price_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_manage_price', $manage_price);

        $amount_type = isset($_POST["_upow_preorder_amount_type_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_amount_type_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_amount_type', $amount_type );

        // Save Amount
        $amount = isset($_POST["_upow_preorder_amount_{$i}"]) ? sanitize_text_field($_POST["_upow_preorder_amount_{$i}"]) : '';
        update_post_meta($variation_id, '_upow_preorder_amount', $amount);

    }


}