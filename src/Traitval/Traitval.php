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
}
