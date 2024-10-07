<?php
namespace Ultimate\Upow\Front\SwatchVariation;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class SwatchVariation
{
    use Traitval;

    // Class properties
    protected $settings = [];

    public function __construct() {

        // Fetch all options at once
        $this->settings = [
            'swatch_variation_enable'             => get_option('upow_enable_global_swatch_on_off', true),
            'shop_variation_onoff'                => get_option('upow_enable_swatch_shop_page', true),
            'enable_swatch_product_page'          => get_option('upow_enable_swatch_product_page', true),
            'enable_disable_tooltip_design'       => get_option('upow_enable_disable_tooltip_design', true),
            'enable_swatches_image_tooltip'       => get_option('upow_enable_swatches_image_tooltip', true),
            'enable_swith_label'                  => get_option('upow_enable_swith_label', true),
            'variations_label_separator'          => get_option('upow_variations_label_separator_text', true),
            'convert_dropdown_to_label'           => get_option('upow_convert_dropdown_to_label', true),
            'ajax_variations_thresholds'          => get_option('change_ajax_variations_thresholds', true),
            'swatches_tooltip_pos'                => get_option('upow_swatches_tooltip_pos', true),
            'swatches_item_height'                => get_option('upow_swatches_item_height', true),
            'swatches_item_width'                 => get_option('upow_swatches_item_width', true),
            'tooltip_box_width'                   => get_option('upow_tooltip_box_width', true),
            'tooltip_box_height'                  => get_option('upow_tooltip_box_height', true),
            'font_size'                           => get_option('upow_font_size', true),
            'tooltip_background_color'            => get_option('upow_tooltip_background_color', true),
            'tooltip_font_color'                  => get_option('upow_tooltip_font_color', true),
            'swatches_shape_style'                => get_option('upow_swatches_shape_style', true),
            'swatches_disable_attribute_effect'   => get_option('upow_swatches_disable_attribute_effect', true),
            'enable_clear_btn'                    => get_option('upow_enable_clear_btn', true),
            'swatches_position'                   => apply_filters('upow_swatches_position', get_option('upow_swatches_position', 'after_cart'))
        ];

        // echo $this->settings['ajax_variations_thresholds'];

        

        // Return early if swatch variation is disabled
        if (empty($this->settings['swatch_variation_enable'])) {
            return;
        }

        $this->init();
        // Initialize hooks
        $this->initialize_hooks();

        

        // Add custom CSS
       add_action('wp_head', [ $this, 'swatch_custom_css'] );

    }

    /**
     * Initialize hooks and filters for the class.
     */
    protected function initialize_hooks() {

        // Enqueue frontend and admin assets
        
        add_action('admin_enqueue_scripts', [ $this, 'upow_enqueue_swatch_admin_assets' ]);
        add_action('wp_enqueue_scripts', [ $this, 'upow_enqueue_swatch_frontend_assets' ]);

        // Product attribute filters and actions
        add_action('woocommerce_product_option_terms', [ $this, 'upow_attr_select' ], 10, 3);
        add_filter('product_attributes_type_selector', [ $this, 'upow_add_attr_type' ]);
        add_filter('woocommerce_dropdown_variation_attribute_options_html', [ $this, 'upow_swatches_html' ], 222, 2);

        // Save actions and custom class
        add_action('edited_pa_color', [ $this, 'upow_save_color' ]);
        add_action('woocommerce_before_variations_form', [ $this, 'upow_add_custom_class_to_variations_table' ]);
        add_action('woocommerce_after_variations_form', [ $this, 'upow_modify_variations_table_class' ]);

        // Display swatch variations
        add_filter('upow_variations_switch_woocommerce', [ $this, 'upow_display_swatch_variations' ], 10, 1);

        add_action('wp_ajax_upow_load_variation', [ $this,'upow_load_variation'] );
        add_action('wp_ajax_nopriv_upow_load_variation', [ $this,'upow_load_variation']);
        
        // Increase variation threshold
        add_filter('woocommerce_ajax_variation_threshold', [ $this, 'upow_increase_wc_variation_threshold' ], 10, 2);
        
        // Shop page swatches
        $this->swatches_position_shop_page();
    }

    /**
     * Initialize custom functionalities.
     * 
     * This method is used to initialize the custom management of attribute terms.
     *
     * @return void
     */
    public function init() {
        self::manage_attribute_term(); 
    }

    /**
     * Enqueue admin assets for swatch variation.
     *
     * This function enqueues the necessary JavaScript or css file for swatch functionality in the admin panel.
     * It only loads the script when inside the WordPress admin dashboard.
     *
     * @return void
     */
    public function upow_enqueue_swatch_admin_assets()
    {

        if( is_admin(  ) ) {
            // Enqueue admin JS
            wp_enqueue_script('upow-switch-admin-js', UPOW_CORE_ASSETS . 'src/Front/SwatchVariation/js/switch-admin.js', array('jquery'), UPOW_VERSION, true);
        }
        
    }

