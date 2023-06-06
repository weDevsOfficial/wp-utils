<?php

namespace WeDevs\WpUtils;

trait ContainerTrait {

    /**
     * Container for dynamic properties.
     *
     * @var array
     */
    protected $container = [];

    /**
     * Get dynamic property from container.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get( $name ) {
        if ( isset( $this->container[ $name ] ) ) {
            return $this->container[ $name ];
        }

        return null;
    }

    /**
     * Set dynamic property to container.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set( $name, $value ) {
        $this->container[ $name ] = $value;
    }
}
