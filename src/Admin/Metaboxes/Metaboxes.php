<?php

namespace Ultimate\Upow\Admin\Metaboxes;

use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class Metaboxes
{
    use Traitval;

    /**
     * Constructor
     * 
     * The constructor adds actions to the WordPress hooks for saving post data
     * and adding meta boxes to the post edit screen. These actions ensure that
     * custom metadata is handled properly within the Ultimate Product Options For WooCommerce plugin.
     */
    public function __construct()
    {
        // Add an action to the 'save_post' hook to handle saving custom meta data


        // Add an action to the 'add_meta_boxes' hook to add custom meta boxes to the post edit screen
        add_action('add_meta_boxes', array($this, 'upow_product_meta_boxes'));
        add_action('save_post', array($this, 'upow_product_meta_save'));
        add_filter('woocommerce_product_data_tabs', array($this, 'upow_add_custom_product_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'upow_show_upow_product_meta_box'));
        add_action('woocommerce_process_product_meta', array($this, 'upow_product_meta_save'));
    }

    /**
     * Add a custom meta box for extra item text details.
     *
     * This function hooks into the 'add_meta_boxes' action to add a custom meta box 
     * for entering extra item text details in the product field screen.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function upow_product_meta_boxes()
    {
        add_meta_box(
            'upow_product_meta_box',
            __('Extra Product Custom Fields', 'ultimate-product-options-for-woocommerce'),
            array($this, 'upow_show_upow_product_meta_box'),
            array('upow_product'),
            'normal',
            'high'
        );
    }

    function upow_add_custom_product_data_tab($tabs)
    {
        $tabs['upow_extra_fields'] = array(
            'label'    => __('Extra Fields', 'ultimate-product-options-for-woocommerce'),
            'target'   => 'upow_product_data',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 21,
        );
        return $tabs;
    }

    /**
     * Display the Extra Product Data meta box.
     *
     * This function is the callback used by `add_meta_box` to render the content of the
     * extra item text meta box on the product field screen. It retrieves the current value
     * of the 'upow_product' meta field for the current post and displays an input field
     * for editing it.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function upow_show_upow_product_meta_box()
    {
        global $post;
        $upow_product = get_post_meta($post->ID, 'upow_product', true);

        $nonce = wp_create_nonce('upow-metaboxes-nonce');

?>
        <div id="upow_product_data" class="panel woocommerce_options_panel">
            <input type="hidden" name="upow-metaboxes-nonce" value="<?php echo esc_attr($nonce); ?>" />
            <div id="upow-extra-options-wrapper">
                <div class="upow-extra-options-wrapper">
                    <?php if (!empty($upow_product)) {
                        foreach ($upow_product as $index => $field_group) { ?>
                            <div class="upow-extra-field-group" data-index="<?php echo esc_attr($index); ?>">
                                <div class="upow-extra-field-group-header"><?php echo esc_html__("Field Group", "ultimate-product-options-for-woocommerce"); ?> <?php echo esc_html($index + 1); ?> </div>

                                <div class="upow-extra-field-group-body" style="display: none;">
                                    <p>
                                        <label for="upow_product[<?php echo esc_attr($index); ?>][field_type]"><?php echo esc_html__("Field Type", "ultimate-product-options-for-woocommerce"); ?></label>
                                        <select name="upow_product[<?php echo esc_attr($index); ?>][field_type]">
                                            <option value="text" <?php selected($field_group['field_type'], 'input'); ?>><?php echo esc_html__("Input", "ultimate-product-options-for-woocommerce"); ?></option>
                                            <option value="radio" <?php selected($field_group['field_type'], 'radio'); ?>><?php echo esc_html__("Radio", "ultimate-product-options-for-woocommerce"); ?></option>
                                            <option value="checkbox" <?php selected($field_group['field_type'], 'checkbox'); ?>><?php echo esc_html__("Checkbox", "ultimate-product-options-for-woocommerce"); ?></option>
                                        </select>
                                    </p>
                                    <p>
                                        <label for="upow_product[<?php echo esc_attr($index); ?>][field_label]"><?php echo esc_html__("Field Label", "ultimate-product-options-for-woocommerce"); ?></label>
                                        <input type="text" name="upow_product[<?php echo esc_attr($index); ?>][field_label]" value="<?php echo esc_attr($field_group['field_label']); ?>">
                                    </p>
                                    <p>
                                        <label for="upow_product[<?php echo esc_attr($index); ?>][required]"><?php echo esc_html__("Required", "ultimate-product-options-for-woocommerce"); ?></label>
                                        <label class="upow-label-switch">
                                            <input type="checkbox" name="upow_product[<?php echo esc_attr($index); ?>][required]" value="1" <?php checked($field_group['required'], '1'); ?>>
                                            <span class="upow-slider upow-round"></span>
                                        </label>
                                    </p>
                                    <p>
                                        <label for="upow_product[<?php echo esc_attr($index); ?>][default_value]"><?php echo esc_html__("Default Value", "ultimate-product-options-for-woocommerce"); ?></label>
                                        <input type="text" name="upow_product[<?php echo esc_attr($index); ?>][default_value]" value="<?php echo esc_attr($field_group['default_value']); ?>">
                                    </p>
                                    <p>
                                        <label for="upow_product[<?php echo esc_attr($index); ?>][placeholder_text]"><?php echo esc_html__("Placeholder Text", "ultimate-product-options-for-woocommerce"); ?></label>
                                        <input type="text" name="upow_product[<?php echo esc_attr($index); ?>][placeholder_text]" value="<?php echo esc_attr($field_group['placeholder_text']); ?>">
                                    </p>
                                    <button type="button" class="remove-upow-extra-field-group"><?php echo esc_html__("Remove", "ultimate-product-options-for-woocommerce"); ?></button>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
                <button type="button" id="add-upow-extra-field-group"><?php echo esc_html__("Add More Fields", "ultimate-product-options-for-woocommerce"); ?></button>
            </div>
    <?php
    }

    /**
     * Save the Extra Product Data meta field.
     *
     * This function saves the value of the 'upow_product' meta field when a post is saved.
     * It verifies a nonce to ensure the request is legitimate, then updates the meta field
     * with the value from the form input.
     *
     * @since 1.0.0
     *
     * @param int $post_id The ID of the post being saved.
     * @return int The post ID if the nonce is invalid.
     */
    public function upow_product_meta_save($post_id)
    {
        // Verify nonce

        if (!isset($_POST['upow-metaboxes-nonce']) || !wp_verify_nonce($_POST['upow-metaboxes-nonce'], 'upow-metaboxes-nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check permissions
        $post_type = get_post_type($post_id);
        if ('upow_product' === $post_type) {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        }

        // Handle multiple instances of upow_product
        if (isset($_POST['upow_product'])) {
            $new_items = $_POST['upow_product'];
            $sanitized_data = sanitize_upow_custom_field_items_data($new_items);
            update_post_meta($post_id, 'upow_product', $sanitized_data);
        } else {
            delete_post_meta($post_id, 'upow_product');
        }
    }
}
