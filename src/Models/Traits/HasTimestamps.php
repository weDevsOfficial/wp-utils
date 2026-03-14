<?php

namespace WeDevs\WpUtils\Models\Traits;

/**
 * HasTimestamps trait.
 *
 * Auto-manages created_at and updated_at columns.
 *
 * @phpstan-require-extends \WeDevs\WpUtils\Models\Model
 */
trait HasTimestamps {

    /**
     * Boot the HasTimestamps trait.
     *
     * @return void
     */
    protected static function bootHasTimestamps() {
        $entity = static::getEntityName();

        // Set created_at on insert.
        add_action(
            static::hookName( $entity . '_creating' ),
            function ( $model ) {
                $now = current_time( 'mysql' );

                if ( empty( $model->created_at ) ) {
                    $model->setAttribute( 'created_at', $now );
                }

                if ( empty( $model->updated_at ) ) {
                    $model->setAttribute( 'updated_at', $now );
                }
            },
        );

        // Set updated_at on update.
        add_action(
            static::hookName( $entity . '_updating' ),
            function ( $model ) {
                $model->setAttribute( 'updated_at', current_time( 'mysql' ) );
            },
        );
    }

    /**
     * Get the current MySQL-formatted timestamp.
     *
     * @return string
     */
    public function freshTimestamp() {
        return current_time( 'mysql' );
    }
}
