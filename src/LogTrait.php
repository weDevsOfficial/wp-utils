<?php

namespace WeDevs\WpUtils;

/**
 * LogTrait class
 */
trait LogTrait {

    /**
     * Log a message.
     *
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    protected function log( $message, $level = 'info' ) {
        $formatted_message = sprintf(
            '[%s] %s',
            strtoupper( $level ),
            $message
        );

        // Write the log message using error_log()
        error_log( $formatted_message );
    }

    /**
     * Log a info message.
     *
     * @param string $message
     *
     * @return void
     */
    public function log_info( $message ) {
        $this->log( $message, 'info' );
    }

    /**
     * Log a error message.
     *
     * @param string $message
     *
     * @return void
     */
    public function log_error( $message ) {
        $this->log( $message, 'error' );
    }

    /**
     * Log a debug message.
     *
     * @param string $message
     *
     * @return void
     */
    public function log_debug( $message ) {
        $this->log( $message, 'debug' );
    }
}
