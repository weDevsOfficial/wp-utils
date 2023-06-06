<?php

namespace WeDevs\WpUtils;

trait HookTrait {

    /**
     * Add action hook.
     *
     * @param string $hook
     * @param string $callback
     * @param int    $priority
     * @param int    $args
     *
     * @return true
     */
    public function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
        return add_action( $hook, [$this, $callback], $priority, $args );
    }

    /**
     * Add filter hook.
     *
     * @param string $hook
     * @param string $callback
     * @param int    $priority
     * @param int    $args
     *
     * @return true
     */
    public function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
        return add_filter( $hook, [$this, $callback], $priority, $args );
    }

    /**
     * Remove action hook.
     *
     * @param string $hook
     * @param string $callback
     * @param int    $priority
     *
     * @return bool
     */
    public function remove_action( $hook, $callback, $priority = 10 ) {
        return remove_action( $hook, [$this, $callback], $priority );
    }

    /**
     * Remove filter hook.
     *
     * @param string $hook
     * @param string $callback
     * @param int    $priority
     *
     * @return bool
     */
    public function remove_filter( $hook, $callback, $priority = 10 ) {
        return remove_filter( $hook, [$this, $callback], $priority );
    }
}
