<?php
/**
 * Kura-ai Booking System Loader
 *
 * Loads dependencies and registers hooks for the plugin.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Loader {

	/**
	 * Collection of actions to register.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $actions;

	/**
	 * Collection of filters to register.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $filters;

	/**
	 * Initialize collections.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Add a new action to the collection.
	 *
	 * @since 1.0.0
	 * @param string $hook Action hook name.
	 * @param object $component Component object.
	 * @param string $callback Callback method name.
	 * @param int    $priority Priority (default: 10).
	 * @param int    $accepted_args Number of accepted arguments (default: 1).
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection.
	 *
	 * @since 1.0.0
	 * @param string $hook Filter hook name.
	 * @param object $component Component object.
	 * @param string $callback Callback method name.
	 * @param int    $priority Priority (default: 10).
	 * @param int    $accepted_args Number of accepted arguments (default: 1).
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add hook to collection.
	 *
	 * @since 1.0.0
	 * @param array  $hooks Collection of hooks.
	 * @param string $hook Hook name.
	 * @param object $component Component object.
	 * @param string $callback Callback method name.
	 * @param int    $priority Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return array Updated collection.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register all actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
