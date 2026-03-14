<?php

namespace WeDevs\WpUtils\Models\Traits;

use WeDevs\WpUtils\Models\QueryBuilder;

/**
 * SoftDeletes trait.
 *
 * Provides soft-delete functionality via a deleted_at column.
 * Models using this trait will auto-exclude deleted rows from queries.
 *
 * @phpstan-require-extends \WeDevs\WpUtils\Models\Model
 */
trait SoftDeletes {

    /**
     * Check if the model is trashed (soft-deleted).
     *
     * @return bool
     */
    public function trashed() {
        return null !== $this->getAttribute( 'deleted_at' );
    }

    /**
     * Soft-delete the model by setting deleted_at.
     *
     * @return bool
     */
    public function trash() {
        global $wpdb;

        $entity = static::getEntityName();

        do_action( static::hookName( $entity . '_trashing' ), $this );

        $now = current_time( 'mysql' );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            static::getTable(),
            [ 'deleted_at' => $now ],
            [ static::$primary_key => $this->getAttribute( static::$primary_key ) ],
        );

        if ( false === $result ) {
            return false;
        }

        $this->setAttribute( 'deleted_at', $now );

        do_action( static::hookName( $entity . '_trashed' ), $this );

        return true;
    }

    /**
     * Restore a soft-deleted model.
     *
     * @return bool
     */
    public function restore() {
        global $wpdb;

        $entity = static::getEntityName();

        do_action( static::hookName( $entity . '_restoring' ), $this );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            static::getTable(),
            [ 'deleted_at' => null ],
            [ static::$primary_key => $this->getAttribute( static::$primary_key ) ],
        );

        if ( false === $result ) {
            return false;
        }

        $this->setAttribute( 'deleted_at', null );

        do_action( static::hookName( $entity . '_restored' ), $this );

        return true;
    }

    /**
     * Start a query that includes trashed models.
     *
     * @return QueryBuilder
     */
    public static function withTrashed() {
        // Build a fresh query without the deleted_at scope.
        return new QueryBuilder( new static() );
    }

    /**
     * Start a query that returns only trashed models.
     *
     * @return QueryBuilder
     */
    public static function onlyTrashed() {
        return ( new QueryBuilder( new static() ) )->whereNotNull( 'deleted_at' );
    }
}
