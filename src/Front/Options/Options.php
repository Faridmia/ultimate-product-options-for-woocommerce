<?php
namespace Ultimate\Upow\Front\Options;
use Ultimate\Upow\Traitval\Traitval;

class Options
{
    use Traitval;
    public $global_product_id;
    protected $settings = [];

   /*
    * Constructor
    *
    * The constructor adds an action to the 'admin_menu' hook to add custom 
    * submenus to the WordPress admin menu.
    */
    public function __construct()
    {

        $this->settings = [
            'global_extra_feature_on_off' => get_option('upow_extra_feature_on_off_global'),
            'global_feature_on_off' => get_option('upow_global_extra_feature_on_off'),
            'exclude_product'       => get_option('upow_exclude_product'),
            'product_feature_title' => get_option('upow_extra_product_feature_title'),
            'select_product'        => get_option('upow_select_product'),
            'total_field_value'     => get_option('upow_extra_fields_items'),
        ];

        if( $this->settings['global_extra_feature_on_off'] != '1' ) {
            return;
        }
       
        $this->set_currency_position();

        add_action('wp', array( $this, 'initialize_product_id' ) );
        add_action('woocommerce_before_add_to_cart_button', array( $this, 'upow_add_custom_fields_single_page') );
        add_filter('woocommerce_add_to_cart_validation', array( $this, 'upow_validate_custom_fields'), 10, 3 );
        add_filter('woocommerce_add_cart_item_data', array( $this, 'upow_add_custom_fields_to_cart'), 10, 2 );
        add_action('wp_enqueue_scripts', [ $this, 'localize_script'], 100 );

    }

    /**
     * Initialize the global product ID.
     * 
     * This function is hooked into 'wp' to ensure the product is set up before getting the ID.
     * 
     * @since 1.0.0
     *
     * @ return void
     */
    public function initialize_product_id()
    {
        if (is_product()) {
            $this->global_product_id = get_the_ID( );
        }
    }

    /**
     * Localizes the frontend script with product-specific data.
     *
     * This function checks if the 'upow-frontend-script' is enqueued, and if so,
     * it localizes the script with the product ID for use in JavaScript.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function localize_script() {

        // Ensure the script is enqueued before localizing
        if ( wp_script_is('upow-frontend-script', 'enqueued') ) {

            wp_localize_script('upow-frontend-script', 'upow_localize_product_obj', array(
                'productId' => $this->global_product_id
            ));
        }
    }

    /**
     * Returns default query arguments for 'upow_product' posts.
     *
     * @since 1.0.0
     *
     * @return array Default query arguments.
     */
    private function get_default_args() {
        return [
            'post_type'      => 'upow_product',
            'posts_per_page' => 10,
            'order'          => 'DESC',
            'orderby'        => 'date',
        ];
    }

    /**
     * Retrieves the 'upow_product' meta value for the current product.
     *
     * @since 1.0.0
     *
     * @return mixed Meta value of 'upow_product'.
     */
    private function get_upow_product() {

        return get_post_meta( $this->global_product_id, 'upow_product', true );

    }

    /**
     * Get query arguments and upow product meta data
     *
     * @since 1.0.0
     *
     * @return array [ 'args' => array, 'upow_product' => mixed ]
     */
    public function get_upow_query_args() {
        
        $args = [];
        $upow_product = '';
    
        if ( $this->is_global_feature_enabled() ) {

            if ( $this->is_product_excluded() ) {
                $args = [];
                $upow_product = '';
            } else {
                $args = $this->get_default_args();
                $upow_product = $this->get_upow_product();
            }

        } elseif ($this->is_selected_product()) {

            $args = $this->get_default_args();
            $upow_product = $this->get_upow_product();

        }
    
        return ['args' => $args, 'upow_product' => $upow_product];
    }
    
    /**
     * Checks if the global feature is enabled based on the settings.
     *
     * @since 1.0.0
     *
     * @return bool True if the global feature is enabled, false otherwise.
     */
    private function is_global_feature_enabled() {

        return ($this->settings['global_feature_on_off'] === 'yes' || $this->settings['global_feature_on_off'] == 1 || !empty($this->settings['global_feature_on_off'])) 
       && $this->settings['global_feature_on_off'] !== 'no';

    }
    
