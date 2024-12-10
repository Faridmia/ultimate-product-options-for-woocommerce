<?php
namespace Ultimate\Upow\Admin\AdminPanel;
use Ultimate\Upow\Admin\AdminPanel\Settings\ExtraFieldsSettings;
use Ultimate\Upow\Admin\AdminPanel\Settings\FlashSaleSettings;
use Ultimate\Upow\Admin\AdminPanel\Settings\GeneralSettings;
use Ultimate\Upow\Admin\AdminPanel\Settings\Backorder;
use Ultimate\Upow\Admin\AdminPanel\Settings\Preorder;
use Ultimate\Upow\Admin\AdminPanel\Settings\SwatchVariations;
use Ultimate\Upow\Admin\AdminPanel\Settings\QuickCheckout;
use Ultimate\Upow\Admin\AdminPanel\Settings\SortingManager;
use Ultimate\Upow\Admin\Ajax\CommonAjax;
use Ultimate\Upow\Admin\Ajax\FlashSaleAjax;
use Ultimate\Upow\Admin\Ajax\GeneralTabAjax;
use Ultimate\Upow\Admin\Ajax\ProductOptionsAjax;
use Ultimate\Upow\Admin\Ajax\BackorderAjax;
use Ultimate\Upow\Admin\Ajax\SwatchVariationAjax;
use Ultimate\Upow\Admin\Ajax\PreorderAjax;
use Ultimate\Upow\Admin\Ajax\QuickCheckoutAjax;
use Ultimate\Upow\Admin\Ajax\SortingManagerAjax;

/**
 * Class Admin
 *
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for initializing the admin menu and other admin-related features
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class AdminPanel {

    protected $generalSettings;
    protected $flashSaleSettings;
    protected $extraFieldsSettings;
    protected $flashSaleAjax;
    protected $productOptionsAjax;
    protected $GeneralTabAjax;
    protected $CommonAjax;
    protected $BackorderAjax;
    protected $SwatchVariationAjax;
    protected $PreorderAjax;
    protected $QuickCheckoutAjax;
    protected $SortingManagerAjax;
    /**
     * Initialize the class
     *
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary classes and features for the admin area.
     */

    public function __construct() {
        $this->generalSettings = new GeneralSettings();
        $this->flashSaleAjax = new FlashSaleAjax();
        $this->productOptionsAjax = new ProductOptionsAjax();
        $this->GeneralTabAjax = new GeneralTabAjax();
        $this->CommonAjax = new CommonAjax();
        $this->BackorderAjax = new BackorderAjax();
        $this->SwatchVariationAjax = new SwatchVariationAjax();
        $this->PreorderAjax = new PreorderAjax();
        $this->QuickCheckoutAjax = new QuickCheckoutAjax();
        $this->SortingManagerAjax = new SortingManagerAjax();

        $this->initialize_hooks();
    }
    
    protected function initialize_hooks() {

        add_action( 'upow_after_add_option', array( $this, 'upow_after_add_option_callback' ), 5 );

        // swatch variations ajax actions
        add_action( 'wp_ajax_upow_swatches_variations_save_options', array($this->SwatchVariationAjax, 'upow_swatches_variations_save_options') );
        add_action( 'wp_ajax_nopriv_upow_swatches_variations_save_options', array($this->SwatchVariationAjax, 'upow_swatches_variations_save_options') );

        // flashsale  ajax actions
        add_action( 'wp_ajax_save_flashsale_popup_settings', array($this->flashSaleAjax, 'save_popup_settings') );
        add_action( 'wp_ajax_nopriv_save_flashsale_popup_settings', array($this->flashSaleAjax, 'save_popup_settings') );

        

        // Register AJAX actions for product options
        add_action( 'wp_ajax_upow_get_all_product_options', array($this->CommonAjax, 'get_all_product_options') );
        add_action( 'wp_ajax_nopriv_upow_get_all_product_options', array($this->CommonAjax, 'get_all_product_options') );

        add_action( 'wp_ajax_upow_flashsale_settings_save_options', array( $this->flashSaleAjax, 'upow_flashsale_settings_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_flashsale_settings_save_options', array( $this->flashSaleAjax, 'upow_flashsale_settings_save_options' ) );

        // general saving data via ajax
        add_action( 'wp_ajax_upow_general_settings_save_options', array( $this->GeneralTabAjax, 'upow_general_settings_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_general_settings_save_options', array( $this->GeneralTabAjax, 'upow_general_settings_save_options' ) );

        // extra options fields
        add_action( 'wp_ajax_upow_extra_options_fields_save_options', array( $this->productOptionsAjax, 'upow_extra_options_fields_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_extra_options_fields_save_options', array( $this->productOptionsAjax, 'upow_extra_options_fields_save_options' ) );

        // exclude product via ajax action
        add_action( 'wp_ajax_upow_get_exclude_all_product_options', array( $this->CommonAjax, 'upow_get_exclude_all_product_options' ) );
        add_action( 'wp_ajax_nopriv_upow_get_exclude_all_product_options', array( $this->CommonAjax, 'upow_get_exclude_all_product_options' ) );

        // all categories via ajax action
        add_action( 'wp_ajax_upow_get_all_product_categories', array( $this->CommonAjax, 'upow_get_all_product_categories' ) );
        add_action( 'wp_ajax_nopriv_upow_get_all_product_categories', array( $this->CommonAjax, 'upow_get_all_product_categories' ) );

        // backorder ajax
        add_action( 'wp_ajax_upow_backorder_options_fields_save_options', array( $this->BackorderAjax, 'upow_backorder_options_fields_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_backorder_options_fields_save_options', array( $this->BackorderAjax, 'upow_backorder_options_fields_save_options' ) );

        // preorder ajax
        add_action( 'wp_ajax_upow_preorder_options_fields_save_options', array( $this->PreorderAjax, 'upow_preorder_options_fields_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_preorder_options_fields_save_options', array( $this->PreorderAjax, 'upow_preorder_options_fields_save_options' ) );

        // quick checkout ajax
        add_action( 'wp_ajax_upow_quick_checkout_fields_save_options', array( $this->QuickCheckoutAjax, 'upow_quick_checkout_fields_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_quick_checkout_fields_save_options', array( $this->QuickCheckoutAjax, 'upow_quick_checkout_fields_save_options' ) );

        // sorting ajax

        add_action( 'wp_ajax_upow_sorting_manager_fields_save_options', array( $this->SortingManagerAjax, 'upow_sorting_manager_fields_save_options' ) );
        add_action( 'wp_ajax_nopriv_upow_sorting_manager_fields_save_options', array( $this->SortingManagerAjax, 'upow_sorting_manager_fields_save_options' ) );


    }

    public function upow_after_add_option_callback() {
        
        FlashSaleSettings::getInstance()->display();
        FlashSaleSettings::getInstance()->popup_display();
        ExtraFieldsSettings::getInstance()->upow_extra_fields_backend();
        Backorder::getInstance()->upow_backorder_fields_backend();
        SwatchVariations::getInstance()->upow_swatchvariations_fields_backend();
        Preorder::getInstance()->upow_preorder_fields_backend();
        QuickCheckout::getInstance()->upow_quickcheckout_fields_backend();
        SortingManager::getInstance()->upow_sorting_manager_fields_backend();
    }
}