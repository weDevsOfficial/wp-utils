<?php

namespace WeDevs\WpUtils;

/**
 * Logger trait.
 */
trait Logger {

    /**
     * Log a message.
     *
     * @param string $message Log message.
     * @param string $level   Log level (info, error, debug, warning).
     * @param array  $context Optional context data.
     *
     * @return void
     */
    protected function log( $message, $level = 'info', array $context = [] ) {
        if ( 'debug' === $level && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
            return;
        }

        $formatted_message = sprintf(
            '[%s][%s] %s',
            strtoupper( $level ),
            static::class,
            $message,
        );

        if ( ! empty( $context ) ) {
            $formatted_message .= ' ' . wp_json_encode( $context );
        }

        error_log( $formatted_message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    }

    /**
     * Log an info message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context data.
     *
     * @return void
     */
    public function log_info( $message, array $context = [] ) {
        $this->log( $message, 'info', $context );
    }

    /**
     * Log an error message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context data.
     *
     * @return void
     */
    public function log_error( $message, array $context = [] ) {
        $this->log( $message, 'error', $context );
    }

    /**
     * Log a debug message.
     *
     * Only logs when WP_DEBUG is enabled.
     *
     * @param string $message Log message.
     * @param array  $context Optional context data.
     *
     * @return void
     */
    public function log_debug( $message, array $context = [] ) {
        $this->log( $message, 'debug', $context );
    }

    /**
     * Log a warning message.
     *
     * @param string $message Log message.
     * @param array  $context Optional context data.
     *
     * @return void
     */
    public function log_warning( $message, array $context = [] ) {
        $this->log( $message, 'warning', $context );
    }
}
