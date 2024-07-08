<?php

namespace Ultimate\Upow\Hookwoo;

use Ultimate\Upow\Traitval\Traitval;
use Ultimate\Upow\Hookwoo\Cart\Cart;
use Ultimate\Upow\Hookwoo\Order\Order;


/**
 * Class Common
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides initialization for post types within the Ultimate Product Options For WooCommerce plugin.
 */
class Hookwoo
{
    use Traitval;
    public $cart_instance;
    public $order_instance;

    /**
     * @var Posttype $posttypes_instance An instance of the Posttype class.
     */
    //public $posttypes_instance;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary hooks for the class.
     */
    protected function initialize()
    {
        $this->include_hooks();
    }

    /**
     * Initialize Hooks
     * 
     * This method initializes hooks and assigns an instance of the Posttype class
     * to the $posttypes_instance property.
     */
    public function include_hooks()
    {
        $this->cart_instance    = new Cart();
        $this->order_instance   = new Order();
    }
}
