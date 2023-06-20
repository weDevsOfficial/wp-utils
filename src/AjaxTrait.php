<?php

namespace WeDevs\WpUtils;

trait AjaxTrait {

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
			'prefix' => '',     // it is always a good idea to prefix actions to make it unique.
			'nopriv' => true,
			'priv'   => true,
		];

		$args = wp_parse_args( $default, $args );

		if ( $args['priv'] ) {
			add_action( 'wp_ajax' . $args['prefix'] . $action, $callback );
		}

		if ( $args['nopriv'] ) {
			add_action( 'wp_ajax_nopriv' . $args['prefix'] . $action, $callback );
		}
	}
}
