<?php
namespace Ultimate\Upow\Admin;
use Ultimate\Upow\Admin\Menu\Menu;
use Ultimate\Upow\Admin\Metaboxes\Metaboxes;
use Ultimate\Upow\Traitval\Traitval;
use Ultimate\Upow\Admin\AdminPanel\AdminPanel;

/**
 * Class Admin
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for initializing the admin menu and other admin-related features
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Admin
{
    use Traitval;

    /**
     * @var Menu $menu_instance An instance of the Menu class.
     */
    protected $menu_instance;
    protected $metabox_instance;
    protected $admin_panel_instance;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary classes and features for the admin area.
     */
    protected function initialize()
    {

        $this->define_classes();
    }

    /**
     * Define Classes
     * 
     * This method initializes the classes used in the admin area, specifically the
     * Menu class, and assigns an instance of it to the $menu_instance property.
     */
    private function define_classes()
    {
        $this->menu_instance        = new Menu();
        $this->metabox_instance     = new Metaboxes();
        $this->admin_panel_instance = new AdminPanel();
    }
}
