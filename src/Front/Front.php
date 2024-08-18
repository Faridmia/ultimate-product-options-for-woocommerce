<?php
namespace Ultimate\Upow\Front;
use Ultimate\Upow\Traitval\Traitval;
use Ultimate\Upow\Front\Options\Options;

/**
 * Class Front
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for initializing the admin menu and other Front-related features
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Front
{
    use Traitval;

    /**
     * @var Options $options_instance An instance of the Options class.
     */
    public $options_instance;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary classes and features for the front area.
     */
    protected function initialize()
    {
        $this->init_hooks();
    }

    /**
     * Initialize Hooks
     * 
     * This method initializes the necessary instances and hooks for the class.
     * Specifically, it obtains the singleton instance of the Options class and
     * assigns it to the $options_instance property.
     */
    private function init_hooks()
    {
        $this->options_instance = Options::getInstance();
    }
}
