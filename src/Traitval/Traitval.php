<?php

namespace Ultimate\Upow\Traitval;

/**
 * Traitval
 * 
 * This trait provides a singleton implementation for initializing and managing
 * certain functionalities within the Ultimate Product Options For WooCommerce plugin.
 */
trait Traitval
{
	/**
	 * @var bool|self $singleton The singleton instance of this trait.
	 */
	private static $singleton = false;
	public $currency_right;
    public $currency_left;

	/**
	 * @var string $plugin_pref The prefix used for plugin-related options and settings.
	 */
	public $plugin_pref = 'ultimate-product-options-for-woocommerce';

	/**
	 * Constructor
	 * 
	 * The private constructor prevents direct instantiation. It initializes the trait
	 * by calling the initialize method.
	 */
	private function __construct()
	{
		$this->initialize();
        
	}

	/**
	 * Initialize the trait
	 * 
	 * This protected method can be overridden by classes using this trait to include
	 * additional initialization code.
	 */
	protected function initialize()
	{
		// Initialization code can be added here by the class using this trait.
        
	}

	/**
	 * Get the Singleton Instance
	 * 
	 * This static method ensures that only one instance of the trait is created.
	 * It returns the singleton instance, creating it if it does not exist.
	 * 
	 * @return self The singleton instance of the trait.
	 */
	public static function getInstance()
	{
		if (self::$singleton === false) {
			self::$singleton = new self();
		}
		return self::$singleton;
	}

	public function set_currency_position() {

        $currency_position = get_option('woocommerce_currency_pos');
        $currency_symbol = get_woocommerce_currency_symbol();

        switch ($currency_position) {
            case 'left':
                $this->currency_left = $currency_symbol;
                break;
            case 'right':
                $this->currency_right = $currency_symbol;
                break;
            case 'left_space':
                $this->currency_left = $currency_symbol . '&nbsp;';
                break;
            case 'right_space':
                $this->currency_right = '&nbsp;' . $currency_symbol;
                break;
            default:
                $this->currency_left = '';
                $this->currency_right = '';
        }

		return [
			'currency_left' => $this->currency_left,
			'currency_right' => $this->currency_right,
		];
    }

	public function render_checkbox_item( $label, $name, $checked_value = 0 ) {
        ?>
        <div class="upow-general-item upow-checkbox-item">
            <div class="upow-gen-item-con">
                <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <div class="upow-extra-options-each-tem">
                    <span></span>
                    <input name="<?php echo esc_attr($name); ?>" type="checkbox" <?php echo esc_attr($checked_value); ?> value="1">
                </div>
            </div>
        </div>
        <?php
    }

	// Utility method to check option and return checked value
    public function get_option_checked($option_name) {
        $option_value = get_option($option_name, true);
        return ($option_value == 'yes' || $option_value == '1' || !empty($checked_value)) ? "checked='checked'" : '';
    }

	// Method to render text input items
    public function render_color_input($label, $name, $value) {
        ?>
        <div class="upow-general-item">
            <div class="upow-gen-item-con">
                <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce'); ?></label>
                <input type="text" class="upow-section-bg" name="<?php echo esc_attr($name); ?>"
                    value="<?php echo esc_attr($value); ?>">
            </div>
        </div>
        <?php
    }
	public function render_text_input($label, $name, $value, $placeholder = '') {
        ?>
        <div class="upow-general-item">
            <div class="upow-gen-item-con">
                <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <input type="text" name="<?php echo esc_attr($name); ?>" class="upow_additional_text"
                    value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
            </div>
        </div>
        <?php
    }

    public function render_number_input($label, $name, $value, $placeholder = '') {
        ?>
        <div class="upow-general-item">
            <div class="upow-gen-item-con">
            <label
                for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <input type="number" class="upow-title-text-size" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
            </div>
        </div>
        <?php
    }

    public function render_select_item($name, $label, $options) {
        ?>
        <div class="upow-general-item">
            <div class="upow-gen-item-con">
                <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <select name="<?php echo esc_attr($name); ?>" class="upow-countdown-position">
                    <option value=""><?php echo esc_html__('select', 'ultimate-product-options-for-woocommerce') ?></option>
                    <?php foreach ($options as $value => $option_label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($this->options[$name], $value); ?>>
                            <?php echo esc_html__($option_label, 'ultimate-product-options-for-woocommerce'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    public function render_fontsize_input_func( $label, $name, $value, $placeholder = '' ) {
        ?>
        <div class="upow-general-item upow-font-size-input-field">
            <div class="upow-gen-item-con">
                <label  for="<?php echo esc_attr($name); ?>"><?php echo esc_html__($label, 'ultimate-product-options-for-woocommerce') ?></label>
                <div class="upow-font-size-wrapper">
                    <input placeholder="<?php echo esc_attr($placeholder); ?>" type="text" class="upow-tooltip-font-size" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
                    <span class="upow-font-size-unit"><?php echo esc_html__("Px","ultimate-product-options-for-woocommerce");?></span>
                </div>
            </div>
        </div>
     <?php
    }


    

}