    /**
     * Determines if the current product is excluded based on settings.
     *
     * Checks if the product ID is present in the exclude product list.
     *
     * @since 1.0.0
     *
     * @return bool True if the product is excluded, false otherwise.
     */
    private function is_product_excluded() {

        if (is_array( $this->settings['exclude_product'] ) || is_object( $this->settings['exclude_product'] ) ) {

            foreach ($this->settings['exclude_product'] as $value) {
                if ($this->global_product_id == $value) {
                    return true;
                }
            }
        }

        return false;

    }
    
    /**
     * Checks if the current product is selected based on settings and global feature status.
     *
     * This function first ensures the global feature is disabled, then checks if 
     * the current product ID is present in the selected product list.
     *
     * @since 1.0.0
     *
     * @return bool True if the product is selected, false otherwise.
     */
    private function is_selected_product() {

        if ( !$this->is_global_feature_enabled() ) {

            if ( is_array( $this->settings['select_product'] ) || is_object( $this->settings['select_product']) ) {
                foreach ( $this->settings['select_product'] as $value ) {
                    if ( $value == $this->global_product_id ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Renders custom field items for each post in the provided custom query.
     *
     * This function checks if the custom query has posts, and if so, iterates through 
     * each post, retrieves the 'upow_product' meta value, and calls the 
     * render_custom_field_item method to display it. 
     * It resets post data after processing the query.
     *
     * @since 1.0.0
     *
     * @param WP_Query $custom_query The custom query object to loop through.
     * @return void
     */
    private function render_custom_query( $custom_query ) {

        if ( !$custom_query->have_posts() ) {
            return;
        }
    
        $main_id = 1;
        while ( $custom_query->have_posts() ) {

            $custom_query->the_post();
            $upow_product = get_post_meta( get_the_ID(), 'upow_product', true);
            $this->render_custom_field_item( $main_id,$upow_product );
            $main_id++;

        }

        wp_reset_postdata();
    }
    
    /**
     * Renders custom field items for a given product.
     *
     * This function checks if the provided product data is valid and non-empty. 
     * If valid, it generates a nonce for security and outputs a hidden nonce field, 
     * along with a title and inner content that includes rendered fields for each 
     * extra item in the product.
     *
     * @since 1.0.0
     *
     * @param int $main_id The main ID used for rendering fields.
     * @param array $upow_product An array of custom product field items to render.
     * @return void
     */
    private function render_custom_field_item( $main_id,$upow_product ) {

        if (empty( $upow_product ) || !is_array( $upow_product ) ) {
            return;
        }
    
        $nonce = wp_create_nonce('upow_template_nonce');
        $total_field_value = get_option('upow_extra_fields_items', true);
        ob_start();
        echo '<input type="hidden" name="upow_template_nonce" value="' . esc_attr($nonce) . '" />';
        echo '<div class="upow-extra-acc-item">
                <div class="upow-extra-title-tab">
                    <h3 class="title">' . get_the_title() . '<span class="icon"></span></h3>
                </div>
                <div class="upow-inner-content">';
    
        $count = 1;
        foreach ( $upow_product as $extra_item ) {
            $this->render_field( $extra_item, $main_id, $count, $total_field_value );
            $count++;
        }
    
        echo '</div></div>';
        $content = ob_get_clean();
        printf('%s', do_shortcode( $content ) );
    }

    /**
     * Renders a custom field based on the provided extra item data.
     *
     * This function generates the HTML for a custom field, including a label and 
     * an input element, handling different field types and required status.
     *
     * @since 1.0.0
     *
     * @param array $extra_item An associative array containing field data, including 
     *                          'field_type', 'field_label', 'default_value', 
     *                          'placeholder_text', and 'required'.
     * @param int $main_id The main ID used for rendering the field.
     * @param int $count The index of the current field being rendered.
     * @param array $total_field_value An associative array containing total field values.
     * @return void
     */
    private function render_field( $extra_item, $main_id, $count, $total_field_value ) {

        $field_type         = $extra_item['field_type'] ?? '';
        $field_label        = $extra_item['field_label'] ?? '';
        $default_value      = htmlspecialchars($extra_item['default_value'] ?? '', ENT_QUOTES, 'UTF-8');
        $placeholder_text   = $extra_item['placeholder_text'] ?? '';
        $required           = !empty($extra_item['required']);
        $label_after        = $required ? '*' : '';
        $required_check     = $required ? 'required' : '';
    
        $field_key          = "upow_{$count}_{$main_id}";
        $main_value         = $total_field_value[$field_key] ?? $default_value;
        $checked            = (!empty($total_field_value[$field_key]) && $field_type != 'text') ? 'checked' : '';
        
        ob_start();
        echo '<input type="hidden" name="upow_item_label_text[]" value="' . esc_attr($field_label) . '" />';
        echo '<div class="upow-extra-options ' . ($field_type == 'text' ? 'upow-input-field-group' : '') . '">';
        echo '<label>';
    
        if ($field_type === 'text') {
            echo '<span class="upow-item-label-text">' . esc_html($field_label) . esc_html($label_after) . '</span>';
            echo '<input type="text" name="upow_custom_field_items_data[' . esc_attr($field_key) . ']" value="' . esc_attr($main_value) . '" data-price="' . esc_attr($main_value) . '" placeholder="' . esc_attr($placeholder_text) . '" class="' . esc_attr($required_check) . ' upow-text-field upow-check-change" />';
        } else {
            echo '<input type="' . esc_attr($field_type) . '" name="upow_custom_field_items_data[' . esc_attr($field_key) . ']" value="' . esc_attr($default_value) . '" ' . $checked . ' data-price="' . esc_attr($default_value) . '" class="' . esc_attr($required_check) . ' upow-check-change" />';
            echo '<span class="upow-item-label-text">' . esc_html($field_label) . esc_html($label_after) . '</span>';
        }
    
        echo '</label>';
        if ($field_type != 'text') { ?>
            <span class="upow-extra-item-price">
                <?php echo  wp_kses_post( $this->currency_left . $default_value . $this->currency_right ); ?>
            </span>
        <?php } 
        echo '</div>';
        $content = ob_get_clean();
        printf('%s', do_shortcode( $content ) );

    }
    

    /**
     * Renders specific custom item fields for a given product.
     *
     * This function outputs HTML for extra product fields based on the provided 
     * product data, including field type, label, default value, and whether 
     * the field is required.
     *
     * @since 1.0.0
     *
     * @param int $global_product_id The ID of the global product.
     * @param array $upow_product An associative array containing custom field data for the product.
     * @return void
     */
    public function upow_specific_product_item_fields( $global_product_id, $upow_product ) {

        if ( empty( $upow_product ) || !is_array( $upow_product ) ) {
            return;
        }
    
        $main_id = 2;
        $total_field_value = get_option('upow_extra_fields_items', true);
    
        foreach ( $upow_product as $values ) {

            $field_type         = $values['field_type'] ?? '';
            $field_label        = $values['field_label'] ?? '';
            $default_value      = $values['default_value'] ?? '';
            $required           = !empty($values['required']);
            $placeholder_text   = $values['placeholder_text'] ?? '';
            $required_check     = $required ? 'required' : '';
            $label_after        = $required ? '*' : '';
    
            $field_key          = "upow_{$main_id}_{$global_product_id}";
            $main_value         = $total_field_value[$field_key] ?? $default_value;
            $checked            = ($field_type !== 'text' && !empty($total_field_value[$field_key])) ? 'checked' : '';
            ob_start();
            echo '<input type="hidden" name="upow_item_label_text[]" value="' . esc_attr($field_label) . '" />';
            echo '<div class="upow-extra-options ' . ($field_type === 'text' ? 'upow-input-field-group' : '') . '">';
            echo '<label>';
    
            if ($field_type === 'text') {
                echo '<span class="upow-item-label-text">' . esc_html($field_label) . esc_html($label_after) . '</span>';
                echo '<input type="text" name="upow_custom_field_items_data[' . esc_attr($field_key) . ']" value="' . esc_attr($main_value) . '" data-price="' . esc_attr($main_value) . '" placeholder="' . esc_attr($placeholder_text) . '" class="' . esc_attr($required_check) . '" />';
            } else {
                echo '<input type="' . esc_attr($field_type) . '" name="upow_custom_field_items_data[' . esc_attr($field_key) . ']" value="' . esc_attr($default_value) . '" ' . $checked . ' data-price="' . esc_attr($default_value) . '" class="' . esc_attr($required_check) . '" />';
                echo esc_html($field_label) . esc_html($label_after);
            }
    
            echo '</label>';
            if ($field_type != 'text') { ?>
                <span class="upow-extra-item-price">
                <?php echo  wp_kses_post( $this->currency_left . $default_value . $this->currency_right ); ?>
                </span>
            <?php } 
            echo "</div>";
    
            $main_id++;
            $content = ob_get_clean();
            printf('%s', do_shortcode( $content ) );

        }
    }

    /**
     * Adds custom fields to the WooCommerce product single page.
     *
     * This function hooks into 'woocommerce_before_add_to_cart_button' to display custom fields
     * on the product single page. These fields will appear before the "Add to cart" button.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function upow_add_custom_fields_single_page() {

        global $product;
        global $post;

        $query_data            = $this->get_upow_query_args();
        $args                  = $query_data['args'];
        $upow_product          = $query_data['upow_product'];
        $custom_query          = new \WP_Query( $args );
        $product_feature_title = $this->settings['upow_extra_product_feature_title'];

        $global_product_id = $this->global_product_id;

        if ( empty( $custom_query )  && empty( $upow_product )  ) {
            return;
        }
        
        if ( $custom_query->have_posts() ||  is_array( $upow_product ) || is_object( $upow_product )  ) {
            ob_start();
        ?>
        <div class="upow-extra-acc-wrap">
            <?php if( isset( $product_feature_title ) && !empty( $product_feature_title ) ) { ?>
            <label for="iconic-engraving">
                <h2 class="upow-extra-wrap-title"><?php echo wp_kses_post( $product_feature_title ); ?></h2>
            </label>
            <?php
            }
            $this->upow_specific_product_item_fields( $global_product_id, $upow_product );
            ?>
            <div class="upow-extra-acc">
                <?php
                $this->render_custom_query( $custom_query );
                ?>
                <div class="upow-extra-addons-pricing-info">
                    <div class="upow-options-total-prices">
                        <p>
                            <span class="upow-total-price-label"><?php esc_html_e('Options Amount:', 'ultimate-product-options-for-woocommerce'); ?></span>
                            <span class="upow-options-total-price">0</span>
                        </p>
                    </div>
                    <div class="upow-total-prices">
                        <p>
                            <span class="upow-total-price-label"><?php esc_html_e('Final Total:', 'ultimate-product-options-for-woocommerce'); ?></span>
                            <span class="upow-total-price"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        printf('%s', do_shortcode( $content ) );

        }
    }

    /**
     * Validates custom fields submitted with a product.
     *
     * This function checks if the nonce for template validation is set and 
     * verifies it, then checks if custom field data is present in the 
     * submitted form data.
     *
     * @since 1.0.0
     *
     * @param bool $passed Indicates if the validation has passed or failed.
     * @param int $product_id The ID of the product being validated.
     * @param int $quantity The quantity of the product being purchased.
     * @return bool $passed The modified validation status.
     */
    public function upow_validate_custom_fields( $passed, $product_id, $quantity )
    {

        if ( ! isset( $_POST['upow_template_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['upow_template_nonce'] ) ) , 'upow_template_nonce' ) ) {
            return;
        }

        if (isset($_POST['upow_custom_field_items_data']) && !empty($_POST['upow_custom_field_items_data'])) {
            $passed = true;
        }

        return $passed;
    }

    /**
     * Adds custom fields to cart item data.
     *
     * This function hooks into 'woocommerce_add_cart_item_data' to add custom field data
     * to the cart items. It checks if the custom field 'upow_custom_field_items_data' is set and adds it to the cart item data.
     *
     * @since 1.0.0
     *
     * @param array $cart_item_data The cart item data array.
     * @param int $product_id The ID of the product being added to the cart.
     * @return array Modified cart item data with custom fields.
     */
    public function upow_add_custom_fields_to_cart( $cart_item_data, $product_id )
    {

        if ( ! isset( $_POST['upow_template_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['upow_template_nonce'] ) ) , 'upow_template_nonce' ) ) {
            return;
        }

        if (isset($_POST['upow_custom_field_items_data'])) {

            $upow_item_label_text   = isset($_POST['upow_item_label_text']) ? wc_clean($_POST['upow_item_label_text']) : array();
            $extra_item_data        = array();
            $extra_item_prices      = 0;
            
            foreach ( sanitize_upow_custom_field_items_data( wp_unslash($_POST['upow_custom_field_items_data'] ) ) as $key => $value ) {

                if (is_array($value)) {
                    foreach ($value as $sub_value) {
                        $extra_item_prices += floatval($sub_value);
                        $extra_item_data[$key][] = wc_clean($sub_value);
                    }
                } else {
                    $extra_item_prices += floatval($value);
                    $extra_item_data[$key] = wc_clean($value);
                }
            }

            // Store custom field data in cart item
            $cart_item_data['upow_custom_field_items_data']        = $extra_item_data;
            $cart_item_data['upow_custom_field_items_data_price']  = $extra_item_prices;
            $cart_item_data['upow_item_label_text']                = $upow_item_label_text;
            update_option('upow_extra_fields_items', $cart_item_data['upow_custom_field_items_data']);

        }

        return $cart_item_data;
    }
}
