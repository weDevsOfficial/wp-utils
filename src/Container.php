<?php

namespace WeDevs\WpUtils;

trait Container {

    /**
     * Container for dynamic properties.
     *
     * @var array<string, mixed>
     */
    protected $container = [];

    /**
     * Get dynamic property from container.
     *
     * @param string $name Property name.
     *
     * @return mixed
     */
    public function __get( $name ) {
        if ( array_key_exists( $name, $this->container ) ) {
            return $this->container[ $name ];
        }

        return null;
    }

    /**
     * Set dynamic property to container.
     *
     * @param string $name  Property name.
     * @param mixed  $value Property value.
     *
     * @return void
     */
    public function __set( $name, $value ) {
        $this->container[ $name ] = $value;
    }

    /**
     * Check if a dynamic property exists in the container.
     *
     * @param string $name Property name.
     *
     * @return bool
     */
    public function __isset( $name ) {
        return array_key_exists( $name, $this->container );
    }

    /**
     * Remove a dynamic property from the container.
     *
     * @param string $name Property name.
     *
     * @return void
     */
    public function __unset( $name ) {
        unset( $this->container[ $name ] );
    }
}
