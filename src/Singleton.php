<?php

namespace WeDevs\WpUtils;

trait Singleton {

    /**
     * Singleton instances keyed by class name.
     *
     * @var array<class-string, static>
     */
    private static $instances = [];

    /**
     * Get the singleton instance.
     *
     * @return static
     */
    public static function instance() {
        $class = static::class;

        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new static();
        }

        return self::$instances[ $class ];
    }
}
