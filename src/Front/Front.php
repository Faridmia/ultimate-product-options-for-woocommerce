<?php
namespace Ultimate\Upow\Front;

use Ultimate\Upow\Traitval\Traitval;
use Ultimate\Upow\Front\Options\Options;
use Ultimate\Upow\Front\FlashSale\FlashSale;
use Ultimate\Upow\Front\General\General;
use Ultimate\Upow\Front\Backorder\Backorder;
use Ultimate\Upow\Front\Preorder\Preorder;
use Ultimate\Upow\Front\SwatchVariation\SwatchVariation;
use Ultimate\Upow\Front\QuickCheckout\QuickCheckout;
use Ultimate\Upow\Front\SortingManager\SortingManager;

/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class Front
{
    use Traitval;

    /**
     * @var Options $options_instance An instance of the Options class.
     */
    protected $options_instance;

    /**
     * @var FlashSale $flashsale_instance An instance of the FlashSale class.
     */
    protected $flashsale_instance;
    protected $backorder_instance;
    protected $swatches_instance;
    protected $preorder_instance;
    protected $quickcheckout_instance;
    protected $sorting_instance;

    /**
     * Initialize the class
     */
    protected function initialize()
    {
        $this->init_hooks();
        add_action('wp_head', [ $this, 'add_generate_custom_css'] );
        add_action( 'wp_head', [ $this, 'add_custom_css_general_settings'] );
        add_action( 'wp_head', [ $this, 'add_google_analytics'] );
        add_action( 'wp_footer', [ $this, 'add_custom_js'] );
    }

    /**
     * Initialize Hooks
     */
    private function init_hooks()
    {
       $this->options_instance          = Options::getInstance();
       $this->flashsale_instance        = FlashSale::getInstance();
       $this->backorder_instance        = Backorder::getInstance();
       $this->swatches_instance         = SwatchVariation::getInstance();
       $this->preorder_instance         = Preorder::getInstance();
       $this->quickcheckout_instance    = QuickCheckout::getInstance();
       $this->sorting_instance          = SortingManager::getInstance();
        
    }

    /**
     * Calls the method to generate custom CSS.
     *
     * This function invokes the generate_custom_css method from the General class instance.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_generate_custom_css() {
        General::getInstance()->add_generate_custom_css();
    }

    /**
     * Adds custom CSS to the general settings.
     *
     * This function invokes the add_custom_css method from the General class instance.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_custom_css_general_settings() {
        General::getInstance()->add_custom_css();
    }

    /**
     * Adds custom JavaScript to the page.
     *
     * This function invokes the add_custom_js method from the General class instance.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_custom_js() {
        General::getInstance()->add_custom_js();
    }

    /**
     * Adds Google Analytics tracking code.
     *
     * This function invokes the add_google_analytics method from the General class instance.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_google_analytics() {
        General::getInstance()->add_google_analytics();
    }
}