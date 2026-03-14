<?php

/**
 * Model helper functions.
 *
 * @package WeDevs\WpUtils
 */

if ( ! function_exists( 'wputils_class_uses_recursive' ) ) {
    /**
     * Get all traits used by a class and its parents, recursively.
     *
     * @param string|object $class Class name or object.
     *
     * @return array<string, string> Trait class names.
     */
    function wputils_class_uses_recursive( $class ) {
        if ( is_object( $class ) ) {
            $class = get_class( $class );
        }

        $results = [];

        foreach ( array_reverse( class_parents( $class ) ) + [ $class => $class ] as $parent ) {
            $results += wputils_trait_uses_recursive( $parent );
        }

        return array_unique( $results );
    }
}

if ( ! function_exists( 'wputils_trait_uses_recursive' ) ) {
    /**
     * Get all traits used by a trait and its nested traits, recursively.
     *
     * @param string $trait Trait class name.
     *
     * @return array<string, string> Trait class names.
     */
    function wputils_trait_uses_recursive( $trait ) {
        $traits = class_uses( $trait ) ?: [];

        foreach ( $traits as $used_trait ) {
            $traits += wputils_trait_uses_recursive( $used_trait );
        }

        return $traits;
    }
}
