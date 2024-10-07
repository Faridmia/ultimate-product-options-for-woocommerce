<?php
namespace Ultimate\Upow\Front\FlashSale;
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
            'enable_flash_sale' => get_option_upow('upow_enable_flash_sale_here', true),
            'flash_sale_settings' => get_option_upow('upow_flash_sale_settings', true),
            'override_sale_price' => get_option_upow('upow_override_saleflash', true),
        ];
        
    }

    public function hide_regular_price( $price_html, $product ) {

        if ( $this->settings['enable_flash_sale'] && $this->settings['enable_flash_sale'] == 1 ) {

            if ( !empty( $this->settings['flash_sale_settings'] ) ) {

                foreach ( $this->settings['flash_sale_settings'] as $product_data ) {

                    foreach ( $product_data['upow_flashsale_product'] as $data ) {
                        if ( $this->is_flash_sale_active( $data ) ) {
                            $proId = $this->get_product_ids( $data, $product );
                            if ( in_array( $product->get_id(), $proId ) ) {
                                return $this->get_discounted_price_html( $product, $data['discount_value'] ?? '0', $data['discount_type'] ?? '0');
                            }
                        }
                    }

                }

            }

        }

        return $price_html;

    }

    private function is_flash_sale_active( $data ) {

        $start_date = strtotime( $data['flashsale_start_date'] );
        $end_date   = strtotime( $data['flashsale_end_date'] );
        $today      = strtotime('today');

        return $start_date <= $today && $end_date >= $today;
    }

    private function get_product_ids( $data , $product ) {

        if ( $data['apply_all_product'] == '1') {

            if(isset( $data['exclude_product'] ) && !empty( $data['exclude_product'] ) ) {
                if ( in_array( $product->get_id(), $data['exclude_product']) ?? [] ) {
                    return [];
                }
            }
            
            return [$product->get_id()];
        }

        return isset( $data['select_product'] ) ? $data['select_product'] : [];

    }

    private function get_discounted_price_html( $product, $discount_value, $discount_type ) {
        if ($product->is_type('variable')) {
            return $this->get_variable_product_price_html( $product, $discount_value, $discount_type );
        } else {
            return $this->get_simple_product_price_html( $product, $discount_value, $discount_type );
        }
    }

    private function get_variable_product_price_html( $product, $discount_value, $discount_type ) {

        $min_price = null;
        $max_price = null;

        foreach ( $product->get_available_variations() as $variation ) {

            $variation_id = $variation['variation_id'];
            $variable_product = new \WC_Product_Variation( $variation_id );

            $price = $this->calculate_discounted_price(
                $this->get_product_price( $variable_product ),
                $discount_value,
                $discount_type
            );

            if ( is_null($min_price) || $price < $min_price ) {
                $min_price = $price;
            }
            if ( is_null($max_price) || $price > $max_price ) {
                $max_price = $price;
            }
        }

        if ( $min_price !== $max_price ) {
            return '<ins>' . wc_price( $min_price ) . ' - ' . wc_price( $max_price ) . '</ins>';
        } else {
            return '<ins>' . wc_price( $min_price ) . '</ins>';
        }
    }

    private function get_simple_product_price_html( $product, $discount_value, $discount_type ) {

        $price = $this->calculate_discounted_price(
            $this->get_product_price( $product ),
            $discount_value,
            $discount_type
        );

        return '<ins>' . wc_price($price) . '</ins>';

    }

    private function get_product_price( $product ) {

        $regular_price  = $product->get_regular_price();
        $sales_price    = $this->settings['override_sale_price'] ? $product->get_sale_price() : 0;
        return $sales_price !== '' && $sales_price != '0' ? $sales_price : $regular_price;

    }

    private function calculate_discounted_price( $price, $discount_value = '', $discount_type ) {
        $price = (float) $price;
    
        // Ensure discount_value is a valid number
        $discount_value = is_numeric($discount_value) ? (float)$discount_value : 0;
    
        switch ( $discount_type ) {
            case 'percent_discount':

                if ($discount_value > 0) {
                    $price = $price - ( $price * ($discount_value / 100 ) );
                }

                break;
            case 'fixed_discount':

                if ( $discount_value > 0 ) {
                    $price = $price - $discount_value;
                }

                break;
            case 'fixed_price':

                if ( $discount_value > 0) {
                    $price = $discount_value;
                }

                break;
        }
    
        return max( $price, 0 );
    }
}

// Usage
add_filter('woocommerce_get_price_html', function( $price_html, $product ) {

    $flash_sale = new PriceOverride();
    return $flash_sale->hide_regular_price( $price_html, $product );

}, 10, 2);