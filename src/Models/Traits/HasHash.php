<?php

namespace WeDevs\WpUtils\Models\Traits;

/**
 * HasHash trait.
 *
 * Auto-generates a UUID v4 hash on model creation.
 *
 * @phpstan-require-extends \WeDevs\WpUtils\Models\Model
 */
trait HasHash {

    /**
     * Boot the HasHash trait.
     *
     * Hooks into the creating action to auto-generate a hash.
     *
     * @return void
     */
    protected static function bootHasHash() {
        $entity = static::getEntityName();

        add_action(
            static::hookName( $entity . '_creating' ),
            function ( $model ) {
                if ( empty( $model->hash ) ) {
                    $model->setAttribute( 'hash', wp_generate_uuid4() );
                }
            },
        );
    }

    /**
     * Find a model by its hash.
     *
     * @param string $hash The UUID hash.
     *
     * @return static|null
     */
    public static function findByHash( $hash ) {
        return static::query()->where( 'hash', $hash )->first();
    }

    /**
     * Find a model by its hash or throw.
     *
     * @param string $hash The UUID hash.
     *
     * @return static
     *
     * @throws \RuntimeException If not found.
     */
    public static function findByHashOrFail( $hash ) {
        $model = static::findByHash( $hash );

        if ( ! $model ) {
            throw new \RuntimeException(
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                sprintf( '%s with hash %s not found.', esc_html( static::getEntityName() ), esc_html( $hash ) ),
            );
        }

        return $model;
    }

    /**
     * Get the route key (hash) for REST API URLs.
     *
     * @return string
     */
    public function getRouteKey() {
        return $this->hash;
    }
}