    /**
     * Enqueue frontend assets for swatch variation.
     *
     * This function enqueues the necessary CSS and JavaScript files for the swatch functionality on the frontend.
     *
     * @return void
     */
    public function upow_enqueue_swatch_frontend_assets() {

        wp_enqueue_style('upow-switch-front-css', UPOW_CORE_ASSETS . 'src/Front/SwatchVariation/css/swatch-front.css', array(), UPOW_VERSION);
        wp_enqueue_script('upow-switch-front-js', UPOW_CORE_ASSETS . 'src/Front/SwatchVariation/js/swatch-frontend.js', array('jquery'), time(), true);
        wp_enqueue_script('wc-add-to-cart-variation');
        
    }

    /**
     * Add custom hooks for handling attribute columns and fields.
     *
     * This method dynamically adds filters and actions for custom taxonomy columns and edit form fields.
     * It sets up custom columns for the taxonomy and ensures the proper callbacks are used for rendering.
     *
     * @param string $taxonomy_name   The name of the taxonomy to modify.
     * @param string $column_callback The callback method for adding the taxonomy column.
     *
     * @return void
     */
    public static function add_attribute_hooks( $taxonomy_name, $column_callback ) {

        add_filter( 'manage_edit-' . $taxonomy_name . '_columns', array( static::class, $column_callback ) );
        add_filter( 'manage_' . $taxonomy_name . '_custom_column', array( static::class, 'create_attribute_column_content' ), 10, 3 );
        add_action( $taxonomy_name . '_edit_form_fields', array( static::class, 'upow_edit_fields' ), 10, 2 );

    }

