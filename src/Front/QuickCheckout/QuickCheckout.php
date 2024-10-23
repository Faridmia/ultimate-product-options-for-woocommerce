<?php
namespace Ultimate\Upow\Front\QuickCheckout;
use Ultimate\Upow\Traitval\Traitval;
/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class QuickCheckout
{
    use Traitval;

    public $quick_checkout_enable = '';
    public $checkout_shop_enable = '';

    public function __construct() {

       
        $this->quick_checkout_enable = get_option( 'upow_oneclick_checkout_on_off', true );
        $this->checkout_shop_enable = get_option('upow_oneclick_checkout_shop_enable',true);

        if( $this->quick_checkout_enable != 1 ) {
            return;
        }

        add_action( 'template_redirect', array(  $this, 'upow_redirect_to_checkout_if_cart' ) );
        add_filter ( 'add_to_cart_redirect', array(  $this, 'upow_redirect_to_checkout' ) );

        update_option( 'woocommerce_cart_redirect_after_add', 'no' );
        update_option( 'woocommerce_enable_ajax_add_to_cart', 'no' );

    }

    
    function upow_redirect_to_checkout() {
        
        global $woocommerce;
    
        wc_clear_notices();

        if( $this->checkout_shop_enable == '1' && is_shop() && is_product() ) {
            return $woocommerce->cart->get_checkout_url();
        }
        elseif( !is_shop() && is_product() ) {
            return $woocommerce->cart->get_checkout_url();
        }

        return wc_get_cart_url();
        
    }

    function upow_redirect_to_checkout_if_cart() {
        if ( !is_cart() ) return;

        if ( WC()->cart->is_empty() ) {
            wp_redirect( wc_get_page_permalink( 'shop' ), 302 );
        } else {
            wp_redirect( wc_get_checkout_url(), 302 );
        }

        exit;
    } 

}