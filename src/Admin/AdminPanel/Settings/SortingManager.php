<?php
namespace Ultimate\Upow\Admin\AdminPanel\Settings;

use Ultimate\Upow\Traitval\Traitval;

/**
 * Class SortingManager
 * 
 * Manages sorting-related settings for the Ultimate Product Options For WooCommerce plugin.
 */
class SortingManager
{
    use Traitval;

    private $options = [];
    /**
     * Retrieve sorting options with default values.
     */
    private function get_upow_sorting_manager_fields_options()
    {
        $default_settings = [
            'default' => [
                'menu_order'  => '',
                'popularity'  => '',
                'rating'      => '',
                'date'        => '',
                'price'       => '',
                'price-desc'  => '',
            ],
            'custom' => [
                'alphabetical'   => '',
                'reverse_alpha'  => '',
                'by_stock'       => '',
                'review_count'   => '',
                'on_sale_first'  => '',
            ],
        ];

        foreach ($default_settings as $group => $fields) {
           
            foreach ($fields as $key => $default_value) {
                $option_name = "upow_wc_advanced_sorting_settings[$group][$key]";
                $this->options[$group][$key] = $this->get_option_checked($option_name, $default_value);
            }
        }

    }

    /**
     * Render input field for sorting options.
     */
    private function render_checkbox_field( $name, $label, $checked )
    {
        $id = sanitize_title($name);
        ?>
        <div class="upow-extra-options-each-item upow-sorting-manager">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($label); ?>
            </label>
            <input 
                type="checkbox" 
                name="<?php echo esc_attr($name); ?>" 
                value="1" 
                id="<?php echo esc_attr($id); ?>" 
                <?php echo $checked ? 'checked' : ''; ?>
            >
        </div>
        <?php
    }

    /**
     * Render sorting manager fields in the backend.
     */
    public function upow_sorting_manager_fields_backend()
    {
        $this->get_upow_sorting_manager_fields_options();

        $default_options = [
            'menu_order'  => __('Default sorting', 'ultimate-product-options-for-woocommerce'),
            'popularity'  => __('Sort by popularity', 'ultimate-product-options-for-woocommerce'),
            'rating'      => __('Sort by average rating', 'ultimate-product-options-for-woocommerce'),
            'date'        => __('Sort by latest', 'ultimate-product-options-for-woocommerce'),
            'price'       => __('Sort by price: low to high', 'ultimate-product-options-for-woocommerce'),
            'price-desc'  => __('Sort by price: high to low', 'ultimate-product-options-for-woocommerce'),
        ];

        $custom_options = [
            'alphabetical'   => __('Sort by name: A to Z', 'ultimate-product-options-for-woocommerce'),
            'reverse_alpha'  => __('Sort by name: Z to A', 'ultimate-product-options-for-woocommerce'),
            'by_stock'       => __('Sort by availability', 'ultimate-product-options-for-woocommerce'),
            'review_count'   => __('Sort by review count', 'ultimate-product-options-for-woocommerce'),
            'on_sale_first'  => __('Show sale items first', 'ultimate-product-options-for-woocommerce'),
        ];

        $upow_sorting_on_off = get_option( 'upow_sorting_on_off', true );

        if( $upow_sorting_on_off == 1 ) {
            $sorting_on_off = "checked='checked'";
        } else {
            $sorting_on_off = '';
        }
        ?>
        <div class="upow-slide-opt-section" data-option="product-sorting-manager-fields">
            <div class="upow-shado">
                <div class="upow-ati-all">
                    <div class="upow-ati-a"></div>
                    <div class="upow-ati-b"><?php echo esc_html__('Extra Product Sorting', 'ultimate-product-options-for-woocommerce'); ?></div>
                </div>
            </div>
            <div class="upow-general-section upow-slide-item">
                <form method="post" class="upow-sorting-manager-options-fields">
                    <div class="upow-settings-main-wrapper">
                        
                        <div class="upow-settings-left-panel">
                            <div><?php $this->render_checkbox_item('Adnanced Sorting Enable?', 'upow_sorting_on_off', $sorting_on_off ); ?></div>
                            <h3><?php echo esc_html__('Remove Default Sorting', 'ultimate-product-options-for-woocommerce'); ?></h3>
                            <p><?php echo esc_html__('Select default sorting options to remove from your shop.', 'ultimate-product-options-for-woocommerce'); ?></p>
                            <?php
                            echo $this->sorting_on_off;
                            foreach ($default_options as $key => $label) {
                                $this->render_checkbox_field(
                                    "upow_wc_advanced_sorting_settings[default][$key]",
                                    $label,
                                    $this->options['default'][$key]
                                );
                            }
                            ?>
                        </div>

                        <div class="upow-settings-right-panel">
                            <h3><?php echo esc_html__('Add New Sorting', 'ultimate-product-options-for-woocommerce'); ?></h3>
                            <p><?php echo esc_html__('Select sorting options to add to your shop.', 'ultimate-product-options-for-woocommerce'); ?></p>
                            <?php
                            foreach ($custom_options as $key => $label) {
                                $this->render_checkbox_field(
                                    "upow_wc_advanced_sorting_settings[custom][$key]",
                                    $label,
                                    $this->options['custom'][$key]
                                );
                            }
                            ?>
                        </div>
                    </div>
                    <div class="upow-general-item-save upow-settings-button-top">
                        <input 
                            type="submit" 
                            class="upow_checkbox_item_save" 
                            value="<?php echo esc_attr__('Save Changes', 'ultimate-product-options-for-woocommerce'); ?>"
                        >
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}