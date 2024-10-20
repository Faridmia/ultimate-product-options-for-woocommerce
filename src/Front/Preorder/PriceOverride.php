<?php
namespace Ultimate\Upow\Front\Preorder;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class PriceOverride {

    protected $settings = [];
    use Traitval;

    public function __construct() {

        $this->settings = [
            'enable_preorder' => get_option('upow_preorder_on_off',true),
        ];

        if( $this->settings['enable_preorder'] != '1' ) {
            return;
        }
        
        add_action( 'woocommerce_product_variation_get_price', array( $this, 'get_variable_product_price_html' ), 10, 2 );
        add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'get_variable_product_price_html' ), 10, 2 );
        add_filter( 'woocommerce_variation_prices_price', array( $this, 'get_variable_product_price_html' ), 10, 2 );
        add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'get_variable_product_price_html' ), 10, 2 );
        add_filter('woocommerce_get_price_html', array($this, 'modify_price_for_preorder_variation' ), 10, 2);
        add_filter( 'woocommerce_variable_get_price_html', array( $this, 'modify_price_for_preorder_variation' ), 10, 2 );
        
    }

    /**
     * Modifies the price HTML for pre-order products based on their pre-order pricing settings.
     *
     * This function checks if a product is a simple product or a variation and whether pre-order 
     * is enabled. If pre-order pricing is set, it calculates and formats the price according to 
     * the specified management strategy (fixed, decrease, or increase).
     *
     * @param string $price_html The original price HTML to be modified.
     * @param WC_Product $product The product object for which the price is being modified.
     * @return string The modified price HTML reflecting pre-order pricing.
     */
    public function modify_price_for_preorder_variation( $price_html, $product ) {

        $get_id                  = $product->get_id();
        $preorder_price          = get_post_meta( $get_id, '_upow_preorder_amount', true );
        $manage_price            = get_post_meta( $get_id, '_upow_preorder_manage_price', true );
        $discount_type           = get_post_meta( $get_id, '_upow_preorder_amount_type', true );
        $simple_preorder_enable  = get_post_meta( $get_id, '_upow_preorder_sample', true );
        $enable_preorder         = get_post_meta( $get_id, '_upow_preorder_variable_product', true );
    
        // Ensure the product is either simple or variation type
        if ( $product->is_type( 'variation' ) || $product->is_type( 'simple' ) ) {
    
            // Check if preorder is enabled and preorder price is set
            if ( ( $enable_preorder === 'yes' || $simple_preorder_enable == 'yes' ) && ! empty( $preorder_price ) ) {
                $sale_price    = $product->get_sale_price();
                $regular_price = $product->get_regular_price();
    
                // Handle the regular price for non-sale products
                if ( $manage_price == 'fixed_price' ) {
                    $calculated_price =  $preorder_price;
                    $price_html = wc_format_sale_price( $regular_price, $calculated_price );
                } elseif ( $manage_price == 'decrease_price' ) {
                    $calculated_price = $this->calculate_discounted_price( $product, $regular_price, $sale_price, $preorder_price, $discount_type, 'decrease' );
                    $price_html = wc_format_sale_price( $regular_price, $calculated_price );
                } elseif ( $manage_price == 'increase_price' ) {
                    $calculated_price = $this->calculate_discounted_price( $product, $regular_price, $sale_price, $preorder_price, $discount_type, 'increase' );
                    $price_html = wc_format_sale_price( $regular_price, $calculated_price );
                }
                
            }
        }
    
        return $price_html;
    }


    /**
     * Retrieves and modifies the price HTML for variable products that are available for pre-order.
     *
     * This function checks if the product variation has a regular price set and if pre-order 
     * is enabled. It then calculates the appropriate price based on the management strategy 
     * (fixed, decrease, or increase) for pre-orders. The function includes a static flag to 
     * prevent recursive price calculations.
     *
     * @param string $price The original price HTML of the product.
     * @param WC_Product_Variation $product The variable product object for which the price is being retrieved.
     * @return string The modified price HTML reflecting pre-order pricing, or the original price if conditions are not met.
     */
    public function get_variable_product_price_html( $price, $product ) {
        // Check if we are already calculating the price to prevent loops
        static $processing = false;
    
        if ( $processing ) {
            return $price;  // Return original price if already processing to prevent loop
        }
    
        $processing = true;  // Set the flag to indicate we are processing the price
    
        $variation_id    = $product->get_id();
        $regular_price   = get_post_meta( $variation_id, '_regular_price', true );
        $preorder_price  = get_post_meta( $variation_id, '_upow_preorder_amount', true );
        $enable_preorder = get_post_meta( $variation_id, '_upow_preorder_variable_product', true );
        $manage_price    = get_post_meta( $variation_id, '_upow_preorder_manage_price', true );
        $discount_type   = get_post_meta( $variation_id, '_upow_preorder_amount_type', true );
    
        if ( ! $regular_price || $enable_preorder !== 'yes' || empty( $preorder_price ) ) {
            $processing = false;  // Reset the flag before returning the price
            return $price;  // Return original price if conditions are not met
        }
    
        $sale_price = get_post_meta( $variation_id, '_sale_price', true );
    
        // Handle price management logic
        switch ( $manage_price ) {
            case 'fixed_price':
                $price = $preorder_price;
                break;
    
            case 'decrease_price':
                $price = $this->calculate_discounted_price( $product, $regular_price, $sale_price, $preorder_price, $discount_type, 'decrease' );
                break;
    
            case 'increase_price':
                $price = $this->calculate_discounted_price( $product, $regular_price, $sale_price, $preorder_price, $discount_type, 'increase' );
                break;
    
            default:
                $processing = false;  // Reset the flag before returning the price
                return $price;  // Return original price if no condition is met
        }
    
        $processing = false;  // Reset the flag after processing is complete
    
        return $price;
    }

    /**
     * Calculates the adjusted price of a product based on a specified discount type and operation (increase or decrease).
     *
     * @param WC_Product $product The product object.
     * @param float $regular_price The regular price of the product.
     * @param float $sale_price The current sale price of the product, if applicable.
     * @param float $preorder_price The discount or markup amount to apply.
     * @param string $discount_type The type of discount ('fixed_amount' or 'percentage').
     * @param string $operation The operation to perform ('increase' or 'decrease').
     * @return float The final calculated price after applying the discount or markup.
     */
    
    private function calculate_discounted_price( $product, $regular_price, $sale_price, $preorder_price, $discount_type, $operation ) {
        $base_price = $product->is_on_sale() ? $sale_price : $regular_price;
    
        if ( $discount_type === 'fixed_amount' ) {
            $price = ( $operation === 'increase' ) ? $base_price + $preorder_price : max( 0, $base_price - $preorder_price );
        } elseif ( $discount_type === 'percentage' && $preorder_price < 100 ) {
            $price_percentage = ( $preorder_price / 100 ) * $base_price;
            $price = ( $operation === 'increase' ) ? $base_price + $price_percentage : max( 0, $base_price - $price_percentage );
        } else {
            $price = $base_price;
        }
    
        return $price;
    }
    
}
