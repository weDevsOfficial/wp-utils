<?php

namespace WeDevs\WpUtils;

trait AjaxTrait {

	/**
	 * A predefined array to use when we need to create AJAX actions only for logged in users
	 *
	 * @var array
	 */
	protected $logged_in_only = [ 'nopriv' => false ];

	/**
	 * A predefined array to use when we need to create AJAX actions only for logged out users
	 *
	 * @var array
	 */
	protected $logged_out_only = [ 'nopriv' => true, 'priv' => false ];

	/**
	 * Register ajax into action hook
	 *
	 * Usage:
	 * register_ajax( 'action', 'action_callback' ); // for logged-in and logged-out users
	 * register_ajax( 'action', 'action_callback', [ 'nopriv' => false ] ); // for logged-in users only
	 * register_ajax( 'action', 'action_callback', [ 'nopriv' => true, 'priv' => false ] ); // for logged-out users only
	 *
	 * @param string $action
	 * @param callable|string $callback
	 * @param array $args
	 *
	 * @return void
	 */
	public function register_ajax( $action, $callback, $args = [] ) {
		$default = [
			'nopriv'        => true,
			'priv'          => true,
			'priority'      => 10,
			'accepted_args' => 1,
		];

		$args = wp_parse_args( $default, $args );

		if ( $args['priv'] ) {
			add_action( 'wp_ajax_' . $action, $callback, $args['priority'], $args['accepted_args'] );
		}

		if ( $args['nopriv'] ) {
			add_action( 'wp_ajax_nopriv_' . $action, $callback, $args['priority'], $args['accepted_args'] );
		}
	}
}