    /**
     * Manage WooCommerce attribute terms and add custom hooks for each attribute type.
     *
     * This method retrieves all WooCommerce attribute taxonomies and adds the appropriate hooks 
     * for each attribute type (color, image, label). It dynamically assigns the correct callback 
     * for handling custom columns based on the attribute type.
     *
     * The function handles three attribute types:
     * - `color_type`: Adds hooks for handling color attributes.
     * - `image_type`: Adds hooks for handling image attributes.
     * - `label_type`: Adds hooks for handling label attributes.
     *
     * @return void
     */
    public static function manage_attribute_term() {

        $COLOR = 'color_type';
        $IMAGE = 'image_type';
        $LABEL = 'label_type';

        // Get all WooCommerce attribute taxonomies
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if ( empty( $attribute_taxonomies ) ) {
            return;
        }

        foreach ( $attribute_taxonomies as $taxonomy ) {
            $taxonomy_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );

            switch ( $taxonomy->attribute_type ) {
            case $COLOR:
                static::add_attribute_hooks( $taxonomy_name, 'color_attribute_column' );
                break;

            case $IMAGE:
                static::add_attribute_hooks( $taxonomy_name, 'image_attribute_column' );
                break;

            case $LABEL:
                static::add_attribute_hooks( $taxonomy_name, 'label_attribute_column' );
                break;

            default:
                break;
            }
        }

    }

    /**
     * Add a custom attribute column to the WooCommerce attribute table.
     *
     * This function inserts a custom column into the WooCommerce attribute admin table. It repositions
     * the checkbox and name columns if they exist, and then adds the custom column based on the provided
     * column key and label.
     *
     * @param array  $columns      The existing columns in the attribute admin table.
     * @param string $column_key   The key for the new custom column.
     * @param string $column_label The label for the new custom column.
     *
     * @return array Modified columns with the custom column added.
     */
    public static function add_custom_attribute_column( $columns, $column_key, $column_label ) {
        $new_columns = array();

        if ( isset( $columns['cb'] ) ) {
            $new_columns['cb'] = $columns['cb'];
            unset( $columns['cb'] );
        }

        $new_columns[$column_key] = __( $column_label, 'your-text-domain' );

        if ( isset( $columns['name'] ) ) {
            $new_columns['name'] = $columns['name'];
            unset( $columns['name'] );
        }

        return array_merge( $new_columns, $columns );
    }

    /**
     * Add a custom 'Color' attribute column to the WooCommerce attribute table.
     *
     * This method adds a 'Color' column to the WooCommerce attribute admin table by utilizing the 
     * `add_custom_attribute_column` method. It passes the column key as 'color' and the label as 'Color'.
     *
     * @param array $columns The existing columns in the WooCommerce attribute admin table.
     *
     * @return array Modified columns with the 'Color' attribute column added.
     */
    public static function color_attribute_column( $columns ) {
        return self::add_custom_attribute_column( $columns, 'color', 'Color' );
    }

    /**
     * Add a custom 'Image' attribute column to the WooCommerce attribute table.
     *
     * This method adds an 'Image' column to the WooCommerce attribute admin table by utilizing the
     * `add_custom_attribute_column` method. It passes the column key as 'image' and the label as 'Image'.
     *
     * @param array $columns The existing columns in the WooCommerce attribute admin table.
     *
     * @return array Modified columns with the 'Image' attribute column added.
     */
    public static function image_attribute_column( $columns ) {
        return self::add_custom_attribute_column( $columns, 'image', 'Image' );
    }

    /**
     * Add a custom 'Label' attribute column to the WooCommerce attribute table.
     *
     * This method adds an 'label' column to the WooCommerce attribute admin table by utilizing the
     * `add_custom_attribute_column` method. It passes the column key as 'label' and the label as 'label'.
     *
     * @param array $columns The existing columns in the WooCommerce attribute admin table.
     *
     * @return array Modified columns with the 'label' attribute column added.
     */
    public static function label_attribute_column( $columns ) {
        return self::add_custom_attribute_column( $columns, 'label', 'Label' );
    }

    /**
     * Outputs content for custom attribute columns in the WooCommerce admin.
     *
     * Generates content for 'color', 'image', and 'label' columns using term metadata.
     *
     * @param string $content     Existing content for the column.
     * @param string $column_name Column name ('color', 'image', or 'label').
     * @param int    $term_id     Term ID for the attribute.
     *
     * @return string Updated content for the column.
     */

    public static function create_attribute_column_content( $content, $column_name, $term_id ) {

        $color = get_term_meta( $term_id, 'color_type', true ) ?: 'N/A';
        $get_image = get_term_meta( $term_id, 'image_type', true );
        $label_type = get_term_meta( $term_id, 'label_type', true ) ?: 'N/A';

        if ( empty( $get_image ) ) {
            $get_image = UPOW_CORE_ASSETS . 'assets/admin/images/fallback-placeholder.png';
        }

        $content = '';
        if ( $column_name === 'color' ) {
            $content .= '<span class="upow-switch-attribute-column-color" style="background-color: ' . esc_attr( $color ) . ';"></span>';
        } elseif ( $column_name === 'image' ) {
            $content .= '<img class="upow-switch-attribute-column-image" src="' . esc_url( $get_image ) . '">';
        } elseif ( $column_name === 'label' ) {
            $content .= esc_html( $label_type );
        }

        return $content;
    }

    /**
     * Render custom fields for editing attribute terms in WooCommerce.
     *
     * This method adds custom input fields for color, image, and label types
     * when editing attribute terms in the WooCommerce admin. It checks the 
     * attribute type and displays the corresponding fields.
     *
     * @param object $term     The term object being edited.
     * @param string $taxonomy The taxonomy of the term being edited.
     *
     * @return void
     */
    public static function upow_edit_fields( $term, $taxonomy ) {

        // do nothing if this term isn't the Color type
        global $wpdb;

        $attribute_type = $wpdb->get_var(
            $wpdb->prepare(
                "
        SELECT attribute_type
        FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies
        WHERE attribute_name = '%s'
        ",
                substr( $taxonomy, 3 ) // remove "pa_" prefix
            )
        );

        // if it is not a color attribute, just do nothing
        if ( 'color_type' !== $attribute_type && 'image_type' != $attribute_type && 'label_type' != $attribute_type ) {
            return;
        }

        // we can use attribute type as a meta key why not
        $color          = get_term_meta( $term->term_id, 'color_type', true );
        $image_type     = get_term_meta( $term->term_id, 'image_type', true );
        $label_type     = get_term_meta( $term->term_id, 'label_type', true );

        switch ( $attribute_type ) {
        case 'color_type':
            ?>
            <tr class="form-field">
                <th><label for="term-color_type"><?php echo esc_html__( "Color", "ultimate-product-options-for-woocommerce" ); ?></label></th>
                <td><input type="text" id="term-color_type" name="color_type" class="term_color_ecaw" value="<?php echo esc_attr( $color ) ?>" /></td>
            </tr>
            <?php
            break;
        case 'image_type':
            ?>
            <tr class="form-field">
                <th><label for="term-image_type"><?php echo esc_html__( "Image", "ultimate-product-options-for-woocommerce" ); ?></label></th>
                <td>
                    <div class="upow-term-image" id="upow-swatch-term-image">
                        <img src="<?php echo esc_url( $image_type ); ?>" id="upow-term-image-preview" style="<?php echo empty( $image_type ) ? 'display:none;' : ''; ?>"/>
                    </div>
                    <div>
                        <input type="hidden" id="upow-swatch-term-img-input" name="image_type" value="<?php echo esc_attr( $image_type ); ?>"/>
                        <a class="button" id="upow-swatch-term-upload-img-btn">
                            <?php esc_html_e( 'Upload Image', 'ultimate-product-options-for-woocommerce' );?>
                        </a>
                        <a class="button <?php echo empty( $image_type ) ? 'upow-d-none' : ''; ?>" id="upow-swatch-term-img-remove-btn">
                            <?php esc_html_e( 'Remove', 'ultimate-product-options-for-woocommerce' );?>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            break;
        case 'label_type':
            ?>
            <tr class="form-field">
                <th><label for="term-<?php echo esc_attr( $label_type ) ?>"><?php echo esc_html__( "Label Name", "ultimate-product-options-for-woocommerce" ); ?></label></th>
                <td><input type="text" id="term-label_type" name="label_type" class="term_label_ecaw" value="<?php echo esc_attr( $label_type ) ?>" /></td>
            </tr>
            <?php
            break;
        default:
        }
    }

    /**
     * Generates a multi-select dropdown for WooCommerce product attributes.
     *
     * This function allows the selection of multiple attribute values for a product.
     *
     * @param string $attribute_taxonomy The taxonomy of the attribute (e.g., color, size).
     * @param int $i The index of the current attribute.
     * @param object $attribute The attribute object containing details about the attribute.
     *
     * @return void Outputs the HTML for the multi-select dropdown and select buttons.
     */
    public function upow_attr_select( $attribute_taxonomy, $i, $attribute ) {

        // get current values
        $options = $attribute->get_options();
        $options = !empty( $options ) ? $options : array();

        ?>
            <select multiple="multiple" data-placeholder="Select Values" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i); ?>][]">
                <?php
                $colors = get_terms( "pa_$attribute_taxonomy->attribute_name", array( 'hide_empty' => 0 ) );
                if ( $colors ) {
                    foreach ( $colors as $color ) {
                        echo '<option value="' . $color->term_id . '"' . wc_selected( $color->term_id, $options ) . '>' . $color->name . '</option>';
                    }
                }
                ?>
            </select>
            <button class="button plus select_all_attributes"><?php echo esc_html__( "Select all", "ultimate-product-options-for-woocommerce" ); ?></button>
            <button class="button minus select_no_attributes"><?php echo esc_html__( "Select none", "ultimate-product-options-for-woocommerce" ); ?></button>
        <?php
    }

    /**
     * Adds custom attribute types to WooCommerce product attributes.
     *
     * This function registers additional attribute types, such as color, image, and label,
     * making them available for use in product settings.
     *
     * @param array $types Existing attribute types.
     * @return array Modified array of attribute types including custom types.
     */
    public function upow_add_attr_type( $types ) {

        // let's add a color here!
        $types['color_type'] = 'Color'; 
        $types['image_type'] = 'Image'; 
        $types['label_type'] = 'Label'; 

        return $types;
    }

    /**
     * Saves custom attribute metadata for a term.
     *
     * This function updates the term metadata for color, image, and label types 
     * based on the submitted POST data.
     *
     * @param int $term_id The ID of the term being updated.
     * @return void
     */
    public function upow_save_color( $term_id ) {

        $color_type = !empty( $_POST['color_type'] ) ? $_POST['color_type'] : '';
        update_term_meta( $term_id, 'color_type', sanitize_hex_color( $color_type ) );

        $image_type = !empty( $_POST['image_type'] ) ? $_POST['image_type'] : '';
        update_term_meta( $term_id, 'image_type', esc_url_raw( $image_type ) );

        $label_type = !empty( $_POST['label_type'] ) ? $_POST['label_type'] : '';
        update_term_meta( $term_id, 'label_type', sanitize_text_field( $label_type ) );

    }

    /**
     * Generates HTML for product attribute swatches on the product page.
     *
     * Replaces dropdowns with color, image, or label swatches based on user settings.
     * Retrieves attribute terms and their metadata to create the swatch display.
     *
     * @param string $html Existing HTML content for product attributes.
     * @param array $args Array containing:
     *                    - string $attribute The attribute taxonomy.
     *                    - array $options The attribute options.
     *                    - object $product The current product object.
     *                    - string $selected The currently selected attribute value.
     *
     * @return string Modified HTML with swatches.
     */

    public function upow_swatches_html( $html, $args ) {


        if( is_product() && $this->settings['enable_swatch_product_page'] != '1' ) {
            return $html;
        }

        if(  $this->settings['convert_dropdown_to_label'] != '1' ) {
            return $html;
        }

        global $wpdb;
        $taxonomy = $args['attribute'];
        $options  = $args['options'];
        $product  = $args['product'];
    
        $attribute_type = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT attribute_type
                FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies
                WHERE attribute_name = '%s'
                ",
                substr($taxonomy, 3)
            )
        );
    
        $html = '<div class="upow-variation-default-wrapper" data-separatortext="'.$this->settings['variations_label_separator'] .'">' . $html . '</div>';
    
        $terms = wc_get_product_terms($product->get_id(), $taxonomy, array('fields' => 'all'));

        $shaped_style = !empty( $this->settings['swatches_shape_style'] ) ? $this->settings['swatches_shape_style'] : 'squared';
        $disable_attribute_effect = !empty( $this->settings['swatches_disable_attribute_effect'] ) ? $this->settings['swatches_disable_attribute_effect'] : 'cross';
    
        $swatches_html = '<div class="upow-swatch-wrapper '. $shaped_style .' '. $disable_attribute_effect. '" data-attribute_name="attribute_' . strtolower( $taxonomy ) . '">';
        
        $tooltip_enable = $this->settings['enable_disable_tooltip_design'] == '1' ? '<span class="upow-variation-tooltip">%s</span>' : '';
        
    
        foreach ( $terms as $term ) {
            
            if ( in_array( $term->slug, $options ) ) {

                $hex_color = get_term_meta($term->term_id, 'color_type', true);
                $get_image = get_term_meta($term->term_id, 'image_type', true);
                $label_type = get_term_meta($term->term_id, 'label_type', true);
                $selected = $args['selected'] === $term->slug ? 'selected' : '';

                $enabled_variation = $this->get_enabled_variations( $product->get_id(),$taxonomy,$term );

                if( !empty( $enabled_variation ) && $enabled_variation['enabled_variations'] && empty( $enabled_variation['variation_id'] ) ) {
                    continue;
                }

                switch ( $attribute_type ) {
                    case 'image_type':

                        if (empty( $get_image ) ) {

                            $get_image = CVUPOW_CORE_ASSETS . 'assets/admin/images/fallback-placeholder.png';

                        }

                        $image             = '<img src="' . esc_url($get_image) . '" alt="variation image"/>';
                        $swatch_type_class = 'upow-variations-image';

                        if( $this->settings['enable_swatches_image_tooltip'] == '1' ) : 
                            $tooltip_enable = $this->settings['enable_disable_tooltip_design'] == '1' && $this->settings['enable_swatches_image_tooltip'] == '1' ? '<span class="upow-variation-tooltip upow-tooltip-image"><img src="' . esc_url($get_image) . '" alt="variation image"/><span class="upow-switch-label">%s</span></span>' : '';
                        endif;

                        break;

                    case 'color_type':
                        $hex_color          = empty( $hex_color ) ? '#e5e5e5' : $hex_color;
                        $image              = sprintf('style="background-color:%s;"', esc_attr( $hex_color ) );
                        $swatch_type_class  = 'upow-variations-color';
                        break;

                    case 'label_type':
                        $label_text         = !empty( $label_type ) ? $label_type : $term->name;
                        $image              = esc_html( $label_text );
                        $swatch_type_class  = 'upow-variations-label';
                        break;

                    default:
                   
                        $label_text         = $term->name;
                        $image              = esc_html( $label_text );
                        $swatch_type_class  = 'upow-variations-label';
                        break;
                }

                $tooltip_text = $taxonomy == 'pa_size' ? substr($term->name, 0, 1) : $term->name;

                $swatches_html .= sprintf(
                    '<span data-attr_name='. $taxonomy . ' class="upow-swatch-item %s %s" data-title="%s" data-value="%s" %s data-variation_id="%s">%s
                        '.$tooltip_enable.'
                    </span>',
                    esc_attr($swatch_type_class),
                    esc_attr($selected),
                    esc_attr($term->name),
                    esc_attr($term->slug),
                    ('color_type' == $attribute_type && !empty($hex_color)) ? $image : '',
                    esc_attr($term->term_id),
                    ('label_type' == $attribute_type || 'image_type' == $attribute_type) ? $image : '',
                    esc_html($tooltip_text)
                );
            }
        }
    
        // Additional code for Logo taxonomy
        if ( $taxonomy == 'Logo' ) {
            foreach ( $options as $value ) {

                
                $selected = ($value == $args['selected']) ? 'selected' : '';
                $swatch_type_class = 'switch-logo';
                $swatches_html .= sprintf(
                    '<span data-attr_name='. $taxonomy . ' class="upow-swatch-item upow-variations-label %s %s" data-title="%s" data-value="%s">%s
                        '.$tooltip_enable.'
                    </span>',
                    esc_attr($swatch_type_class),
                    esc_attr($selected),
                    esc_attr($taxonomy),
                    esc_attr($value),
                    esc_attr($value),
                    
                    esc_attr($value)
                );
            }
        }

        $swatches_html .= '</div>';
    
        return $html . $swatches_html;
    }
    
    /**
     * Retrieves enabled variations for a variable product based on a given taxonomy and term.
     *
     * Checks if the product is variable and returns the status of enabled variations 
     * along with the variation ID that matches the specified term.
     *
     * @param int $product_id The ID of the product.
     * @param string $taxonomy The attribute taxonomy (e.g., 'color', 'size').
     * @param mixed $term The term object or string to match against the variation attributes.
     *
     * @return array An associative array containing:
     *               - bool 'enabled_variations': Indicates if variations are enabled.
     *               - int 'variation_id': The ID of the matching variation, or an empty string if none found.
     */
    public function get_enabled_variations( $product_id, $texonomy,$term ) {
        $product = wc_get_product( $product_id );

        if ( !$product->is_type('variable') ) {
            return [];
        }

        $enabled_variations = true;
        $variation_id = '';

        $available_variations = $product->get_available_variations();

        foreach ( $product->get_available_variations() as $variation ) {
        
            if( isset( $variation['attributes']['attribute_' . strtolower( $texonomy )])) {
                $attr_name = $variation['attributes']['attribute_' . strtolower( $texonomy ) ];
                if( ( is_object( $term ) && $term->slug == $attr_name )  || ( is_string( $term ) && $term == $attr_name ) || empty( $attr_name ) ) {
                    $variation_id = $variation['variation_id'];
                }
                
            } else {
                $enabled_variations = false;
            }
        }

        return array( 
            'enabled_variations' => $enabled_variations,
            'variation_id'       => $variation_id
        );
    }

    /**
     * Adds a custom class to the variations table in WooCommerce.
     *
     * This function starts output buffering to capture and modify the variations table output.
     */
    public function upow_add_custom_class_to_variations_table() {
        ob_start(); // Start output buffering
    }


    /**
     * Modifies the class of the variations table in WooCommerce.
     *
     * This function captures the output from the variations table,
     * replaces the default class with a custom class, and then outputs the modified HTML.
     */
    public function upow_modify_variations_table_class() {

        $html = ob_get_clean(); 

        $html = str_replace( 'class="variations"', 'class="variations upow-variation-form"', $html );

        printf("%s", do_shortcode( $html ) );

    }


    /**
     * Displays swatch variations for a given product on the shop or archive pages.
     *
     * This function generates a custom HTML structure for variation attributes,
     * including dropdowns for selecting variations and a reset button if enabled.
     *
     * @param int $product_id The ID of the product to display variations for.
     * @return string|null The generated HTML for variations or null if not applicable.
     */
    public function upow_display_swatch_variations( $product_id ) {

        global $product;

        // Ensure $product is defined and is of the correct type.
        if (!$product || !is_a($product, 'WC_Product')) {
            $product = wc_get_product($product_id);
        }

        if( $this->settings['shop_variation_onoff'] != 1 ) {
            return;
        }

        if ( is_shop() || is_archive() ) {
            if ( $product && $product->is_type('variable') ) {

                $attributes             = $product->get_variation_attributes();
                $available_variations   = $product->get_available_variations();
                $switch_html            = '';
                $switch_html .= '<div class="variations_form cart" data-product_id="' . absint($product->get_id()) . '"
                data-product_variations="' . htmlspecialchars(wp_json_encode($available_variations)) . '">';
                $switch_html .= '<table class="variations upow-variation-form" cellspacing="0" role="presentation">';
                $switch_html .= '<tbody>';

                foreach ( $attributes as $attribute_name => $options ) {
                    $switch_html .= '<tr>';

                    if( $this->settings['enable_swith_label'] == '1' ) {

                        $switch_html .= '<td class="label">';
                        $switch_html .= '<label for="'. esc_attr( sanitize_title( $attribute_name ) ). '">'. esc_html( wc_attribute_label( $attribute_name ) ) .'</label>';
                        $switch_html .= '</td>';

                    }

                    $switch_html .= '<td class="value">';

                    $selected = isset( $_REQUEST['attribute_' . $attribute_name] )
                        ? wc_clean(urldecode(wp_unslash( $_REQUEST['attribute_' . $attribute_name] ) ))
                        : $product->get_variation_default_attribute( $attribute_name );

                    ob_start();

                    wc_dropdown_variation_attribute_options( array(
                        'options'   => $options,
                        'attribute' => $attribute_name,
                        'product'   => $product,
                        'selected'  => $selected,
                    ) );

                    $switch_html .= ob_get_clean();
                    $attribute_name = $attribute_name ?? '';
                    $attribute_keys = array_keys( $attributes );
                    if( $this->settings['enable_clear_btn'] == '1') {
                        if ( end( $attribute_keys ) === $attribute_name ) {
                            $switch_html .= '<a class="reset_variations" href="#">' . esc_html__('Clear', 'ultimate-product-options-for-woocommerce') . '</a>';
                        }
                    }

                    $switch_html .= '</td>';
                    $switch_html .= '</tr>';
                }

                $switch_html .= '</tbody>';
                $switch_html .= '</table>';
                $switch_html .= '</div>';

                // Add a script to handle the variation changes.

                return $switch_html;
            }
        }
    }

    /**
     * Sets the position of swatches on the shop page based on settings.
     *
     * This function adds appropriate hooks and filters to display product swatches
     * in different positions on the shop page, taking into account the active theme.
     */
    public function swatches_position_shop_page() {

        if( $this->settings['shop_variation_onoff'] != 1 ) {
            return;
        }

        // Retrieve the current theme name
        $theme = wp_get_theme(); 
        $current_theme = $theme->get('Name');
    
        // Mapping Astra theme-specific actions
        $is_astra_theme = ($current_theme == 'astra');
    
        switch ( $this->settings['swatches_position'] ) {
    
            case 'after_title':
                if ( $is_astra_theme ) {
                    add_action('astra_woo_shop_title_before', [ $this, 'upow_swatch_shop_loop_item_title'] );
                } else {
                    add_action('woocommerce_after_shop_loop_item_title', [ $this, 'upow_swatch_shop_loop_item_title'], 8 );
                }
                break;
    
            case 'before_title':
                if ( $is_astra_theme ) {
                    add_action('astra_woo_shop_title_after', [ $this, 'upow_swatch_shop_loop_item_title']);
                } else {
                    add_action('woocommerce_before_shop_loop_item_title', [ $this, 'upow_swatch_shop_loop_item_title'], 7 );
                }
                break;
    
            case 'before_cart':
                add_filter('upow_top_before_cart', [ $this, 'upow_swatch_shop_loop_add_to_cart'], 51, 2 );
                break;
    
            case 'after_cart':
                add_filter('upow_bottom_after_cart', [ $this, 'upow_swatch_shop_loop_add_to_cart'], 10, 2);
                break;
            case 'before_price':
            case 'after_price':
                add_filter( 'woocommerce_get_price_html', [ $this, 'upow_switch_show_loop_item_price'], 100, 2 );
                break;
    
        }
    }

    /**
     * Displays swatch variations alongside the product price in the shop loop.
     *
     * This function modifies the displayed price by adding swatch variations
     * before or after the price based on user settings for variable products.
     *
     * @param string $price The current price HTML.
     * @param WC_Product $product The product object.
     * @return string The modified price HTML with swatches.
     */
    public function upow_switch_show_loop_item_price( $price, $product ) {
        // Get the product ID
        $product_id = $product->get_id();
    
        // Check the position and get the swatch variations accordingly
        $before_price = $after_price = '';
        if ( $product->is_type('variable') && ( is_shop() || is_archive() || is_product() ) ) {

            if( $this->settings['swatches_position'] == 'before_price' ) {

                $before_price = $this->upow_display_swatch_variations( $product_id );

            } elseif( $this->settings['swatches_position'] == 'after_price' ) {
                $after_price = $this->upow_display_swatch_variations( $product_id );
            }
    
            $price = $before_price . $price . $after_price;
            return $price; 

        }
    
        return $price;
    }
    

    /**
     * Displays swatch variations in the shop loop item title area.
     *
     * This function outputs swatch variations for variable products
     * either before or after the product title based on user settings.
     *
     * @global WC_Product $product The current product object.
     * @return void
     */
    public function upow_swatch_shop_loop_item_title () {

        global $product;
        
        if( !$product->is_type('variable') ) {
            return;
        }
        
        if( in_array( $this->settings['swatches_position'], [ 'after_title', 'before_title' ] ) ) { 
            
            ob_start();
            $content = '';
            $content .= $this->upow_display_swatch_variations($product->get_id());
            $content .= ob_get_clean();

            echo do_shortcode( $content );
        }
    }


    /**
     * Adds swatch variations to the WooCommerce shop loop add to cart button.
     *
     * @param string $content The existing content of the add to cart button.
     * @param WC_Product $product The product object being processed.
     * @return string The modified content with swatch variations added, if applicable.
     */
    public function upow_swatch_shop_loop_add_to_cart( $content, $product ) {

        // Check if the product is a variable product
        if ( !$product->is_type('variable') ) {
            return $content;
        }
    
        // Check the swatch position and display accordingly
        if( in_array( $this->settings['swatches_position'], [ 'before_cart', 'after_cart' ] ) ) { 
    
            // Start output buffering
            ob_start();
            echo $this->upow_display_swatch_variations($product->get_id());
            $content_html = ob_get_clean();
            return $content . $content_html;
    
        }
    
        return $content;
    }

    /**
     * Increases the WooCommerce variation threshold for AJAX requests.
     *
     * This function allows you to set a custom threshold for the maximum number of variations 
     * that can be loaded via AJAX for a product.
     *
     * @param int $threshold The current variation threshold.
     * @param WC_Product $product The product object being processed.
     * @return int The modified variation threshold.
     */
    public function upow_increase_wc_variation_threshold( $threshold, $product ) {
        // Increase threshold to 50 variations (or any other value you need)
        return $this->settings['ajax_variations_thresholds'];
    }

    /**
     * Add custom CSS to the front-end
     */
    public function swatch_custom_css()
    {

        $styles = [];
        $swatchItemStyles = [];
        $tooltipStyles = [];
        $tooltipFontsize = [];
        $tooltipcolor = [];
        $tooltipborder = [];
        $tooltipbordercolor = [];

        $border_color = $this->settings['tooltip_background_color'] ? $this->settings['tooltip_background_color'] : 'black';

        if( $this->settings['swatches_tooltip_pos'] == 'top') {
            $styles = array_merge_recursive( $styles, [
                ".upow-swatch-item .upow-variation-tooltip:after" => [
                    'bottom' => '0',
                    'border-color' => $border_color .' transparent transparent  transparent',
                ], 
            ]);
        }

        if( $this->settings['swatches_tooltip_pos'] == 'bottom') {
            $styles = array_merge_recursive( $styles, [
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip" => [
                    'top' => '120%',
                    'bottom' => 'unset',
                    'transform' => 'rotate(180deg) translateX(50%)',
                ],
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip.upow-tooltip-image:after" => [
                    'margin-left'=> '-7px',
                    'border-width' => '7px',
                    'top' => '100%'
                ],
                ".upow-switch-label" => [
                    'transform' => 'rotate(180deg)',
                ],
                
                ".upow-swatch-item .upow-variation-tooltip" => [
                    'transform' => 'rotate(0deg) translateX(-50%)',
                    'bottom' => 'unset',
                    'top' => '120%',

                ],
                ".upow-swatch-item .upow-variation-tooltip:after" => [
                    
                    'bottom' => '100%',
                    'top' => '-10px',
                    'border-color' => 'transparent transparent '.$border_color.' transparent',
                ], 
            ]);
        }

        if( $this->settings['swatches_tooltip_pos'] == 'left' ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-tooltip" => [
                    'left' => 'unset',
                    'bottom' => 'unset',
                    'right' => '110%',
                    'transform' => 'rotate(270deg) translateX(0%)',
                ],
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip" => [
                    'left' => 'unset',
                    'right' => '150%',
                    'bottom' => '50%',
                    'transform' =>  'rotate(270deg) translateX(calc(-50% - 15px)) translateY(-5px)',
                ],
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip:after" => [
                    'margin-left'=> '-5px',
                    'border-width'=> '7px',
                    'left' => 'unset',
                    'top' =>  'unset',
                    'bottom' =>  'unset',
                    'transform' => 'unset'
                ],
                ".upow-switch-label" => [
                    'transform' => 'rotate(180deg)',
                ],
                
                ".upow-swatch-item .upow-variation-tooltip" => [
                    'transform' => 'rotate(0deg) translateX(0%)',
                ],
                ".upow-swatch-item .upow-variation-tooltip:after" => [
                    'left' => '100%',
                    'top' => '50%',
                    'margin-left'=> '0',
                    'bottom' => 'unset',
                    'transform'=> 'translateY(-50%)',
                    'border-color' => 'transparent transparent transparent '.$border_color,
                ], 
            ] );
        }

        if( $this->settings['swatches_tooltip_pos'] == 'right' ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-swatch-item:not(.upow-variations-image) .upow-variation-tooltip" => [
                    'left' => '120%',
                    'bottom' => 'unset',
                    'top' => '50%',
                    'transform' => 'rotate(0deg) translateY(-50%)',
                ],
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip" => [
                    'right' => 'unset',
                    'left' => '160%',
                    'bottom' => '0',
                    'transform' => 'rotate(90deg) translateX(50%)',
                ],
                ".upow-swatch-item.upow-variations-image .upow-variation-tooltip:after" => [
                    'margin-left'=> '-5px',
                    'border-width'=> '7px',
                    'left' => 'unset',
                    'top' => 'unset',
                    'transform' => 'unset',

                ],
                ".upow-switch-label" => [
                    'transform' => 'rotate(180deg)',
                ],
                
                ".upow-swatch-item .upow-variation-tooltip:after" => [
                    'left' => '0',
                    'top' => '50%',
                    'bottom' => 'unset',
                    'margin-left'=> '-10px',
                    'transform'=> 'translateY(-50%)',
                    'border-color' => 'transparent '.$border_color.' transparent transparent;',
                ], 
            ] );
        }

        if( !empty($this->settings['swatches_item_height']) ) {
            $swatchItemStyles['height'] = $this->settings['swatches_item_height'] . 'px';
        }
        
        if( !empty($this->settings['swatches_item_width']) ) {
            $swatchItemStyles['width'] = $this->settings['swatches_item_width'] . 'px';
        }

        if ( !empty( $swatchItemStyles ) ) {
            $styles = array_merge_recursive( $styles, [
                "table.variations .upow-swatch-item,
                .upow-swatch-wrapper.squared .upow-swatch-item.upow-variations-image > img" => array_merge([
                    'display' => 'flex',
                    'align-items' => 'center',
                    'justify-content' => 'center',
                ], $swatchItemStyles ),
            ] );
        }

        if( !empty( $this->settings['tooltip_box_width'] ) ) {
            $tooltipStyles['width'] = $this->settings['tooltip_box_width'] . 'px';
        }

        if( !empty( $this->settings['tooltip_box_height'] ) ) {
            $tooltipStyles['height'] = $this->settings['tooltip_box_height'] . 'px';
        }

        
        if ( !empty( $tooltipStyles ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-tooltip" => array_merge([
                    'display' => 'flex',
                    'align-items' => 'center',
                    'justify-content' => 'center',
                ], $tooltipStyles),
            ]);
        }

        if( !empty( $this->settings['font_size ']) ) {
            $tooltipFontsize['font-size'] = $this->settings['font_size ']. 'px';
        }

        if ( !empty( $tooltipFontsize ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-form .upow-variation-tooltip,
                .upow-variation-tooltip .upow-switch-label" => array_merge([
                   
                ], $tooltipFontsize),
            ]);
        }

        if( !empty( $this->settings['tooltip_background_color'] ) ) {
            $tooltipbg['background-color'] = $this->settings['tooltip_background_color'];
        }

        if( !empty( $this->settings['tooltip_background_color'] ) ) {
            $tooltipborder['border'] = '2px solid ' . $this->settings['tooltip_background_color'];
        }

        if( !empty( $this->settings['tooltip_background_color'] ) ) {
            $tooltipbordercolor['border-color'] = $this->settings['tooltip_background_color'] .' transparent transparent transparent';
        }

        if( !empty( $this->settings['tooltip_font_color'] ) ) {
            $tooltipcolor['color'] = $this->settings['tooltip_font_color'];
        }

        if ( !empty( $tooltipbg ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-form .upow-variation-tooltip,
                .upow-variation-tooltip.upow-tooltip-image .upow-switch-label" => array_merge([
                ], $tooltipbg),
            ]);
        }

        if ( !empty( $tooltipborder ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-tooltip.upow-tooltip-image" => array_merge([
                ], $tooltipborder),
            ]);
        }

        if ( !empty( $tooltipbordercolor ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-tooltip.upow-tooltip-image:after" => array_merge([
                ], $tooltipbordercolor),
            ]);
        }

        if ( !empty( $tooltipcolor ) ) {
            $styles = array_merge_recursive( $styles, [
                ".upow-variation-form .upow-variation-tooltip" => array_merge([
                ], $tooltipcolor),
            ]);
        }

        $custom_style = $this->generate_css( $styles );

        if (!empty($custom_style)) {
            wp_register_style('upow_swatch_variation_options', false);
            wp_enqueue_style('upow_swatch_variation_options');
            wp_add_inline_style('upow_swatch_variation_options', $custom_style);
        }

    }

    /**
     * Generate custom CSS from styles array
     *
     * @param array $styles Array of CSS rules and values.
     * @return string Generated CSS.
     */
    public function generate_css(array $styles)
    {
        $css = '';

        if( is_array($styles ) || is_object( $styles ) ) {
            foreach ( $styles as $selector => $properties ) {
                $css .= $selector . ' {';

                foreach ( $properties as $property => $value ) {
                    if ( $value !== '' ) {
                        $css .= "{$property}: {$value}!important;";
                    }
                }

                $css .= '} ';
            }

            return $css;
        }
    }

}