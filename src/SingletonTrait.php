<?php

namespace WeDevs\WpUtils;

trait SingletonTrait {

    /**
     * Singleton instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
